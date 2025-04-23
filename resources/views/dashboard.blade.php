@extends('layouts.app')

@section('title', 'Dashboard Administrador')

@section('content')
@if(Auth::user()->id_roles == 1)
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Panel de Control - Administrador</h1>
    </div>

    <div class="row">
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