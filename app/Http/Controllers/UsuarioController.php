<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\PersonalApi;
use App\Models\Tienda;
use App\Models\Area;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::with(['tienda', 'area', 'rol'])
            ->where('is_deleted', false) // Solo usuarios no eliminados
            ->orderBy('status', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        $roles = Rol::activos()->get();
        $areas = Area::activos()->get();
        $tiendas = Tienda::activos()->get();

        return view('usuarios.create', compact('roles', 'areas', 'tiendas'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'nombre_personal' => 'required',
            'dni_personal' => [
                'required',
                'exists:personal_api,dni_personal',
                function ($attribute, $value, $fail) {
                    // Verificar si ya existe un usuario con este DNI
                    $exists = Usuario::where('dni_personal', $value)
                        ->where('is_deleted', false)
                        ->exists();

                    if ($exists) {
                        $fail('Este DNI ya está registrado como usuario.');
                    }
                }
            ],
            'clave' => 'required|min:8|confirmed',
            'clave_confirmation' => 'required|min:8',
            'id_roles' => 'required|exists:rols,id_roles',
            'id_tiendas_api' => 'required|exists:tiendas,id_tiendas',
            'id_areas' => 'required|exists:areas,id_areas',
        ], [
            'nombre_personal.required' => 'El nombre del personal es obligatorio',
            'dni_personal.required' => 'El DNI es obligatorio',
            'dni_personal.size' => 'El DNI debe tener 8 caracteres',
            'dni_personal.unique' => 'Este DNI ya está registrado',
            'id_tiendas_api.required' => 'Debe seleccionar una tienda',
            'id_areas.required' => 'Debe seleccionar un área',
            'id_roles.required' => 'Debe seleccionar un rol',
            'clave.required' => 'La contraseña es obligatoria',
            'clave.min' => 'La contraseña debe tener al menos 8 caracteres',
            'clave.confirmed' => 'Las contraseñas no coinciden',
        ]);

        try {
            DB::beginTransaction();

            $personal = PersonalApi::where('nombre', $request->nombre_personal)->firstOrFail();

            // Verificar si ya existe un usuario con este id_personal_api
            $usuarioExistente = Usuario::where('id_personal_api', $personal->id_personal_api)
                ->where('is_deleted', false)
                ->first();

            if ($usuarioExistente) {
                return back()->with('error', 'Este personal ya tiene un usuario asociado.')->withInput();
            }

            $usuario = Usuario::create([
                'nombre_personal' => $request->nombre_personal,
                'dni_personal' => $request->dni_personal, // Guardar DNI
                'id_personal_api' => $personal->id_personal_api,
                'clave' => $request->clave,
                'id_tiendas_api' => $request->id_tiendas_api,
                'id_areas' => $request->id_areas,
                'id_roles' => $request->id_roles,
                'status' => true,
                'is_deleted' => false,
                'created_at' => now()->timezone(config('app.timezone')), // Usar created_at en lugar de create_date
                'updated_at' => now()->timezone(config('app.timezone')), // Usar updated_at en lugar de last_update
            ]);

            DB::commit();
            // \Log::info('Usuario creado exitosamente', ['usuario_id' => $usuario->id_usuarios]);
            return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear el usuario: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $usuario = Usuario::with(['tienda', 'area', 'rol', 'personal'])->findOrFail($id);
        return view('usuarios.show', compact('usuario'));
    }

    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        $roles = Rol::activos()->get();
        $areas = Area::activos()->get();
        $tiendas = Tienda::activos()->get();

        return view('usuarios.edit', compact('usuario', 'roles', 'areas', 'tiendas'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_roles' => 'required|exists:rols,id_roles',
            'id_tiendas_api' => 'required|exists:tiendas,id_tiendas',
            'id_areas' => 'required|exists:areas,id_areas',
            'status' => 'required|boolean',
        ]);

        $usuario = Usuario::findOrFail($id);
        $usuario->update($request->only(['id_tiendas_api', 'id_areas', 'id_roles', 'status']));

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente');
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $usuario = Usuario::findOrFail($id);

            $usuario->update([
                'status' => false,
                'is_deleted' => true,
                'deleted_at' => now()->timezone(config('app.timezone')),
            ]);
            $usuario->delete();
            DB::commit();

            return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }


    public function buscarPersonal(Request $request)
    {
        $term = $request->get('term', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        try {
            // Obtener IDs de personal que ya tienen usuario
            $personalConUsuario = Usuario::where('is_deleted', false)
                ->pluck('id_personal_api')
                ->toArray();

            $results = PersonalApi::select(
                'id_personal_api as id',
                'nombre',
                'dni_personal',
                'id_tiendas_api',
                'id_areas'
            )
                ->with([
                    'tienda:id_tiendas,nombre',
                    'area:id_areas,nombre'
                ])
                ->whereNotIn('id_personal_api', $personalConUsuario) // Excluir personal con usuario existente
                ->where(function ($query) use ($term) {
                    $query->where('nombre', 'ILIKE', "%{$term}%")
                        ->orWhere('dni_personal', 'ILIKE', "%{$term}%")
                        ->orWhere('codigo_personal', 'ILIKE', "%{$term}%");
                })
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'text' => $item->nombre,
                        'nombre' => $item->nombre,
                        'dni_personal' => $item->dni_personal,
                        'tienda' => optional($item->tienda)->nombre ?? 'Sin tienda',
                        'tienda_id' => $item->id_tiendas_api,
                        'area' => optional($item->area)->nombre ?? 'Sin área',
                        'area_id' => $item->id_areas
                    ];
                });

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // public function buscarPersonal(Request $request)
    // {
    //     $term = $request->get('term', '');

    //     if (strlen($term) < 2) {
    //         return response()->json([]);
    //     }

    //     try {
    //         $results = PersonalApi::select(
    //             'id_personal_api as id',
    //             'nombre',
    //             'dni_personal',
    //             'id_tiendas_api',
    //             'id_areas'
    //         )
    //             ->with([
    //                 'tienda:id_tiendas,nombre',
    //                 'area:id_areas,nombre'
    //             ])
    //             ->where(function ($query) use ($term) {
    //                 $query->where('nombre', 'ILIKE', "%{$term}%")
    //                     ->orWhere('dni_personal', 'ILIKE', "%{$term}%")
    //                     ->orWhere('codigo_personal', 'ILIKE', "%{$term}%");
    //             })
    //             ->limit(10)
    //             ->get()
    //             ->map(function ($item) {
    //                 return [
    //                     'id' => $item->id,
    //                     'text' => $item->nombre,
    //                     'nombre' => $item->nombre,
    //                     'dni_personal' => $item->dni_personal, // Agregar este campo
    //                     'tienda' => optional($item->tienda)->nombre ?? 'Sin tienda',
    //                     'tienda_id' => $item->id_tiendas_api,
    //                     'area' => optional($item->area)->nombre ?? 'Sin área',
    //                     'area_id' => $item->id_areas
    //                 ];
    //             });

    //         return response()->json($results);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    public function getPersonalData($id)
    {
        try {
            $personal = PersonalApi::with(['area', 'tienda'])->findOrFail($id);

            return response()->json([
                'id_personal_api' => $personal->id_personal_api,
                'nombre' => $personal->nombre,
                'dni_personal' => $personal->dni_personal,
                'id_areas' => $personal->id_areas,
                'area_nombre' => $personal->area->nombre ?? 'N/A',
                'id_tiendas_api' => $personal->id_tiendas_api,
                'tienda_nombre' => $personal->tienda->nombre ?? 'N/A',
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Personal no encontrado',
                'success' => false
            ], 404);
        }
    }

    public function verificarPersonal(Request $request)
    {
        $id_personal = $request->get('id');

        $tieneUsuario = Usuario::where('id_personal_api', $id_personal)
            ->where('is_deleted', false)
            ->exists();

        return response()->json(['tiene_usuario' => $tieneUsuario]);
    }
}
