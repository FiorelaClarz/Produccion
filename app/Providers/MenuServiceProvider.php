<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class MenuServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        view()->composer('*', function ($view) {
            $menuItems = [];

            if (Auth::check()) {
                $user = Auth::user();
                $menuItems = $this->getMenuItemsForRole($user->id_roles);
            }

            $view->with('menuItems', $menuItems);
        });
    }
    
    /**
     * Obtiene los elementos del menú según el rol del usuario
     *
     * @param int $roleId ID del rol del usuario
     * @return array Arreglo con los elementos del menú correspondientes al rol
     */
    protected function getMenuItemsForRole($roleId)
    {
        // Construir los menús para cada rol
        $baseMenu = $this->getBaseMenu();
        
        switch ($roleId) {
            case 1: // Administrador
                return array_merge($baseMenu, $this->getAdminMenu());
            case 2: // Gerencia
                return array_merge($baseMenu, $this->getGerenciaMenu());
            case 3: // Personal
                return array_merge($baseMenu, $this->getPersonalMenu());
            case 4: // Operador
                return array_merge($baseMenu, $this->getOperadorMenu());
            default:
                return $baseMenu;
        }
    }
    
    /**
     * Menú base común a todos los roles autenticados
     * 
     * @return array
     */
    private function getBaseMenu()
    {
        return [
            [
                'text' => 'Inicio',
                'route' => 'home',
                'icon' => 'fas fa-home',
                'visible' => true
            ],
            [
                'text' => 'Mi Perfil',
                'route' => 'usuarios.show',
                'icon' => 'fas fa-user-circle',
                'params' => ['id' => Auth::id()],
                'visible' => true
            ]
        ];
    }
    
    /**
     * Menú para el rol Administrador
     * 
     * @return array
     */
    private function getAdminMenu()
    {
        return [
            // Administración del sistema - Menú destacado para administrador
            [
                'text' => 'Administración de Tablas',
                'icon' => 'fas fa-database',
                'visible' => true,
                'submenu' => [
                    // Gestión de Usuarios
                    [
                        'text' => 'Usuarios',
                        'route' => 'usuarios.index',
                        'icon' => 'fas fa-users'
                    ],
                    // Gestión de Roles
                    [
                        'text' => 'Roles',
                        'route' => 'rols.index',
                        'icon' => 'fas fa-user-tag'
                    ],
                    // Gestión de Turnos
                    [
                        'text' => 'Turnos',
                        'route' => 'turnos.index',
                        'icon' => 'fas fa-calendar-alt'
                    ],
                    // Gestión de Áreas
                    [
                        'text' => 'Áreas',
                        'route' => 'areas.index',
                        'icon' => 'fas fa-map-marked-alt'
                    ],
                    // Gestión de Tiendas
                    [
                        'text' => 'Tiendas',
                        'route' => 'tiendas.index',
                        'icon' => 'fas fa-store'
                    ],
                    // Otras tablas del sistema
                    [
                        'text' => 'Estados',
                        'route' => 'estados.index',
                        'icon' => 'fas fa-info-circle'
                    ],
                    [
                        'text' => 'Unidades de medida',
                        'route' => 'umedidas.index',
                        'icon' => 'fas fa-balance-scale'
                    ]
                ]
            ],
            // Otras secciones del menú para administrador
            [
                'text' => 'Configuración',
                'icon' => 'fas fa-cogs',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Horarios Límite',
                        'route' => 'hora-limites.index',
                        'icon' => 'fas fa-clock'
                    ]
                ]
            ],
            // Recetas
            [
                'text' => 'Recetas',
                'route' => 'recetas.index',
                'icon' => 'fas fa-utensils',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Todas las Recetas',
                        'route' => 'recetas.index',
                        'icon' => 'fas fa-list'
                    ],
                    [
                        'text' => 'Nueva Receta',
                        'route' => 'recetas.create',
                        'icon' => 'fas fa-plus-circle'
                    ]
                ]
            ],
            // Equipos
            [
                'text' => 'Equipos',
                'route' => 'equipos.index',
                'icon' => 'fas fa-users-cog',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Todos los Equipos',
                        'route' => 'equipos.index',
                        'icon' => 'fas fa-list'
                    ],
                    [
                        'text' => 'Nuevo Equipo',
                        'route' => 'equipos.create',
                        'icon' => 'fas fa-plus-circle'
                    ]
                ]
            ],
            // Pedidos
            [
                'text' => 'Pedidos',
                'route' => 'pedidos.index',
                'icon' => 'fas fa-clipboard-list',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Todos los Pedidos',
                        'route' => 'pedidos.index',
                        'icon' => 'fas fa-list'
                    ],
                    [
                        'text' => 'Nuevo Pedido',
                        'route' => 'pedidos.create',
                        'icon' => 'fas fa-plus-circle'
                    ],
                    [
                        'text' => 'Horas Límite',
                        'route' => 'hora-limites.index',
                        'icon' => 'fas fa-clock'
                    ]
                ]
            ],
            // Producción
            [
                'text' => 'Producción',
                'route' => 'produccion.index',
                'icon' => 'fas fa-industry',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Todas las Producciones',
                        'route' => 'produccion.index',
                        'icon' => 'fas fa-list'
                    ],
                    [
                        'text' => 'Vista Administrador',
                        'route' => 'produccion.index-admin',
                        'icon' => 'fas fa-user-shield'
                    ],
                    [
                        'text' => 'Reporte por Períodos',
                        'route' => 'produccion.periodos',
                        'icon' => 'fas fa-chart-bar'
                    ],
                    [
                        'text' => 'Exportar a Excel',
                        'route' => 'produccion.exportar-excel',
                        'icon' => 'fas fa-file-excel'
                    ],
                    [
                        'text' => 'Exportar a PDF',
                        'route' => 'produccion.exportar-pdf',
                        'icon' => 'fas fa-file-pdf'
                    ]
                ]
            ],
            // Mermas
            [
                'text' => 'Mermas',
                'route' => 'mermas.index',
                'icon' => 'fas fa-trash-alt',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Todas las Mermas',
                        'route' => 'mermas.index',
                        'icon' => 'fas fa-list'
                    ],
                    [
                        'text' => 'Registrar Merma',
                        'route' => 'mermas.create',
                        'icon' => 'fas fa-plus-circle'
                    ],
                    [
                        'text' => 'Generar PDF',
                        'route' => 'mermas.pdf-multiple',
                        'icon' => 'fas fa-file-pdf'
                    ]
                ]
            ],
            // Comparativo
            [
                'text' => 'Comparativo',
                'route' => 'produccion.comparativo',
                'icon' => 'fas fa-chart-pie',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Ver Comparativo',
                        'route' => 'produccion.comparativo',
                        'icon' => 'fas fa-chart-line'
                    ],
                    [
                        'text' => 'Exportar a PDF',
                        'route' => 'produccion.comparativo.pdf',
                        'icon' => 'fas fa-file-pdf'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Menú para el rol de Gerencia
     * 
     * @return array
     */
    private function getGerenciaMenu()
    {
        return [
            // Gestión de Áreas y Tiendas
            [
                'text' => 'Gestión de Ubicaciones',
                'icon' => 'fas fa-map-marker-alt',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Áreas',
                        'route' => 'areas.index',
                        'icon' => 'fas fa-map-marked-alt'
                    ],
                    [
                        'text' => 'Tiendas',
                        'route' => 'tiendas.index',
                        'icon' => 'fas fa-store'
                    ]
                ]
            ],
            // Pedidos
            [
                'text' => 'Pedidos',
                'route' => 'pedidos.index',
                'icon' => 'fas fa-clipboard-list',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Todos los Pedidos',
                        'route' => 'pedidos.index',
                        'icon' => 'fas fa-list'
                    ]
                ]
            ],
            // Producción
            [
                'text' => 'Producción',
                'route' => 'produccion.index',
                'icon' => 'fas fa-industry',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Todas las Producciones',
                        'route' => 'produccion.index',
                        'icon' => 'fas fa-list'
                    ],
                    [
                        'text' => 'Reporte por Períodos',
                        'route' => 'produccion.periodos',
                        'icon' => 'fas fa-chart-bar'
                    ],
                    [
                        'text' => 'Exportar a Excel',
                        'route' => 'produccion.exportar-excel',
                        'icon' => 'fas fa-file-excel'
                    ],
                    [
                        'text' => 'Exportar a PDF',
                        'route' => 'produccion.exportar-pdf',
                        'icon' => 'fas fa-file-pdf'
                    ]
                ]
            ],
            // Mermas
            [
                'text' => 'Mermas',
                'route' => 'mermas.index',
                'icon' => 'fas fa-trash-alt',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Todas las Mermas',
                        'route' => 'mermas.index',
                        'icon' => 'fas fa-list'
                    ],
                    [
                        'text' => 'Generar PDF',
                        'route' => 'mermas.pdf-multiple',
                        'icon' => 'fas fa-file-pdf'
                    ]
                ]
            ],
            // Comparativo
            [
                'text' => 'Comparativo',
                'route' => 'produccion.comparativo',
                'icon' => 'fas fa-chart-pie',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Ver Comparativo',
                        'route' => 'produccion.comparativo',
                        'icon' => 'fas fa-chart-line'
                    ],
                    [
                        'text' => 'Exportar a PDF',
                        'route' => 'produccion.comparativo.pdf',
                        'icon' => 'fas fa-file-pdf'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Menú para el rol de Personal de producción
     * 
     * @return array
     */
    private function getPersonalMenu()
    {
        return [
            // Equipos de producción
            [
                'text' => 'Equipos',
                'route' => 'equipos.index',
                'icon' => 'fas fa-users-cog',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Todos los Equipos',
                        'route' => 'equipos.index',
                        'icon' => 'fas fa-list'
                    ]
                ]
            ],
            // Producción - Vista Personal
            [
                'text' => 'Producción',
                'route' => 'produccion.index-personal',
                'icon' => 'fas fa-industry',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Panel de Producción',
                        'route' => 'produccion.index-personal',
                        'icon' => 'fas fa-tasks'
                    ],
                    [
                        'text' => 'Mis Producciones',
                        'route' => 'produccion.index-personal',
                        'icon' => 'fas fa-list'
                    ],
                    [
                        'text' => 'Marcar Salida',
                        'route' => 'equipos.registrar-salida',
                        'icon' => 'fas fa-sign-out-alt',
                        'params' => ['id' => 'active_equipment_id']
                    ]
                ]
            ],
            // Recetas - Solo visualización
            [
                'text' => 'Recetas',
                'route' => 'recetas.index',
                'icon' => 'fas fa-utensils',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Ver Recetas',
                        'route' => 'recetas.index',
                        'icon' => 'fas fa-list'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Menú para el rol de Operador
     * 
     * @return array
     */
    private function getOperadorMenu()
    {
        return [
            // Producción limitada para operadores
            [
                'text' => 'Producción',
                'route' => 'produccion.index-personal',
                'icon' => 'fas fa-industry',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Panel de Producción',
                        'route' => 'produccion.index-personal',
                        'icon' => 'fas fa-tasks'
                    ],
                    [
                        'text' => 'Mis Producciones',
                        'route' => 'produccion.index',
                        'icon' => 'fas fa-list'
                    ],
                    [
                        'text' => 'Marcar Salida',
                        'route' => 'equipos.registrar-salida',
                        'icon' => 'fas fa-sign-out-alt',
                        'params' => ['id' => 'active_equipment_id']
                    ]
                ]
            ],
            // Recetas - Solo visualización
            [
                'text' => 'Recetas',
                'route' => 'recetas.index',
                'icon' => 'fas fa-utensils',
                'visible' => true,
                'access' => 'view_only',
                'submenu' => [
                    [
                        'text' => 'Ver Recetas',
                        'route' => 'recetas.index',
                        'icon' => 'fas fa-list'
                    ]
                ]
            ],
            // Mermas - Solo visualización
            [
                'text' => 'Mermas',
                'route' => 'mermas.index',
                'icon' => 'fas fa-trash-alt',
                'visible' => true,
                'access' => 'view_only',
                'submenu' => [
                    [
                        'text' => 'Ver Mermas',
                        'route' => 'mermas.index',
                        'icon' => 'fas fa-list'
                    ]
                ]
            ]
        ];
    }
}

