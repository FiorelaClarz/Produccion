<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\EstadoController;

Route::get('/', function () {
    return view('welcome');
});

// Versión mejorada usando resource (más limpio y estándar)
Route::resource('rols', RolController::class)->except(['show']); // Excluyo show si no lo usas

// O si prefieres mantener tu versión actual, corrige el nombre de ruta para create:
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
