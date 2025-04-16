<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecetaCabecera extends Model
{
    use HasFactory;

    protected $table = 'recetas_cab';
    protected $primaryKey = 'id_recetas';
    public $timestamps = true;

    protected $fillable = [
        'id_areas',
        'id_productos_api',
        'nombre',
        'create_date',
        'last_update',
        'status',
        'is_deleted',
        'cant_rendimiento',
        'id_u_medidas'
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
    public function recetasDetalle()
    {
        return $this->hasMany(RecetaDetalle::class, 'id_recetas_cab');
    }

    // Relación con ProduccionDetalle
    public function produccionesDetalle()
    {
        return $this->hasMany(ProduccionDetalle::class, 'id_recetas');
    }
}