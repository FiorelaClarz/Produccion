@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: calc(100vh - 150px);">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-header" style="background: linear-gradient(135deg, #033988 70%, #f01917 100%); border-bottom: 3px solid #ebe64b;">
                    <h5 class="mb-0 text-white playfair-display-sc-bold"><i class="fas fa-sign-in-alt me-2" style="color: #ebe64b;"></i> Iniciar Sesión</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="dni_personal" class="form-label">DNI</label>
                            <input id="dni_personal" type="text" 
                                   class="form-control @error('dni_personal') is-invalid @enderror" 
                                   name="dni_personal" value="{{ old('dni_personal') }}" 
                                   required autocomplete="dni" autofocus
                                   placeholder="Ingrese su DNI" style="border: 1px solid #ebe64b;">

                            @error('dni_personal')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="clave" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <input id="clave" type="password" 
                                       class="form-control @error('clave') is-invalid @enderror" 
                                       name="clave" required autocomplete="current-password"
                                       placeholder="Ingrese su contraseña" style="border: 1px solid #ebe64b;">
                                <button class="btn toggle-password" style="border: 1px solid #ebe64b; background-color: #ffffff;" type="button">
                                    <i class="fas fa-eye" style="color: #033988;"></i>
                                </button>
                            </div>
                            @error('clave')
                                <div class="invalid-feedback d-block">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn" style="background-color: #033988; color: white; border: none; padding: 12px; font-weight: bold; border-radius: 5px; transition: all 0.3s ease;">
                                <i class="fas fa-sign-in-alt me-2" style="color: #ebe64b;"></i> Iniciar Sesión
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        // Mostrar/ocultar contraseña
        $('.toggle-password').click(function() {
            const icon = $(this).find('i');
            const input = $(this).siblings('input');
            
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    });
</script>
@endsection
@endsection
