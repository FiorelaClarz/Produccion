<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoCabecera extends Model
{
    use HasFactory;

    protected $table = 'pedidos_cab';
    protected $primaryKey = 'id_pedidos_cab';
    public $timestamps = true;

    protected $fillable = [
        'id_usuarios',
        'id_tiendas_api',
        'fecha_created',
        'fecha_last_update',
        'hora_created',
        'hora_last_update',
        'esta_dentro_de_hora',
        'hora_limite',
        'doc_interno',
        'id_estados',
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

    // Relación con Tienda
    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tiendas_api');
    }

    // Relación con Estado
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_estados');
    }

    // Relación con PedidoDetalle
    public function pedidosDetalle()
    {
        return $this->hasMany(PedidoDetalle::class, 'id_pedidos_cab');
    }
}