@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Detalles de Merma #{{ $merma->id_mermas_cab }}</h4>
                        <div>
                            @if($merma->id_usuarios == Auth::id())
                                <a href="{{ route('mermas.pdf', $merma->id_mermas_cab) }}" class="btn btn-sm btn-secondary" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Exportar PDF
                                </a>
                                <a href="{{ route('mermas.edit', $merma->id_mermas_cab) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Información de la Merma -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2"><i class="fas fa-info-circle me-2"></i>Información General</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-calendar-alt me-1"></i> Fecha:</label>
                                    <p>{{ \Carbon\Carbon::parse($merma->fecha_registro)->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-clock me-1"></i> Hora:</label>
                                    <p>{{ $merma->hora_registro }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-user me-1"></i> Usuario:</label>
                                    <p>{{ $merma->usuario->nombre_personal ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-store me-1"></i> Tienda:</label>
                                    <p>{{ $merma->tienda->nombre ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-history me-1"></i> Última actualización:</label>
                                    <p>{{ \Carbon\Carbon::parse($merma->updated_at)->format('d/m/Y H:i:s') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold"><i class="fas fa-hashtag me-1"></i> Total de ítems:</label>
                                    <p>{{ $merma->mermasDetalle->where('is_deleted', false)->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles de la Merma -->
                    <div class="mt-4">
                        <h5 class="border-bottom pb-2"><i class="fas fa-clipboard-list me-2"></i>Productos Registrados</h5>
                        
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Área</th>
                                        <th>Receta</th>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-center">Costo</th>
                                        <th class="text-center">Total</th>
                                        <th>U. Medida</th>
                                        <th>Observación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($merma->mermasDetalle->where('is_deleted', false) as $index => $detalle)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>{{ $detalle->area->nombre ?? 'N/A' }}</td>
                                            <td>{{ $detalle->receta->nombre ?? 'N/A' }}</td>
                                            <td>{{ optional($detalle->receta)->id_productos_api ?? 'N/A' }}</td>
                                            <td class="text-center">{{ number_format($detalle->cantidad, 2) }}</td>
                                            <td class="text-center">{{ number_format($detalle->costo ?? 0, 2) }}</td>
                                            <td class="text-center">{{ number_format($detalle->total ?? 0, 2) }}</td>
                                            <td>{{ $detalle->uMedida->nombre ?? 'N/A' }}</td>
                                            <td>{{ $detalle->obs ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No hay detalles disponibles</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('mermas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                        @if($merma->id_usuarios == Auth::id())
                            <form action="{{ route('mermas.destroy', $merma->id_mermas_cab) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger" id="btn-eliminar">
                                    <i class="fas fa-trash me-1"></i> Eliminar Merma
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Eliminar merma
        $('#btn-eliminar').click(function() {
            Swal.fire({
                title: '¿Está seguro?',
                text: 'Esta acción eliminará la merma y no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('.delete-form').submit();
                }
            });
        });
    });
</script>
@endpush


