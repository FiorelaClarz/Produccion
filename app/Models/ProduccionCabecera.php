<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduccionCabecera extends Model
{
    use HasFactory;

    protected $table = 'produccion_cab';
    protected $primaryKey = 'id_produccion_cab';
    public $timestamps = true;

    protected $fillable = [
        'id_equipos',
        'id_turnos',
        'id_usuario',
        'fecha',
        'hora',
        'doc_interno',
        'created_at',
        'updated_at',
    ];

    // Relación con EquipoCabecera
    public function equipo()
    {
        return $this->belongsTo(EquipoCabecera::class, 'id_equipos');
    }

    // Relación con Turno
    public function turno()
    {
        return $this->belongsTo(Turno::class, 'id_turnos');
    }

    // Relación con Usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    // Relación con ProduccionDetalle
    public function produccionesDetalle()
    {
        return $this->hasMany(ProduccionDetalle::class, 'id_produccion_cab');
    }
}