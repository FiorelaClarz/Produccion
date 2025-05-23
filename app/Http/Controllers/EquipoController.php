<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\EquipoCabecera;
use App\Models\EquipoDetalle;
use App\Models\PersonalApi;
use App\Models\Turno;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EquipoController extends Controller
{
    /**
     * Muestra el listado de equipos del área del usuario logueado
     * 
     * @return \Illuminate\View\View Vista index con los equipos
     */
    public function index()
    {
        // Obtener el ID del área del usuario logueado
        $userAreaId = Auth::user()->id_areas;

        // Obtener equipos activos del área del usuario, ordenados por fecha de creación
        $equipos = EquipoCabecera::with(['usuario', 'area', 'turno', 'equiposDetalle.personal'])
            ->where('id_areas', $userAreaId)
            ->where('is_deleted', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('equipos.index', compact('equipos'));
    }

    /**
     * Muestra el formulario para crear un nuevo equipo
     * 
     * @return \Illuminate\View\View Vista create con datos necesarios
     */
    public function create()
    {
        // Obtener el ID del área del usuario logueado
        $userAreaId = Auth::user()->id_areas;

        // Obtener personal del mismo área que el usuario, activos y no eliminados
        $personal = PersonalApi::where('id_areas', $userAreaId)
            ->where('status', true) // Solo personal activo
            ->where('is_deleted', false) // Solo personal no eliminado
            ->get();

        // Obtener turnos activos
        $turnos = Turno::where('status', true)
            ->where('is_deleted', false)
            ->get();

        return view('equipos.create', compact('personal', 'turnos'));
    }

    /**
     * Almacena un nuevo equipo en la base de datos
     * 
     * @param  \Illuminate\Http\Request  $request Datos del formulario
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito/error
     */
    public function store(Request $request)
    {
        // Validar datos del formulario
        $validatedData = $request->validate([
            'id_turnos' => 'required|exists:turnos,id_turnos',
            'personal_seleccionado' => 'required|array|min:1',
            'personal_seleccionado.*' => 'exists:personal_api,id_personal_api'
        ]);

        try {
            DB::beginTransaction();

            // Obtener usuario logueado y su área
            $user = Auth::user();

            // Crear cabecera del equipo
            $equipoCab = EquipoCabecera::create([
                'id_usuarios' => $user->id_usuarios,
                'id_areas' => $user->id_areas,
                'id_turnos' => $request->id_turnos,
                'status' => true,
                'is_deleted' => false,
                'salida' => null
            ]);

            // Registrar creación de cabecera
            Log::info('Cabecera de equipo creada:', [
                'id' => $equipoCab->id_equipos_cab,
                'data' => $equipoCab->toArray()
            ]);

            // Procesar cada miembro del equipo seleccionado
            foreach ($request->personal_seleccionado as $idPersonal) {
                EquipoDetalle::create([
                    'id_equipos_cab' => $equipoCab->id_equipos_cab,
                    'id_personal_api' => $idPersonal,
                    'status' => true,
                    'is_deleted' => false
                ]);
            }

            DB::commit();

            return redirect()->route('equipos.index')
                ->with('success', 'Equipo creado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear equipo:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al crear el equipo: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Muestra los detalles de un equipo específico
     * 
     * @param  int  $id ID del equipo
     * @return \Illuminate\View\View Vista show con los datos del equipo
     */
    public function show($id)
    {
        // Obtener equipo con todas sus relaciones
        $equipo = EquipoCabecera::with([
            'usuario.personal',
            'area',
            'turno',
            'equiposDetalle.personal'
        ])->findOrFail($id);

        // Verificar que el equipo pertenezca al área del usuario
        if ($equipo->id_areas != Auth::user()->id_areas) {
            abort(403, 'No tienes permiso para ver este equipo');
        }

        return view('equipos.show', compact('equipo'));
    }

    /**
     * Muestra el formulario para editar un equipo existente
     * 
     * @param  int  $id ID del equipo
     * @return \Illuminate\View\View Vista edit con los datos del equipo
     */
    public function edit($id)
    {
        // Obtener equipo con sus detalles
        $equipo = EquipoCabecera::with(['equiposDetalle'])->findOrFail($id);

        // Verificar que el equipo pertenezca al área del usuario
        if ($equipo->id_areas != Auth::user()->id_areas) {
            abort(403, 'No tienes permiso para editar este equipo');
        }

        // Obtener personal del mismo área que el usuario, activos y no eliminados
        $personal = PersonalApi::where('id_areas', $equipo->id_areas)
            ->where('status', true)
            ->where('is_deleted', false)
            ->get();

        // Obtener turnos activos
        $turnos = Turno::where('status', true)
            ->where('is_deleted', false)
            ->get();

        // Obtener IDs del personal ya en el equipo
        $personalEnEquipo = $equipo->equiposDetalle->pluck('id_personal_api')->toArray();

        return view('equipos.edit', compact('equipo', 'personal', 'turnos', 'personalEnEquipo'));
    }

    /**
     * Actualiza un equipo existente en la base de datos
     * 
     * @param  \Illuminate\Http\Request  $request Datos del formulario
     * @param  int  $id ID del equipo
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito/error
     */
    public function update(Request $request, $id)
    {
        // Validar datos del formulario
        $validatedData = $request->validate([
            'id_turnos' => 'required|exists:turnos,id_turnos',
            'personal_seleccionado' => 'required|array|min:1',
            'personal_seleccionado.*' => 'exists:personal_api,id_personal_api'
        ]);

        try {
            DB::beginTransaction();

            // Obtener equipo existente
            $equipo = EquipoCabecera::findOrFail($id);

            // Verificar que el equipo pertenezca al área del usuario
            if ($equipo->id_areas != Auth::user()->id_areas) {
                abort(403, 'No tienes permiso para editar este equipo');
            }

            // Actualizar datos principales del equipo
            $equipo->update([
                'id_turnos' => $request->id_turnos,
                'updated_at' => now()
            ]);

            // Obtener IDs del personal actual en el equipo
            $personalActual = $equipo->equiposDetalle()->pluck('id_personal_api')->toArray();

            // Personal a agregar (nuevos)
            $personalAAgregar = array_diff($request->personal_seleccionado, $personalActual);

            // Personal a eliminar (que ya no están seleccionados)
            $personalAEliminar = array_diff($personalActual, $request->personal_seleccionado);

            // Agregar nuevos miembros al equipo
            foreach ($personalAAgregar as $idPersonal) {
                // Buscar si existe un registro eliminado para este personal
                $registroExistente = EquipoDetalle::withTrashed()
                    ->where('id_equipos_cab', $equipo->id_equipos_cab)
                    ->where('id_personal_api', $idPersonal)
                    ->first();

                if ($registroExistente) {
                    // Restaurar el registro existente
                    $registroExistente->restore();
                    $registroExistente->update([
                        'status' => true,
                        'is_deleted' => false,
                        'updated_at' => now()
                    ]);
                } else {
                    // Crear nuevo registro
                    EquipoDetalle::create([
                        'id_equipos_cab' => $equipo->id_equipos_cab,
                        'id_personal_api' => $idPersonal,
                        'status' => true,
                        'is_deleted' => false
                    ]);
                }
            }

            // Eliminar (soft delete) miembros que ya no están en el equipo
            EquipoDetalle::where('id_equipos_cab', $equipo->id_equipos_cab)
                ->whereIn('id_personal_api', $personalAEliminar)
                ->update([
                    'status' => false,
                    'is_deleted' => true,
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            DB::commit();

            return redirect()->route('equipos.index')
                ->with('success', 'Equipo actualizado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar equipo:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return back()->with('error', 'Error al actualizar el equipo: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Elimina (soft delete) un equipo específico
     * 
     * @param  int  $id ID del equipo
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito/error
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Obtener equipo y marcarlo como eliminado
            $equipo = EquipoCabecera::findOrFail($id);

            // Verificar que el equipo pertenezca al área del usuario
            if ($equipo->id_areas != Auth::user()->id_areas) {
                abort(403, 'No tienes permiso para eliminar este equipo');
            }

            $equipo->update([
                'is_deleted' => true,
                'deleted_at' => now(),
                'status' => false
            ]);

            // También marcamos como eliminados los detalles
            EquipoDetalle::where('id_equipos_cab', $equipo->id_equipos_cab)
                ->update([
                    'is_deleted' => true,
                    'deleted_at' => now(),
                    'status' => false
                ]);

            DB::commit();

            return redirect()->route('equipos.index')
                ->with('success', 'Equipo eliminado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el equipo: ' . $e->getMessage());
        }
    }

    /**
     * Cambia el estado (activo/inactivo) de un equipo
     * 
     * @param  int  $id ID del equipo
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito/error
     */
    public function toggleStatus($id)
    {
        try {
            DB::beginTransaction();

            $equipo = EquipoCabecera::findOrFail($id);

            if ($equipo->id_areas != Auth::user()->id_areas) {
                abort(403, 'No tienes permiso para modificar este equipo');
            }

            $nuevoEstado = !$equipo->status;

            $equipo->update(['status' => $nuevoEstado]);

            EquipoDetalle::where('id_equipos_cab', $equipo->id_equipos_cab)
                ->update(['status' => $nuevoEstado]);

            DB::commit();

            return back()->with('success', $nuevoEstado ? 'Equipo activado correctamente' : 'Equipo desactivado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }

    /**
     * Registra la hora de salida de un equipo
     * 
     * @param  int  $id ID del equipo
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito/error
     */
    public function registrarSalida($id)
    {
        try {
            // Obtener equipo y registrar salida
            $equipo = EquipoCabecera::findOrFail($id);

            // Verificar que el equipo pertenezca al área del usuario
            if ($equipo->id_areas != Auth::user()->id_areas) {
                abort(403, 'No tienes permiso para modificar este equipo');
            }

            $equipo->update([
                'salida' => now()
            ]);

            // Redireccionar a la vista principal de producción
            return redirect()->route('produccion.index')
                ->with('success', 'Hora de salida registrada correctamente. La sesión de trabajo ha finalizado.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al registrar la salida: ' . $e->getMessage());
        }
    }
}
