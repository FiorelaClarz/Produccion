<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\UsuarioController;
// use App\Http\Controllers\Auth;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth; // Agrega esta línea

Route::get('/', function () {
    return view('welcome');
});

Route::resource('rols', RolController::class)->except(['show']); // show no lo usa

Route::prefix('rols')->group(function () {
    Route::get('/', [RolController::class, 'index'])->name('rols.index');
    Route::get('/create', [RolController::class, 'create'])->name('rols.create'); // Cambiado de 'crear' a 'create'
    Route::post('/', [RolController::class, 'store'])->name('rols.store');
    Route::get('/{id}/edit', [RolController::class, 'edit'])->name('rols.edit'); // Cambiado de 'editar' a 'edit'
    Route::put('/{id}', [RolController::class, 'update'])->name('rols.update');
    Route::delete('/{id}', [RolController::class, 'destroy'])->name('rols.destroy');
});


Route::prefix('turnos')->group(function () {
    Route::get('/', [TurnoController::class, 'index'])->name('turnos.index');
    Route::get('/create', [TurnoController::class, 'create'])->name('turnos.create');
    Route::post('/', [TurnoController::class, 'store'])->name('turnos.store');
    Route::get('/{id}/edit', [TurnoController::class, 'edit'])->name('turnos.edit');
    Route::put('/{id}', [TurnoController::class, 'update'])->name('turnos.update');
    Route::delete('/{id}', [TurnoController::class, 'destroy'])->name('turnos.destroy');
    Route::get('/{id}', [TurnoController::class, 'show'])->name('turnos.show');
});


Route::prefix('estados')->group(function () {
    Route::get('/', [EstadoController::class, 'index'])->name('estados.index');
    Route::get('/create', [EstadoController::class, 'create'])->name('estados.create');
    Route::post('/', [EstadoController::class, 'store'])->name('estados.store');
    Route::get('/{id}', [EstadoController::class, 'show'])->name('estados.show');
    Route::get('/{id}/edit', [EstadoController::class, 'edit'])->name('estados.edit');
    Route::put('/{id}', [EstadoController::class, 'update'])->name('estados.update');
    Route::delete('/{id}', [EstadoController::class, 'destroy'])->name('estados.destroy');
});

Route::prefix('areas')->group(function () {
    Route::get('/', [AreaController::class, 'index'])->name('areas.index');
    Route::get('/create', [AreaController::class, 'create'])->name('areas.create');
    Route::post('/', [AreaController::class, 'store'])->name('areas.store');
    Route::get('/{id}', [AreaController::class, 'show'])->name('areas.show'); // Esta es la ruta importante
    Route::get('/{id}/edit', [AreaController::class, 'edit'])->name('areas.edit');
    Route::put('/{id}', [AreaController::class, 'update'])->name('areas.update');
    Route::delete('/{id}', [AreaController::class, 'destroy'])->name('areas.destroy');
});

Route::prefix('tiendas')->group(function () {
    Route::get('/', [TiendaController::class, 'index'])->name('tiendas.index');
    Route::get('/create', [TiendaController::class, 'create'])->name('tiendas.create');
    Route::post('/', [TiendaController::class, 'store'])->name('tiendas.store');
    Route::get('/{id}', [TiendaController::class, 'show'])->name('tiendas.show');
    Route::get('/{id}/edit', [TiendaController::class, 'edit'])->name('tiendas.edit');
    Route::put('/{id}', [TiendaController::class, 'update'])->name('tiendas.update');
    Route::delete('/{id}', [TiendaController::class, 'destroy'])->name('tiendas.destroy');
});


Route::prefix('usuarios')->group(function () {
    // Rutas adicionales para búsqueda (DEBEN IR ANTES de las rutas con parámetros)
    Route::get('/buscar-personal', [UsuarioController::class, 'buscarPersonal'])->name('usuarios.buscarPersonal');
    Route::get('/get-personal-data/{id}', [UsuarioController::class, 'getPersonalData'])->name('usuarios.getPersonalData');
    Route::get('/usuarios/verificar-personal', [UsuarioController::class, 'verificarPersonal'])->name('usuarios.verificarPersonal');
    // Rutas principales
    Route::get('/', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('/create', [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('/', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('/{id}', [UsuarioController::class, 'show'])->name('usuarios.show');
    Route::get('/{id}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
});


// Rutas de autenticación
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Ruta del dashboard (protegida por auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        // Verificar si el usuario es administrador
        if (Auth::user()->id_roles != 1) {
            return redirect('/')->with('error', 'No tienes permiso para acceder al dashboard');
        }

        // Obtener items del menú para administradores
        $menuItems = [
            [
                'text' => 'Dashboard',
                'route' => 'dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'visible' => true
            ],
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
            ]
        ];

        return view('dashboard', compact('menuItems'));
    })->name('dashboard');

    // Aquí puedes agregar el resto de tus rutas protegidas
    Route::resource('usuarios', UsuarioController::class);

    // Si necesitas la ruta de configuración, agrégala así:
    // Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion');
});
