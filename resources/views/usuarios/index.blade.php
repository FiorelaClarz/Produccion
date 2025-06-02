@extends('layouts.app')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="//cdn.datatables.net/2.3.1/css/dataTables.dataTables.min.css">
<style>
    /* Estilos personalizados para DataTables */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_processing,
    .dataTables_wrapper .dataTables_paginate {
        margin: 15px 0;
        font-size: 0.9rem;
    }
    
    /* Estilos para el buscador */
    .dataTables_wrapper .dataTables_filter {
        position: relative;
        float: right;
        text-align: right;
        margin-bottom: 15px;
    }
    
    /* Ocultar la etiqueta "Buscar:" */
    .dataTables_wrapper .dataTables_filter label {
        position: relative;
        margin-bottom: 0;
        font-weight: 500;
    }
    
    .dataTables_wrapper .dataTables_filter label::before {
        content: '';
    }
    
    /* Estilizar el campo de búsqueda */
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #d1d3e2;
        border-radius: 30px; /* Más redondeado para aspecto moderno */
        padding: 0.6rem 1rem 0.6rem 2.5rem;
        font-size: 0.85rem;
        min-width: 280px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        background-color: #f8f9fc;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%234e73df' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 15px center;
        background-size: 16px;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #4e73df;
        outline: 0;
        box-shadow: 0 4px 10px rgba(78, 115, 223, 0.15);
        background-color: #fff;
    }
    
    /* Añadir texto placeholder con JavaScript */
    .dataTables_wrapper .dataTables_filter input::placeholder {
        color: #b7b9cc;
        opacity: 1;
    }
    
    /* Estilos para el selector de entradas */
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #d1d3e2;
        border-radius: 4px;
        padding: 0.375rem 1.75rem 0.375rem 0.75rem;
        font-size: 0.85rem;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }
    
    .dataTables_wrapper .dataTables_length select:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        outline: 0;
    }
    
    /* Estilos para la paginación */
    .dataTables_wrapper .dataTables_paginate {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.35rem 0.65rem;
        margin: 0 3px;
        font-size: 0.85rem;
        border-radius: 4px;
        border: 1px solid transparent;
        color: #5a5c69 !important;
        transition: all 0.15s ease-in-out;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #4e73df !important;
        border-color: #4e73df;
        color: white !important;
        font-weight: 600;
        box-shadow: 0 2px 5px rgba(78, 115, 223, 0.18);
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
        background: #eaecf4 !important;
        border-color: #d1d3e2;
        color: #2e59d9 !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: default;
    }
    
    /* Estilo para la información de registros */
    .dataTables_wrapper .dataTables_info {
        padding-top: 0.5rem;
        font-size: 0.85rem;
        color: #6e707e;
    }
    
    /* Mejoras para la tabla */
    #dataTable {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        border: none;
    }
    
    #dataTable thead th {
        background-color: #f8f9fc;
        border-bottom: 2px solid #e3e6f0;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.05rem;
        color: #5a5c69;
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }
    
    #dataTable tbody tr {
        transition: all 0.2s;
    }
    
    #dataTable tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-top: 1px solid #e3e6f0;
    }
    
    /* Efecto hover para los encabezados ordenables */
    #dataTable thead th.sorting:hover {
        background-color: #eaecf4;
        cursor: pointer;
    }
    
    /* Estilo para el ordenamiento activo */
    #dataTable thead th.sorting_asc,
    #dataTable thead th.sorting_desc {
        background-color: #e8f0fe;
        color: #4e73df;
    }
    
    /* Mejoras en la interfaz general */
    .dataTables_wrapper {
        padding: 0.5rem 0;
        position: relative;
    }
    
    /* Colores para filas activas */
    .fila-activa-par {
        background-color: #f0fdf4;
        /* Verde muy claro */
    }

    .fila-activa-impar {
        background-color: #dcfce7;
        /* Verde claro */
    }

    /* Colores para filas inactivas */
    .fila-inactiva-par {
        background-color: #fef2f2;
        /* Rojo muy claro */
    }

    .fila-inactiva-impar {
        background-color: #fee2e2;
        /* Rojo claro */
    }

    /* Efecto hover */
    .fila-activa-par:hover,
    .fila-activa-impar:hover {
        background-color: #bbf7d0;
        /* Verde más intenso */
    }

    .fila-inactiva-par:hover,
    .fila-inactiva-impar:hover {
        background-color: #fecaca;
        /* Rojo más intenso */
    }

    /* Estilo para el último registro */
    tr:last-child {
        border-left: 3px solid #ff9800;
    }

    /* Texto negro para el estado */
    .badge-success,
    .badge-danger {
        color: #000 !important;
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header bg-primary py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold text-white">
                <i class="fas fa-users mr-2"></i>Listado de Usuarios
            </h5>
            <a href="{{ route('usuarios.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus-circle mr-1"></i> Nuevo Usuario
            </a>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="dataTable">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">Nombre</th>
                            <th class="text-center">DNI</th>
                            <th class="text-center">Tienda</th>
                            <th class="text-center">Área</th>
                            <th class="text-center">Rol</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $usuario)
                        <tr class="{{ $usuario->status ? ($loop->even ? 'fila-activa-par' : 'fila-activa-impar') : ($loop->even ? 'fila-inactiva-par' : 'fila-inactiva-impar') }}">
                            <td>
                                <div class="font-weight-bold">{{ $usuario->nombre_personal }}</div>
                                <small class="text-muted">ID: {{ $usuario->id_usuarios }}</small>
                            </td>
                            <td class="text-center">{{ $usuario->dni_personal }}</td>
                            <td class="text-center">{{ $usuario->tienda->nombre ?? 'N/A' }}</td>
                            <td class="text-center">{{ $usuario->area->nombre ?? 'N/A' }}</td>
                            <td class="text-center">{{ $usuario->rol->nombre ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $usuario->status ? 'badge-success' : 'badge-danger' }}" style="color: #000 !important;">
                                    <i class="fas fa-circle {{ $usuario->status ? 'text-success' : 'text-danger' }} mr-1"></i>
                                    {{ $usuario->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('usuarios.show', $usuario->id_usuarios) }}" class="btn btn-info btn-sm" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('usuarios.edit', $usuario->id_usuarios) }}" class="btn btn-primary btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('usuarios.destroy', $usuario->id_usuarios) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm('¿Estás seguro que deseas eliminar este usuario?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Colores para filas activas */
    .fila-activa-par {
        background-color: #f0fdf4;
        /* Verde muy claro */
    }

    .fila-activa-impar {
        background-color: #dcfce7;
        /* Verde claro */
    }

    /* Colores para filas inactivas */
    .fila-inactiva-par {
        background-color: #fef2f2;
        /* Rojo muy claro */
    }

    .fila-inactiva-impar {
        background-color: #fee2e2;
        /* Rojo claro */
    }

    /* Efecto hover */
    .fila-activa-par:hover,
    .fila-activa-impar:hover {
        background-color: #bbf7d0;
        /* Verde más intenso */
    }

    .fila-inactiva-par:hover,
    .fila-inactiva-impar:hover {
        background-color: #fecaca;
        /* Rojo más intenso */
    }

    /* Estilo para el último registro */
    tr:last-child {
        border-left: 3px solid #ff9800;
    }

    /* Texto negro para el estado */
    .badge-success,
    .badge-danger {
        color: #000 !important;
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
    }
</style>
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="//cdn.datatables.net/2.3.1/js/dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        // Inicializar DataTables con la nueva sintaxis
        try {
            let table = new DataTable('#dataTable', {
                "responsive": true,
                "language": {
                    "search": "Buscar: ", // Removemos el texto "Buscar:"
                    "zeroRecords": "No se encontraron registros coincidentes",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },
                "dom": '<"top"fl>rt<"bottom"ip>', // Personaliza el layout de DataTables
                "initComplete": function() {
                    // Añadir placeholder al campo de búsqueda
                    $('.dataTables_filter input').attr('placeholder', 'Buscar usuarios...');
                    
                    // Añadir algunas clases para mejorar estilos
                    $('.dataTables_length select').addClass('custom-select-sm');
                    
                    // Añadir efecto de animación sutil al filtrar
                    let searchTimeout;
                    $('.dataTables_filter input').on('keyup', function() {
                        let $rows = $('#dataTable tbody tr');
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(function() {
                            $rows.css('transition', 'opacity 0.3s ease');
                        }, 300);
                    });
                }
            });
            console.log('DataTable inicializada correctamente con estilos mejorados');
        } catch (e) {
            console.error('Error al inicializar DataTable:', e);
        }
    });
</script>
@endpush