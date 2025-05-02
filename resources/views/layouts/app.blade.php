<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'Producción'))</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- CSRF Token para AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Estilos adicionales -->
    <style>
        body {
            padding-top: 56px; /* Para la barra de navegación fija */
        }

        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            z-index: 1000;
            padding: 20px 0;
            overflow-x: hidden;
            overflow-y: auto;
            background-color: #343a40;
            width: 250px;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 10px 15px;
        }

        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }

        @media (max-width: 767.98px) {
            .sidebar {
                width: 100%;
                position: relative;
                top: 0;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
    
    @yield('styles')
</head>

<body>
    <!-- Barra de navegación superior -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Producción') }}
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
            <div class="d-none d-md-block sidebar bg-dark">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        @foreach($menuItems as $item)
                        @if($item['visible'] && Route::has($item['route']))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}"
                                href="{{ route($item['route']) }}">
                                <i class="{{ $item['icon'] }} me-2"></i>
                                {{ $item['text'] }}
                            </a>
                        </li>
                        @endif
                        @endforeach
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
</body>
</html>