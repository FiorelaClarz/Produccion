@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-clipboard-list mr-2"></i>Detalles de Pedidos
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Cliente</th>
                            <th>Tienda</th>
                            <th>Cantidad</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detalle->pedidos as $pedido)
                        <tr>
                            <td>{{ $pedido->id_pedidos_det }}</td>
                            <td>{{ $pedido->pedido->usuario->nombre_personal }}</td>
                            <td>{{ $pedido->pedido->tienda->nombre }}</td>
                            <td>{{ number_format($pedido->cantidad, 2) }}</td>
                            <td>
                                @if($pedido->id_estados == 4)
                                    <span class="badge badge-success">Terminado</span>
                                @elseif($pedido->id_estados == 5)
                                    <span class="badge badge-danger">Cancelado</span>
                                @endif
                            </td>
                            <td>{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
    </div>
</div>

<style>
.badge {
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background-color: #1cc88a;
    color: white;
}

.badge-danger {
    background-color: #e74a3b;
    color: white;
}

.table th {
    background-color: #f8f9fc;
    font-weight: 600;
    color: #5a5c69;
}

.table td {
    vertical-align: middle;
}
</style>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        }
    });
});
</script>
@endsection 