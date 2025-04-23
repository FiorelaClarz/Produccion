@extends('layouts.app')

@section('title', 'Dashboard Administrador')

@section('content')
@if(Auth::user()->id_roles == 1)
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Panel de Control - Administrador</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary">Compartir</button>
                <button type="button" class="btn btn-sm btn-outline-secondary">Exportar</button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                <i class="fas fa-calendar-alt me-1"></i>
                Esta semana
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user me-1"></i>
                    Información del Administrador
                </div>
                <div class="card-body">
                    <h5 class="card-title">Bienvenido, {{ Auth::user()->nombre_personal }}</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="card-text">
                                <strong><i class="fas fa-store me-1"></i> Tienda:</strong><br>
                                {{ Auth::user()->tienda->nombre ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="card-text">
                                <strong><i class="fas fa-building me-1"></i> Área:</strong><br>
                                {{ Auth::user()->area->nombre ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="card-text">
                                <strong><i class="fas fa-user-tag me-1"></i> Rol:</strong><br>
                                {{ Auth::user()->rol->nombre ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Widgets específicos para administradores -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-users me-1"></i> Resumen de Usuarios
                </div>
                <div class="card-body">
                    <p>Total de usuarios: {{ \App\Models\Usuario::count() }}</p>
                    <p>Usuarios activos: {{ \App\Models\Usuario::where('status', true)->count() }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-cog me-1"></i> Acciones Rápidas
                </div>
                <div class="card-body">
                    <a href="{{ route('usuarios.create') }}" class="btn btn-primary me-2">
                        <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
                    </a>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                        <i class="fas fa-list me-1"></i> Listar Usuarios
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="container-fluid">
    <div class="alert alert-danger mt-3">
        No tienes permiso para acceder a esta sección
    </div>
</div>
@endif
@endsection