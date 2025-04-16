<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoDetalle extends Model
{
    use HasFactory;

    protected $table = 'pedidos_det';
    protected $primaryKey = 'id_pedidos_det';
    public $timestamps = true;

    protected $fillable = [
        'id_pedidos_cab',
        'id_productos_api',
        'id_areas',
        'cantidad',
        'id_u_medidas',
        'es_personalizado',
        'descripcion',
        'foto_referencial',
        'is_deleted'
    ];

    protected $casts = [
        'es_personalizado' => 'boolean',
        'is_deleted' => 'boolean'
    ];

    // Relación con PedidoCabecera
    public function pedidoCabecera()
    {
        return $this->belongsTo(PedidoCabecera::class, 'id_pedidos_cab');
    }

    // Relación con Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_productos_api', 'id_item');
    }

    // Relación con Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_areas');
    }

    // Relación con UMedida
    public function uMedida()
    {
        return $this->belongsTo(UMedida::class, 'id_u_medidas');
    }
}