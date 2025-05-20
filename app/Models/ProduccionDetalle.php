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
        'id_u_medidas_prodcc',
        'id_recetas_cab',
        'id_recetas_det',
        'id_areas',
        'cantidad_pedido',
        'cantidad_esperada',
        'cantidad_producida_real',
        'es_iniciado',
        'es_terminado',
        'es_cancelado',
        'costo_diseño',
        'subtotal_receta',
        'total_receta',
        'cant_harina',
        'observaciones',
        'es_personalizado',
        'pedidos_ids',
    'id_pedido_detalle'
    ];

    protected $casts = [
        'es_iniciado' => 'boolean',
        'es_terminado' => 'boolean',
        'es_cancelado' => 'boolean',
        'cantidad_pedido' => 'decimal:2',
        'cantidad_esperada' => 'decimal:2',
        'cantidad_producida_real' => 'decimal:2',
        'costo_diseño' => 'decimal:2',
        'subtotal_receta' => 'decimal:2',
        'total_receta' => 'decimal:2',
        'cant_harina' => 'decimal:2',
        'pedidos_ids' => 'array',
        'es_personalizado' => 'boolean'
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

    // Relación con UMedida (para pedido)
    public function uMedida()
    {
        return $this->belongsTo(UMedida::class, 'id_u_medidas');
    }

    // Relación con UMedida (para producción)
    public function uMedidaProd()
    {
        return $this->belongsTo(UMedida::class, 'id_u_medidas_prodcc');
    }

    // Relación con RecetaCabecera
    public function recetaCabecera()
    {
        return $this->belongsTo(RecetaCabecera::class, 'id_recetas_cab');
    }

    // Relación con RecetaDetalle
    public function recetaDetalle()
    {
        return $this->belongsTo(RecetaDetalle::class, 'id_recetas_det');
    }

    // Relación con Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_areas');
    }

    /**
     * Obtiene los pedidos asociados a este detalle de producción
     */
    public function pedidos()
    {
        return $this->belongsToMany(PedidoDetalle::class, 'pedidos_det', 'id_pedidos_det', 'id_pedidos_det')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }

    /**
     * Obtiene el pedido personalizado asociado (si existe)
     */
    public function pedidoPersonalizado()
    {
        return $this->belongsTo(PedidoDetalle::class, 'id_pedidos_det');
    }
}