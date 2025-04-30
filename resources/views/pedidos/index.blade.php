@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col-md-6">
            <h2>Listado de Pedidos</h2>
        </div>
        <div class="col-md-6 text-end">
            @php
                // Obtener la hora límite configurada en el sistema
                $horaLimiteActual = App\Models\HoraLimite::where('status', true)
                    ->where('is_deleted', false)
                    ->first();
                
                // Obtener la hora actual
                $horaActual = now();
                
                // Parsear la hora límite si existe
                $horaLimite = $horaLimiteActual ? Carbon\Carbon::parse($horaLimiteActual->hora_limite) : null;
                
                // Verificar si estamos dentro del horario permitido (hora actual <= hora límite)
                $dentroDeHoraPermitida = $horaLimite ? $horaActual->lte($horaLimite) : false;
            @endphp

            {{-- Botón para crear nuevo pedido --}}
            <a href="{{ route('pedidos.create') }}" 
               class="btn btn-primary {{ !$dentroDeHoraPermitida ? 'disabled' : '' }}" 
               @if(!$dentroDeHoraPermitida) 
                   title="El tiempo para realizar pedidos ha terminado" 
               @endif>
                <i class="fas fa-plus"></i> Nuevo Pedido
                @if(!$dentroDeHoraPermitida)
                    {{-- Mostrar badge si está fuera del horario --}}
                    <span class="badge bg-danger ms-2">Tiempo agotado</span>
                @endif
            </a>
        </div>
    </div>

    {{-- Mensajes de sesión --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Documento</th>
                    <th>Usuario</th>
                    <th>Tienda</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Hora Límite</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pedidos as $pedido)
                @php
                    // Obtener el estado general del pedido
                    $estados = $pedido->pedidosDetalle->pluck('id_estados')->unique();
                    $estadoGeneral = $estados->count() == 1 
                        ? $pedido->pedidosDetalle->first()->estado->nombre 
                        : 'Mixto';
                @endphp
                
                <tr>
                    <td>{{ $pedido->id_pedidos_cab }}</td>
                    <td>{{ $pedido->doc_interno }}</td>
                    <td>{{ $pedido->usuario->nombre_personal }}</td>
                    <td>{{ $pedido->tienda->nombre }}</td>
                    <td>{{ \Carbon\Carbon::parse($pedido->fecha_created)->format('d/m/Y') }}</td>
                    <td>{{ $pedido->hora_created }}</td>
                    <td>
                        {{ $pedido->horaLimite->hora_limite }}
                        @if (!$dentroDeHoraPermitida)
                            <span class="badge bg-danger">Fuera de horario</span>
                        @endif
                    </td>
                    <td>{{ $estadoGeneral }}</td>
                    <td>
                        {{-- Botón para ver el pedido - siempre activo --}}
                        <a href="{{ route('pedidos.show', $pedido->id_pedidos_cab) }}" 
                           class="btn btn-sm btn-info" 
                           title="Ver detalles del pedido">
                            <i class="fas fa-eye"></i>
                        </a>
                        
                        {{-- Botones condicionales según si estamos dentro del horario permitido --}}
                        @if($dentroDeHoraPermitida)
                            {{-- Botón de editar - solo visible dentro del horario --}}
                            <a href="{{ route('pedidos.edit', $pedido->id_pedidos_cab) }}" 
                               class="btn btn-sm btn-warning" 
                               title="Editar pedido">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            {{-- Formulario para eliminar - solo visible dentro del horario --}}
                            <form action="{{ route('pedidos.destroy', $pedido->id_pedidos_cab) }}" 
                                  method="POST" 
                                  style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-sm btn-danger" 
                                        title="Eliminar pedido" 
                                        onclick="return confirm('¿Estás seguro de eliminar este pedido?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @else
                            {{-- Botones deshabilitados fuera del horario --}}
                            <button class="btn btn-sm btn-secondary" 
                                    disabled 
                                    title="Edición no permitida - Fuera del horario permitido">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-secondary" 
                                    disabled 
                                    title="Eliminación no permitida - Fuera del horario permitido">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="d-flex justify-content-center">
        {{ $pedidos->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Actualizar automáticamente la página cada 5 minutos
        // para reflejar cambios en el estado del horario permitido
        setInterval(() => {
            window.location.reload();
        }, 300000); // 300,000 ms = 5 minutos
    });
</script>
@endsection