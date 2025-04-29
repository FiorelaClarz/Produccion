<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipoCabecera extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipos_cab';
    protected $primaryKey = 'id_equipos_cab';
    public $timestamps = true;

    protected $fillable = [
        'id_usuarios',
        'id_areas',
        'id_turnos',
        'status',
        'is_deleted',
        'salida'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_deleted' => 'boolean',
        'salida' => 'datetime'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'salida'
    ];

    // Relación con Usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuarios');
    }

    // Relación con Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_areas');
    }

    // Relación con Turno
    public function turno()
    {
        return $this->belongsTo(Turno::class, 'id_turnos');
    }

    // Relación con EquipoDetalle
    public function equiposDetalle()
    {
        return $this->hasMany(EquipoDetalle::class, 'id_equipos_cab');
    }

    // Relación con ProduccionCabecera
    public function produccionesCabecera()
    {
        return $this->hasMany(ProduccionCabecera::class, 'id_equipos');
    }

    // Scope para equipos activos
    public function scopeActivos($query)
    {
        return $query->where('status', true)
                    ->where('is_deleted', false);
    }
}