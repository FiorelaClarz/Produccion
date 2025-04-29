<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoCabecera extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pedidos_cab';
    protected $primaryKey = 'id_pedidos_cab';
    public $timestamps = false; // Desactivamos created_at y updated_at

    protected $fillable = [
        'id_usuarios',
        'id_tiendas_api',
        'fecha_created',
        'fecha_last_update',
        'hora_created',
        'hora_last_update',
        'esta_dentro_de_hora',
        'id_hora_limite',
        'doc_interno',
        'is_deleted',
        'status'
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'status' => 'boolean',
        'esta_dentro_de_hora' => 'boolean',
        'fecha_created' => 'datetime:Y-m-d',
        'fecha_last_update' => 'datetime:Y-m-d',
        'hora_created' => 'datetime:H:i:s',
        'hora_last_update' => 'datetime:H:i:s'
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

    // Relación con HoraLimite
    public function horaLimite()
    {
        return $this->belongsTo(HoraLimite::class, 'id_hora_limite');
    }

    // Relación con PedidoDetalle
    public function pedidosDetalle()
    {
        return $this->hasMany(PedidoDetalle::class, 'id_pedidos_cab');
    }
}
