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
        'fecha',
        'hora',
        'doc_interno'
    ];

    // Relación con EquipoCabecera
    public function equipo()
    {
        return $this->belongsTo(EquipoCabecera::class, 'id_equipos');
    }

    // Relación con ProduccionDetalle
    public function produccionesDetalle()
    {
        return $this->hasMany(ProduccionDetalle::class, 'id_produccion_cab');
    }
}