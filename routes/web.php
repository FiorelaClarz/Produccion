<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;

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