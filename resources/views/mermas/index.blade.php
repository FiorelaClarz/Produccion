@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestión de Mermas</h1>
        <div>
            <a href="{{ route('mermas.create') }}" class="btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Merma
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Acciones:</div>
                    <a class="dropdown-item" href="{{ route('mermas.index', ['filter' => 'today']) }}">Hoy</a>
                    <a class="dropdown-item" href="{{ route('mermas.index', ['filter' => 'yesterday']) }}">Ayer</a>
                    <a class="dropdown-item" href="{{ route('mermas.index', ['filter' => 'week']) }}">Última semana</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#customDateModal">Fecha personalizada</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="form-group mb-md-0">
                        <label class="d-block" style="margin-bottom: 0;">
                            <strong>Filtro activo:</strong>
                            <span class="badge badge-primary ml-2">
                                @switch($filter)
                                    @case('today')
                                        Hoy
                                        @break
                                    @case('yesterday')
                                        Ayer
                                        @break
                                    @case('week')
                                        Última semana
                                        @break
                                    @case('custom')
                                        {{ request('custom_date', 'Fecha personalizada') }}
                                        @break
                                    @default
                                        Todos
                                @endswitch
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Mermas -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Mermas</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Usuario</th>
                            <th>Tienda</th>
                            <th>Detalles</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mermas as $merma)
                            <tr>
                                <td>{{ $merma->id_mermas_cab }}</td>
                                <td>{{ \Carbon\Carbon::parse($merma->fecha_registro)->format('d/m/Y') }}</td>
                                <td>{{ $merma->hora_registro }}</td>
                                <td>{{ $merma->usuario->nombre_personal ?? 'N/A' }}</td>
                                <td>{{ $merma->tienda->nombre ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info" 
                                        data-toggle="collapse" 
                                        data-target="#collapse-{{ $merma->id_mermas_cab }}" 
                                        aria-expanded="false">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('mermas.show', $merma->id_mermas_cab) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('mermas.edit', $merma->id_mermas_cab) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('mermas.pdf', $merma->id_mermas_cab) }}" class="btn btn-sm btn-secondary" target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-merma" 
                                            data-id="{{ $merma->id_mermas_cab }}"
                                            data-toggle="modal" 
                                            data-target="#deleteModal">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Detalles expandibles -->
                            <tr class="collapse" id="collapse-{{ $merma->id_mermas_cab }}">
                                <td colspan="7" class="p-0">
                                    <div class="p-3 bg-light">
                                        <h6 class="font-weight-bold mb-3">Detalles de la Merma #{{ $merma->id_mermas_cab }}</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="bg-primary text-white">
                                                    <tr>
                                                        <th>Área</th>
                                                        <th>Receta</th>
                                                        <th>Producto</th>
                                                        <th>Cantidad</th>
                                                        <th>U. Medida</th>
                                                        <th>Observación</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($merma->mermasDetalle as $detalle)
                                                        <tr>
                                                            <td>{{ $detalle->area->nombre ?? 'N/A' }}</td>
                                                            <td>{{ $detalle->receta->nombre ?? 'N/A' }}</td>
                                                            <td>{{ optional($detalle->receta)->id_productos_api ?? 'N/A' }}
                                                            <td class="text-right">{{ number_format($detalle->cantidad, 2) }}</td>
                                                            <td>{{ $detalle->uMedida->nombre ?? 'N/A' }}</td>
                                                            <td>{{ $detalle->obs ?? '-' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center">No hay detalles disponibles</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay registros de mermas disponibles</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end mt-3">
                {{ $mermas->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal de Eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar esta merma? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Fecha Personalizada -->
<div class="modal fade" id="customDateModal" tabindex="-1" role="dialog" aria-labelledby="customDateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="customDateModalLabel">Seleccionar Fecha</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="customDateForm" action="{{ route('mermas.index') }}" method="GET">
                    <input type="hidden" name="filter" value="custom">
                    <div class="form-group">
                        <label for="custom_date">Fecha:</label>
                        <input type="date" class="form-control" id="custom_date" name="custom_date" required value="{{ request('custom_date', date('Y-m-d')) }}">
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Aplicar Filtro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializar DataTable
        $('#dataTable').DataTable({
            "paging": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "responsive": true,
            "language": {
                "search": "Buscar:",
                "zeroRecords": "No se encontraron registros coincidentes",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            }
        });

        // Configurar el modal de eliminación
        $('.delete-merma').click(function() {
            const id = $(this).data('id');
            $('#deleteForm').attr('action', `{{ url('mermas') }}/${id}`);
        });
    });
</script>
@endpush
