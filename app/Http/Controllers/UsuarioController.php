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
            'clave' => 'required|min:8|confirmed',
            'id_roles' => 'required|exists:rols,id_roles',
            'id_tiendas_api' => 'required|exists:tiendas,id_tiendas',
            'id_areas' => 'required|exists:areas,id_areas',
        ]);

        try {
            DB::beginTransaction();

            $personal = PersonalApi::where('nombre', $request->nombre_personal)->firstOrFail();

            $usuario = Usuario::create([
                'nombre_personal' => $request->nombre_personal,
                'id_personal_api' => $personal->id_personal_api,
                'clave' => $request->clave,
                'id_tiendas_api' => $request->id_tiendas_api,
                'id_areas' => $request->id_areas,
                'id_roles' => $request->id_roles,
                'status' => true,
                'is_deleted' => false,
                'create_date' => now(),
            ]);

            DB::commit();

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
        $usuario = Usuario::findOrFail($id);
        $usuario->update([
            'status' => false,
            'is_deleted' => true,
            'deleted_at' => now()->timezone(config('app.timezone')),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario desactivado exitosamente');
    }

    public function buscarPersonal(Request $request)
    {
        $term = $request->get('term', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        // \Log::info("Iniciando búsqueda para: " . $term); // Debug

        try {
            $results = PersonalApi::select(
                'id_personal_api as id',
                'nombre',
                'id_tiendas_api',
                'id_areas'
            )
                ->with([
                    'tienda:id_tiendas,nombre',
                    'area:id_areas,nombre'
                ])
                ->where(function ($query) use ($term) {
                    $query->where('nombre', 'ILIKE', "%{$term}%")
                        ->orWhere('codigo_personal', 'ILIKE', "%{$term}%");
                })
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'text' => $item->nombre, // Mantenemos 'text' para compatibilidad
                        'nombre' => $item->nombre,
                        'tienda' => optional($item->tienda)->nombre ?? 'Sin tienda',
                        'tienda_id' => $item->id_tiendas_api,
                        'area' => optional($item->area)->nombre ?? 'Sin área',
                        'area_id' => $item->id_areas
                    ];
                });

            // \Log::debug('Resultados encontrados:', $results->toArray()); // Debug

            return response()->json($results);
        } catch (\Exception $e) {
            // \Log::error("Error en buscarPersonal: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getPersonalData($id)
    {
        try {
            $personal = PersonalApi::with(['area', 'tienda'])->findOrFail($id);

            return response()->json([
                'id_personal_api' => $personal->id_personal_api,
                'nombre' => $personal->nombre,
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
}
