@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-home me-1"></i> Bienvenido
        </div>
        <div class="card-body">
            <h5 class="card-title">Hola, {{ Auth::user()->nombre_personal }}</h5>
            <p class="card-text">
                Has iniciado sesión como <strong>{{ Auth::user()->rol->nombre ?? 'Usuario' }}</strong>.
            </p>
            
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-store me-1"></i> Tienda
                            </h5>
                            <p class="card-text">
                                {{ Auth::user()->tienda->nombre ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-building me-1"></i> Área
                            </h5>
                            <p class="card-text">
                                {{ Auth::user()->area->nombre ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-user-tag me-1"></i> Rol
                            </h5>
                            <p class="card-text">
                                {{ Auth::user()->rol->nombre ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection