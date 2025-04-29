<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UMedidaController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\EquipoController;
// use App\Http\Controllers\Auth;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth; // Agrega esta línea



use Illuminate\Http\Request; // Necesario para el Request
use App\Models\Producto;


Route::get('/', function () {
    return view('welcome');
});

Route::resource('rols', RolController::class)->except(['show']); // show no lo usa

Route::prefix('rols')->group(function () {
    Route::get('/', [RolController::class, 'index'])->name('rols.index');
    Route::get('/create', [RolController::class, 'create'])->name('rols.create');
    Route::post('/', [RolController::class, 'store'])->name('rols.store');
    Route::get('/{id}/edit', [RolController::class, 'edit'])->name('rols.edit');
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



Route::prefix('umedidas')->group(function () {
    Route::get('/', [UMedidaController::class, 'index'])->name('umedidas.index');
    Route::get('/create', [UMedidaController::class, 'create'])->name('umedidas.create');
    Route::post('/', [UMedidaController::class, 'store'])->name('umedidas.store');
    Route::get('/{id}', [UMedidaController::class, 'show'])->name('umedidas.show');
    Route::get('/{id}/edit', [UMedidaController::class, 'edit'])->name('umedidas.edit');
    Route::put('/{id}', [UMedidaController::class, 'update'])->name('umedidas.update');
    Route::delete('/{id}', [UMedidaController::class, 'destroy'])->name('umedidas.destroy');
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

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Home para otros roles
    Route::get('/home', function () {
        return view('home');
    })->name('home');

    // Rutas de recursos
    Route::resource('usuarios', UsuarioController::class);
    Route::resource('rols', RolController::class)->except(['show']);
    Route::resource('turnos', TurnoController::class);
    Route::resource('estados', EstadoController::class);
    Route::resource('areas', AreaController::class);
    Route::resource('tiendas', TiendaController::class);

    Route::prefix('recetas')->group(function () {
        Route::get('/', [RecetaController::class, 'index'])->name('recetas.index');
        Route::get('/create', [RecetaController::class, 'create'])->name('recetas.create');
        Route::post('/', [RecetaController::class, 'store'])->name('recetas.store');

        // Rutas AJAX primero
        Route::get('/buscar-productos', [RecetaController::class, 'buscarProductos'])->name('recetas.buscarProductos');
        Route::post('/agregar-ingrediente', [RecetaController::class, 'agregarIngrediente'])->name('recetas.agregarIngrediente');

        // Rutas con parámetros
        Route::get('/{id}', [RecetaController::class, 'show'])->name('recetas.show');
        Route::get('/{id}/edit', [RecetaController::class, 'edit'])->name('recetas.edit');
        Route::put('/{id}', [RecetaController::class, 'update'])->name('recetas.update');
        Route::delete('/{id}', [RecetaController::class, 'destroy'])->name('recetas.destroy');
    });

    Route::get('recetas/verificar-producto', [RecetaController::class, 'verificarProducto'])
        ->name('recetas.verificarProducto');

    // Para mostrar recetas
    Route::get('recetas/{id}', [RecetaController::class, 'show'])
        ->name('recetas.show')
        ->where('id', '[0-9]+');

    // Agregar esta ruta
    Route::patch('/recetas/{id}/toggle-status', [RecetaController::class, 'toggleStatus'])
        ->name('recetas.toggle-status');


    // Rutas para equipos
    Route::prefix('equipos')->group(function () {
        Route::get('/', [EquipoController::class, 'index'])->name('equipos.index');
        Route::get('/create', [EquipoController::class, 'create'])->name('equipos.create');
        Route::post('/', [EquipoController::class, 'store'])->name('equipos.store');
        Route::get('/{id}', [EquipoController::class, 'show'])->name('equipos.show');
        Route::get('/{id}/edit', [EquipoController::class, 'edit'])->name('equipos.edit');
        Route::put('/{id}', [EquipoController::class, 'update'])->name('equipos.update');
        Route::delete('/{id}', [EquipoController::class, 'destroy'])->name('equipos.destroy');
        // Route::patch('/{id}/toggle-status', [EquipoController::class, 'toggleStatus'])->name('equipos.toggle-status');
        Route::match(['PATCH', 'POST'], '/equipos/{id}/toggle-status', [EquipoController::class, 'toggleStatus'])
            ->name('equipos.toggle-status');
        Route::post('/{id}/registrar-salida', [EquipoController::class, 'registrarSalida'])->name('equipos.registrar-salida');
    });


    Route::prefix('pedidos')->group(function () {
        // 1. Primero las rutas fijas (sin parámetros)
        // Route::get('buscar/recetas', [PedidoController::class, 'buscarRecetas'])
        //     ->name('pedidos.buscar-recetas');
        Route::get('pedidos/buscar-recetas', [PedidoController::class, 'buscarRecetas'])->name('pedidos.buscar-recetas');

        // 2. Luego las rutas con parámetros
        Route::get('/', [PedidoController::class, 'index'])->name('pedidos.index');
        Route::get('/create', [PedidoController::class, 'create'])->name('pedidos.create');
        Route::post('/', [PedidoController::class, 'store'])->name('pedidos.store');
        Route::get('/{id}', [PedidoController::class, 'show'])->name('pedidos.show');
        Route::get('/{id}/edit', [PedidoController::class, 'edit'])->name('pedidos.edit');
        Route::put('/{id}', [PedidoController::class, 'update'])->name('pedidos.update');
        Route::delete('/{id}', [PedidoController::class, 'destroy'])->name('pedidos.destroy');

        // Rutas adicionales para acciones específicas
        Route::patch('/{id}/cancelar', [PedidoController::class, 'cancelar'])->name('pedidos.cancelar');
        Route::patch('/{id}/procesar', [PedidoController::class, 'procesar'])->name('pedidos.procesar');
    });
});
