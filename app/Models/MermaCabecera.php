<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MermaCabecera extends Model
{
    use HasFactory;

    protected $table = 'mermas_cab';
    protected $primaryKey = 'id_mermas_cab';
    public $timestamps = true;

    protected $fillable = [
        'id_usuarios',
        'id_tiendas_api',
        'fecha_registro',
        'hora_registro',
        'last_update',
        'is_deleted'
    ];
    
    protected $attributes = [
        'is_deleted' => false
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

    // Relación con MermaDetalle
    public function mermasDetalle()
    {
        return $this->hasMany(MermaDetalle::class, 'id_mermas_cab');
    }
}