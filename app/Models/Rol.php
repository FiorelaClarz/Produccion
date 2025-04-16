<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;
    
    protected $table = 'rols';
    protected $primaryKey = 'id_roles';
    public $timestamps = true; // Esto es redundante, es el valor por defecto
    
    protected $fillable = [
        'nombre',
        'status',  // Agregado para que sea asignable en masa
        // create_date y last_update no deberían ser fillable
        // is_deleted no debería ser fillable (se manipula con métodos)
    ];
    
    protected $casts = [
        'status' => 'boolean',
        'is_deleted' => 'boolean',
        // Laravel 8+ usa $casts para fechas también
        'create_date' => 'datetime',
        'last_update' => 'datetime'
    ];

    // Para Laravel < 8, mantener $dates
    protected $dates = [
        'create_date',
        'last_update'
    ];

    /**
     * Scope para roles no eliminados
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
}