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
            ]
        ];

        // Menú para Rol 3
        $rol3Menu = [
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
            ]
        ];

        switch ($roleId) {
            case 1: // Admin
                return array_merge($baseMenu, $adminMenu);
            case 2: // Gerencia
                return array_merge($baseMenu, $gerenciaMenu);
            case 3: // Rol 3
                return array_merge($baseMenu, $rol3Menu);
            default:
                return $baseMenu;
        }
    }
}