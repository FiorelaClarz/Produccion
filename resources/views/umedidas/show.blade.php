@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Detalle de Unidad: {{ $umedida->nombre }}</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>ID:</strong> {{ $umedida->id_u_medidas }}</p>
                    <p><strong>Estado:</strong> 
                        @if($umedida->status)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-warning text-dark">Inactivo</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Creado el:</strong> {{ $umedida->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                    <p><strong>Última actualización:</strong> {{ $umedida->updated_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('umedidas.edit', $umedida->id_u_medidas) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="{{ route('umedidas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>
@endsection