@php
/**
 * Función auxiliar para generar URLs seguras
 */
function generarRuta($item) {
    if (!isset($item['route']) || !Route::has($item['route'])) {
        return '#';
    }
    
    if ($item['route'] == 'usuarios.show' && Auth::check()) {
        return route('usuarios.show', ['usuario' => Auth::id()]);
    }
    
    if (isset($item['params'])) {
        if (is_array($item['params'])) {
            return route($item['route'], $item['params']);
        } elseif ($item['params'] == 'active_equipment_id' && session()->has('active_equipment_id')) {
            return route($item['route'], ['id' => session('active_equipment_id')]);
        }
    }
    
    return route($item['route']);
}
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestión de Producción para Estrella Supermercados">
    <meta name="author" content="Estrella Supermercados">
    <meta name="theme-color" content="#033988"> 
    
    <!-- Título dinámico para la pestaña del navegador -->
    <title>@yield('title', 'Producción') | Estrella Supermercados</title>
    
    <!-- Favicon y configuración de íconos -->
    <link rel="shortcut icon" href="{{ asset('./images/favicon.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('./images/favicon.png') }}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('./images/favicon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('./images/favicon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('./images/favicon.png') }}">
    <!-- jQuery primero -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Luego Popper.js y Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 - Cargado correctamente -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSRF Token para AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Estilos adicionales -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display+SC:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&display=swap');

        /* Estilos para el texto del logo */
        .playfair-display-sc-bold {
            font-family: "Playfair Display SC", serif;
            font-weight: 700;
            font-style: normal;
            color: #ebe64b; /* Color amarillo para el texto del logo */
        }
        
        /* Estilos para la navbar */
        .navbar .nav-link {
            color: #ffffff !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }
        
        .navbar .nav-link:hover {
            color: #ebe64b !important;
        }
        
        .navbar .nav-link.active {
            color: #f01917 !important;
        }
        
        .navbar .dropdown-menu {
            border-top: 3px solid #f01917;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.175);
        }
        
        .navbar .dropdown-item:hover {
            background-color: rgba(235, 230, 75, 0.1);
            color: #033988;
        }
        
        .navbar .dropdown-item i {
            color: #033988;
            width: 20px;
            text-align: center;
        }

        /* Estilos generales */
        body {
            padding-top: 56px; /* Ajuste para la barra de navegación fija */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden; /* Previene scroll horizontal */
            width: 100%;
            max-width: 100%;
        }

        main {
            flex: 1 0 auto; /* Para que el contenido principal ocupe el espacio disponible */
        }

        /* Estilos para el sidebar */
        .sidebar {
            width: 250px;
            position: fixed;
            top: 56px; /* Altura de la barra de navegación */
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            background: linear-gradient(180deg, #033988 0%, #032d70 100%); /* Gradiente azul */
            overflow-y: auto;
            overflow-x: hidden; /* Prevenir scroll horizontal */
            border-right: 3px solid #ebe64b; /* Borde amarillo a la derecha */
            transition: all 0.3s ease;
            max-width: 250px; /* Asegurar que no exceda el ancho */
        }

        .sidebar .nav-link {
            color: #ffffff; /* Texto blanco */
            padding: 0.8rem 1.2rem;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            margin-bottom: 2px;
            position: relative;
            border-radius: 0 30px 30px 0;
            margin-right: 10px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(235, 230, 75, 0.15); /* Amarillo con opacidad */
            color: #ebe64b; /* Texto amarillo al hover */
            border-left: 4px solid #ebe64b; /* Borde izquierdo amarillo */
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link.active {
            background-color: rgba(240, 25, 23, 0.15); /* Rojo con opacidad */
            color: #ffffff; /* Texto blanco */
            font-weight: bold;
            border-left: 4px solid #f01917; /* Borde izquierdo rojo */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        /* Efecto de brillo en elementos activos */
        .sidebar .nav-link.active::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shine 2s infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%); }
            60%, 100% { transform: translateX(100%); }
        }

        .main-content {
            margin-left: 250px;
            padding: 10px;
            width: calc(100% - 250px);
            max-width: calc(100% - 250px);
            overflow-x: hidden;
        }
        
        /* Asegurar que las tablas se ajusten correctamente */
        .main-content table {
            width: 100%;
            max-width: 100%;
            table-layout: fixed;
        }
        
        /* Ajustar celdas de tabla para evitar desbordamiento */
        .main-content table td, .main-content table th {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        /* Estilos para los submenús */
        .sidebar .collapse {
            background-color: rgba(255, 255, 255, 0.05);
            margin: 0 10px 5px 5px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .sidebar .collapse .nav-link {
            padding-left: 2.5rem;
            font-size: 0.95rem;
            border-radius: 5px;
            margin: 2px 5px;
        }
        
        /* Estilos para íconos */
        .sidebar .nav-link i {
            color: #ebe64b; /* Iconos en amarillo */
            margin-right: 10px;
            width: 24px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            top: 1px;
        }
        
        .sidebar .nav-link:hover i {
            transform: scale(1.2);
        }
        
        /* Estilos para headers de secciones */
        .sidebar .nav-link[data-bs-toggle="collapse"] {
            background-color: rgba(0, 0, 0, 0.2);
            margin-top: 5px;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(235, 230, 75, 0.2);
        }
        
        /* Estilo para el chevron */
        .sidebar .fa-chevron-down {
            transition: transform 0.3s;
            color: #ebe64b;
        }
        
        .sidebar .nav-link[aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }

        /* Estilos responsivos */
        @media (max-width: 991.98px) {
            .sidebar {
                width: 100%;
                position: static;
                height: auto;
                margin-bottom: 20px;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                max-width: 100%;
                padding: 8px;
            }
        }
        
        /* Estilos para contenedores */
        .container, .container-fluid {
            width: 100%;
            max-width: 100%;
            padding-right: 10px;
            padding-left: 10px;
        }
    </style>
    
    @yield('styles')
</head>

<body>
    <!-- Barra de navegación superior -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: linear-gradient(135deg, #033988 70%, #f01917 100%); border-bottom: 3px solid #ebe64b;">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('images/logo-estrella.png') }}" alt="PRODUCCIÓN-ESTRELLA" height="40"> <span class="playfair-display-sc-bold">PRODUCCIÓN</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Menú izquierdo (solo para administradores) -->
                @auth
                @if(Auth::user()->id_roles == 1)
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">Inicio</a>
                    </li>
                </ul>
                @endif
                @endauth

                <!-- Menú derecho -->
                <ul class="navbar-nav ms-auto">
                    @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Iniciar Sesión</a>
                    </li>
                    @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            {{ Auth::user()->nombre_personal }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span class="dropdown-item-text">
                                    <i class="fas fa-store me-1"></i>
                                    {{ Auth::user()->tienda->nombre ?? 'Sin tienda' }}
                                </span>
                            </li>
                            <li>
                                <span class="dropdown-item-text">
                                    <i class="fas fa-building me-1"></i>
                                    {{ Auth::user()->area->nombre ?? 'Sin área' }}
                                </span>
                            </li>
                            <li>
                                <span class="dropdown-item-text">
                                    <i class="fas fa-user-tag me-1"></i>
                                    {{ Auth::user()->rol->nombre ?? 'Sin rol' }}
                                </span>
                            </li>
                            <li>
                                <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cambiarPasswordModal">
                                    <i class="fas fa-key me-1"></i> Cambiar Contraseña
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            @auth
            <div class="d-none d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        
                        <!-- Menú dinámico generado por MenuServiceProvider -->
                        @foreach($menuItems as $item)
                        @if(isset($item['visible']) && $item['visible'] && isset($item['route']) && Route::has($item['route']))
                            @if(isset($item['submenu']))
                            <li class="nav-item">
                                <a class="nav-link d-flex justify-content-between align-items-center {{ (request()->routeIs($item['route']) || request()->routeIs($item['route'].'*')) ? 'active' : '' }}"
                                    data-bs-toggle="collapse" href="#submenu-{{ $loop->index }}" role="button" aria-expanded="false">
                                    <span>
                                        <i class="{{ isset($item['icon']) ? $item['icon'] : 'fas fa-circle' }} me-2"></i>
                                        {{ $item['text'] ?? 'Menú' }}
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </a>
                                <div class="collapse {{ (request()->routeIs($item['route']) || request()->routeIs($item['route'].'*')) ? 'show' : '' }}" id="submenu-{{ $loop->index }}">
                                    <ul class="nav flex-column ms-3 mt-2">
                                        @foreach($item['submenu'] as $submenu)
                                        @if(isset($submenu['route']) && Route::has($submenu['route']))
                                        <li class="nav-item">
                                            <a class="nav-link {{ request()->routeIs($submenu['route']) ? 'active' : '' }}" 
                                               href="{{ generarRuta($submenu) }}">
                                                <i class="{{ $submenu['icon'] ?? 'fas fa-circle' }} me-2"></i>
                                                {{ $submenu['text'] }}
                                            </a>
                                        </li>
                                        @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                            @else
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}"
                                    href="{{ generarRuta($item) }}">
                                    <i class="{{ $item['icon'] }} me-2"></i>
                                    {{ $item['text'] }}
                                </a>
                            </li>
                            @endif
                        @endif
                        @endforeach
                        
                        <!-- Menú de Administración de Tablas para administradores (forzado) -->
                        @if(Auth::check() && Auth::user()->id_roles == 1)
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center"
                                data-bs-toggle="collapse" href="#adminTablesMenu" role="button" aria-expanded="false">
                                <span>
                                    <i class="fas fa-database me-2"></i>
                                    Administración de Tablas
                                </span>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <div class="collapse" id="adminTablesMenu">
                                <ul class="nav flex-column ms-3 mt-2">
                                    <!-- Gestión de Usuarios -->
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('usuarios.index') }}">
                                            <i class="fas fa-users me-2"></i>
                                            Usuarios
                                        </a>
                                    </li>
                                    <!-- Gestión de Roles -->
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('rols.index') }}">
                                            <i class="fas fa-user-tag me-2"></i>
                                            Roles
                                        </a>
                                    </li>
                                    <!-- Gestión de Turnos -->
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('turnos.index') }}">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            Turnos
                                        </a>
                                    </li>
                                    <!-- Gestión de Áreas -->
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('areas.index') }}">
                                            <i class="fas fa-map-marked-alt me-2"></i>
                                            Áreas
                                        </a>
                                    </li>
                                    <!-- Gestión de Tiendas -->
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('tiendas.index') }}">
                                            <i class="fas fa-store me-2"></i>
                                            Tiendas
                                        </a>
                                    </li>
                                    <!-- Otras tablas del sistema -->
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('estados.index') }}">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Estados
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('umedidas.index') }}">
                                            <i class="fas fa-balance-scale me-2"></i>
                                            Unidades de medida
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endif
                        
                    </ul>
                </div>
            </div>
            @endauth

            <!-- Main content -->
            <main class="main-content">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- jQuery (solo una vez, al final del body) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS Bundle (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Scripts de la aplicación -->
    @yield('scripts')
    @stack('scripts')
    
    <!-- Variable global para mensajes flash desde Laravel -->
    <script>
        // Inicializar objeto de mensajes flash
        var mensajesFlash = {};
        
        // Configurar mensajes desde la sesión
        @if(session()->has('success'))
            mensajesFlash.success = "{{ session('success') }}";
        @endif
        
        @if(session()->has('error'))
            mensajesFlash.error = "{{ session('error') }}";
        @endif
        
        @if(session()->has('warning'))
            mensajesFlash.warning = "{{ session('warning') }}";
        @endif
    </script>
    
    <!-- Cargar script de notificaciones -->
    <script src="{{ asset('js/notificaciones.js') }}"></script>

    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
    <!-- Agrega el BuscadorAjax aquí -->
    <script>
        class BuscadorAjax {
            constructor(config) {
                this.config = {
                    inputSelector: '',
                    resultsContainerSelector: '',
                    minChars: 2,
                    endpoint: '',
                    template: (item) => '',
                    onSelect: (item) => {},
                    ...config
                };

                this.init();
            }

            init() {
                const {
                    inputSelector,
                    resultsContainerSelector
                } = this.config;
                this.$input = $(inputSelector);
                this.$resultsContainer = $(resultsContainerSelector);

                this.setupEvents();
            }

            setupEvents() {
                let searchTimeout;

                this.$input.on('input', () => {
                    clearTimeout(searchTimeout);
                    const term = this.$input.val().trim();

                    if (term.length >= this.config.minChars) {
                        searchTimeout = setTimeout(() => this.search(term), 300);
                    } else {
                        this.$resultsContainer.hide().empty();
                    }
                });

                $(document).on('click', (e) => {
                    if (!$(e.target).closest([this.config.inputSelector, this.config.resultsContainerSelector].join(',')).length) {
                        this.$resultsContainer.hide();
                    }
                });
            }

            async search(term) {
                const {
                    endpoint,
                    template
                } = this.config;

                this.$resultsContainer.html('<div class="list-group-item">Buscando...</div>').show();

                try {
                    const response = await $.ajax({
                        url: endpoint,
                        type: 'GET',
                        data: {
                            term
                        },
                        dataType: 'json'
                    });

                    if (response.length > 0) {
                        this.$resultsContainer.empty();
                        response.forEach((item, index) => {
                            this.$resultsContainer.append(`
                                <a href="#" class="list-group-item list-group-item-action result-item" data-index="${index}">
                                    ${template(item)}
                                </a>
                            `);
                        });
                        this.$resultsContainer.show();

                        // Configurar evento de selección
                        this.$resultsContainer.find('.result-item').on('click', (e) => {
                            e.preventDefault();
                            const index = $(e.currentTarget).data('index');
                            this.config.onSelect(response[index]);
                            this.$resultsContainer.hide();
                        });
                    } else {
                        this.$resultsContainer.html('<div class="list-group-item">No se encontraron resultados</div>').show();
                    }
                } catch (error) {
                    console.error('Error en búsqueda:', error);
                    this.$resultsContainer.html('<div class="list-group-item text-danger">Error en la búsqueda</div>').show();
                }
            }
        }
    </script>
    
    <!-- Modal para cambiar contraseña -->
    <div class="modal fade" id="cambiarPasswordModal" tabindex="-1" aria-labelledby="cambiarPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="cambiarPasswordModalLabel"><i class="fas fa-key me-2"></i>Cambiar Contraseña</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="cambiarPasswordAlert" class="alert d-none"></div>
                    <form id="cambiarPasswordForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="clave_actual" class="form-label">Contraseña Actual</label>
                            <input type="password" class="form-control" id="clave_actual" name="clave_actual" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="clave_nueva" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="clave_nueva" name="clave_nueva" required>
                            <div class="form-text">La contraseña debe tener al menos 6 caracteres.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="clave_nueva_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" id="clave_nueva_confirmation" name="clave_nueva_confirmation" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnCambiarPassword">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Script para cambiar contraseña -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cambiarPasswordForm = document.getElementById('cambiarPasswordForm');
            const cambiarPasswordAlert = document.getElementById('cambiarPasswordAlert');
            const btnCambiarPassword = document.getElementById('btnCambiarPassword');
            
            btnCambiarPassword.addEventListener('click', function() {
                // Validar que las contraseñas coincidan
                const claveNueva = document.getElementById('clave_nueva').value;
                const claveNuevaConfirmation = document.getElementById('clave_nueva_confirmation').value;
                
                if (claveNueva !== claveNuevaConfirmation) {
                    mostrarAlerta('Las contraseñas no coinciden', 'danger');
                    return;
                }
                
                if (claveNueva.length < 6) {
                    mostrarAlerta('La contraseña debe tener al menos 6 caracteres', 'danger');
                    return;
                }
                
                // Enviar formulario mediante AJAX
                const formData = new FormData(cambiarPasswordForm);
                
                fetch('{{ route("perfil.cambiar-password") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        mostrarAlerta(data.error, 'danger');
                    } else if (data.success) {
                        mostrarAlerta(data.success, 'success');
                        // Limpiar el formulario
                        cambiarPasswordForm.reset();
                        // Cerrar el modal después de 2 segundos
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('cambiarPasswordModal'));
                            modal.hide();
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarAlerta('Error al procesar la solicitud', 'danger');
                });
            });
            
            function mostrarAlerta(mensaje, tipo) {
                cambiarPasswordAlert.textContent = mensaje;
                cambiarPasswordAlert.className = `alert alert-${tipo}`;
                // Ocultar la alerta después de 5 segundos si es de éxito
                if (tipo === 'success') {
                    setTimeout(() => {
                        cambiarPasswordAlert.className = 'alert d-none';
                    }, 5000);
                }
            }
        });
    </script>
</body>
</html>


