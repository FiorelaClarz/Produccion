<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecetaCabecera extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recetas_cab';
    protected $primaryKey = 'id_recetas';
    public $timestamps = true;

    protected $fillable = [
        'id_areas',
        'id_productos_api',
        'id_u_medidas',
        'nombre',
        'status',
        'is_deleted',
        'cant_rendimiento',
        'constante_crecimiento',
        'constante_peso_lata'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_deleted' => 'boolean'
    ];

    // Relación con Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_areas');
    }

    // Relación con Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_productos_api', 'id_item');
    }

    // Relación con UMedida
    public function uMedida()
    {
        return $this->belongsTo(UMedida::class, 'id_u_medidas');
    }

    // Relación con RecetaDetalle
    public function detalles()
    {
        return $this->hasMany(RecetaDetalle::class, 'id_recetas_cab');
    }

    // Relación con ProduccionDetalle
    public function produccionesDetalle()
    {
        return $this->hasMany(ProduccionDetalle::class, 'id_recetas');
    }
}