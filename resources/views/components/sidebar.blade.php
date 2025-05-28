<aside class="sidebar bg-dark text-white" style="width: 250px; min-height: 100vh; position: fixed;">
    <div class="sidebar-header p-3 text-center">
        <h4>Sistema de Gestión</h4>
    </div>
    <ul class="nav flex-column">
        <!-- Opciones comunes para todos los roles -->
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link text-white">
                <i class="fas fa-home me-2"></i> Inicio
            </a>
        </li>
        
        <!-- Opciones para Administrador -->
        @can('admin-access')
        <li class="nav-item">
            <a class="nav-link text-white" data-bs-toggle="collapse" href="#adminMenu">
                <i class="fas fa-database me-2"></i> Administración de Tablas
            </a>
            <div class="collapse show" id="adminMenu">
                <ul class="nav flex-column ps-4">
                    <!-- Gestión de Usuarios -->
                    <li class="nav-item">
                        <a href="{{ route('usuarios.index') }}" class="nav-link text-white">
                            <i class="fas fa-users me-2"></i> Usuarios
                        </a>
                    </li>
                    <!-- Gestión de Roles -->
                    <li class="nav-item">
                        <a href="{{ route('rols.index') }}" class="nav-link text-white">
                            <i class="fas fa-user-tag me-2"></i> Roles
                        </a>
                    </li>
                    <!-- Gestión de Turnos -->
                    <li class="nav-item">
                        <a href="{{ route('turnos.index') }}" class="nav-link text-white">
                            <i class="fas fa-calendar-alt me-2"></i> Turnos
                        </a>
                    </li>
                    <!-- Gestión de Áreas -->
                    <li class="nav-item">
                        <a href="{{ route('areas.index') }}" class="nav-link text-white">
                            <i class="fas fa-map-marked-alt me-2"></i> Áreas
                        </a>
                    </li>
                    <!-- Gestión de Tiendas -->
                    <li class="nav-item">
                        <a href="{{ route('tiendas.index') }}" class="nav-link text-white">
                            <i class="fas fa-store me-2"></i> Tiendas
                        </a>
                    </li>
                    <!-- Otras tablas del sistema -->
                    <li class="nav-item">
                        <a href="{{ route('estados.index') }}" class="nav-link text-white">
                            <i class="fas fa-info-circle me-2"></i> Estados
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('umedidas.index') }}" class="nav-link text-white">
                            <i class="fas fa-balance-scale me-2"></i> Unidades de medida
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        @endcan
        
        <!-- Opciones para Personal -->
        <li class="nav-item">
            <a class="nav-link text-white" data-bs-toggle="collapse" href="#personalMenu">
                <i class="fas fa-user-tie me-2"></i> Operaciones
            </a>
            <div class="collapse show" id="personalMenu">
                <ul class="nav flex-column ps-4">
                    <li class="nav-item">
                        <a href="{{ route('turnos.index') }}" class="nav-link text-white">
                            <i class="fas fa-calendar-alt me-2"></i> Turnos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reportes.index') }}" class="nav-link text-white">
                            <i class="fas fa-chart-bar me-2"></i> Reportes
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
</aside>

<style>
    .sidebar {
        transition: all 0.3s;
    }
    .sidebar .nav-link {
        transition: all 0.2s;
    }
    .sidebar .nav-link:hover {
        background-color: rgba(255,255,255,0.1);
    }
    .sidebar .nav-item .active {
        background-color: rgba(255,255,255,0.2);
    }
</style>