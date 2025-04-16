<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduccionDetalle extends Model
{
    use HasFactory;

    protected $table = 'produccion_det';
    protected $primaryKey = 'id_produccion_det';
    public $timestamps = true;

    protected $fillable = [
        'id_produccion_cab',
        'id_productos_api',
        'id_u_medidas',
        'id_recetas',
        'id_areas',
        'cantidad_pedido',
        'cantidad_producida_real',
        'es_iniciado',
        'es_terminado',
        'es_cancelado',
        'costo_diseño',
        'total_receta',
        'cant_harina'
    ];

    protected $casts = [
        'es_iniciado' => 'boolean',
        'es_terminado' => 'boolean',
        'es_cancelado' => 'boolean'
    ];

    // Relación con ProduccionCabecera
    public function produccionCabecera()
    {
        return $this->belongsTo(ProduccionCabecera::class, 'id_produccion_cab');
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

    // Relación con RecetaCabecera
    public function receta()
    {
        return $this->belongsTo(RecetaCabecera::class, 'id_recetas');
    }

    // Relación con Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_areas');
    }
}