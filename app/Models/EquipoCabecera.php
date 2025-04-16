<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipoCabecera extends Model
{
    use HasFactory;

    protected $table = 'equipos_cab';
    protected $primaryKey = 'id_equipos_cab';
    public $timestamps = true;

    protected $fillable = [
        'id_usuarios',
        'id_areas',
        'id_turnos',
        'fecha_ingreso',
        'fecha_salida',
        'hora_ingreso',
        'hora_salida',
        'create_date',
        'last_update',
        'is_deleted'
    ];

    protected $casts = [
        'is_deleted' => 'boolean'
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
}