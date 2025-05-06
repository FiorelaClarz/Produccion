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

    protected function getMenuItemsForRole($roleId)
    {
        // Menú base común a todos los roles autenticados
        $baseMenu = [
            [
                'text' => 'Inicio',
                'route' => 'home',
                'icon' => 'fas fa-home',
                'visible' => true
            ]
        ];

        // Menú para Administrador (id_roles == 1)
        $adminMenu = [
            [
                'text' => 'Usuarios',
                'route' => 'usuarios.index',
                'icon' => 'fas fa-users',
                'visible' => true
            ],
            [
                'text' => 'Roles',
                'route' => 'rols.index',
                'icon' => 'fas fa-user-tag',
                'visible' => true
            ],
            [
                'text' => 'Turnos',
                'route' => 'turnos.index',
                'icon' => 'fas fa-calendar-alt',
                'visible' => true
            ],
            [
                'text' => 'Áreas',
                'route' => 'areas.index',
                'icon' => 'fas fa-map-marked-alt',
                'visible' => true
            ],
            [
                'text' => 'Tiendas',
                'route' => 'tiendas.index',
                'icon' => 'fas fa-store',
                'visible' => true
            ],
            [
                'text' => 'Estados',
                'route' => 'estados.index',
                'icon' => 'fas fa-info-circle',
                'visible' => true
            ],
            [
                'text' => 'Unidades de medida',
                'route' => 'umedidas.index',
                'icon' => 'fas fa-balance-scale',
                'visible' => true
            ],
            [
                'text' => 'Recetas',
                'route' => 'recetas.index',
                'icon' => 'fas fa-utensils',
                'visible' => true
            ],
            [
                'text' => 'Equipos',
                'route' => 'equipos.index',
                'icon' => 'fas fa-users-cog',
                'visible' => true
            ],
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
            [
                'text' => 'Horas Límite',
                'route' => 'hora-limites.index',
                'icon' => 'fas fa-clock',
                'visible' => true
            ],
            // Nuevo menú para Producción (Admin)
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
                        'text' => 'Nueva Producción',
                        'route' => 'produccion.create',
                        'icon' => 'fas fa-plus-circle'
                    ],
                    [
                        'text' => 'Reportes',
                        'route' => 'produccion.reportes',
                        'icon' => 'fas fa-chart-bar'
                    ]
                ]
            ]
        ];

        // Menú para Gerencia (id_roles == 2)
        $gerenciaMenu = [
            [
                'text' => 'Áreas',
                'route' => 'areas.index',
                'icon' => 'fas fa-map-marked-alt',
                'visible' => true
            ],
            [
                'text' => 'Tiendas',
                'route' => 'tiendas.index',
                'icon' => 'fas fa-store',
                'visible' => true
            ],
            // Menú producción para gerencia
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
                        'text' => 'Reportes',
                        'route' => 'produccion.reportes',
                        'icon' => 'fas fa-chart-bar'
                    ]
                ]
            ]
        ];

        // PERSONAL (id_roles == 3)
        $personal = [
            [
                'text' => 'Roles',
                'route' => 'rols.index',
                'icon' => 'fas fa-user-tag',
                'visible' => true
            ],
            [
                'text' => 'Turnos',
                'route' => 'turnos.index',
                'icon' => 'fas fa-calendar-alt',
                'visible' => true
            ],
            [
                'text' => 'Equipos',
                'route' => 'equipos.index',
                'icon' => 'fas fa-users-cog',
                'visible' => true
            ],
            // Menú producción para personal
            [
                'text' => 'Producción',
                'route' => 'produccion.index',
                'icon' => 'fas fa-industry',
                'visible' => true,
                'submenu' => [
                    [
                        'text' => 'Registrar Producción',
                        'route' => 'produccion.index',
                        'icon' => 'fas fa-edit'
                    ],
                    [
                        'text' => 'Mis Producciones',
                        'route' => 'produccion.index',
                        'icon' => 'fas fa-list'
                    ]
                ]
            ]
        ];
        
        // OPERADOR (id_roles == 4)
        $operador = [
            [
                'text' => 'Roles',
                'route' => 'rols.index',
                'icon' => 'fas fa-user-tag',
                'visible' => true
            ],
            [
                'text' => 'Turnos',
                'route' => 'turnos.index',
                'icon' => 'fas fa-calendar-alt',
                'visible' => true
            ],
            [
                'text' => 'Equipos',
                'route' => 'equipos.index',
                'icon' => 'fas fa-users-cog',
                'visible' => true
            ],
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
            // Menú producción para operador
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
                        'text' => 'Reportes',
                        'route' => 'produccion.reportes',
                        'icon' => 'fas fa-chart-bar'
                    ]
                ]
            ]
        ];

        switch ($roleId) {
            case 1: // Admin
                return array_merge($baseMenu, $adminMenu);
            case 2: // Gerencia
                return array_merge($baseMenu, $gerenciaMenu);
            case 3: // Personal (Rol 3)
                return array_merge($baseMenu, $personal);
            case 4: // Operador (Rol 4)
                return array_merge($baseMenu, $operador);
            default:
                return $baseMenu;
        }
    }
}