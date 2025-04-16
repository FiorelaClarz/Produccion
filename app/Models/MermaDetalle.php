<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MermaDetalle extends Model
{
    use HasFactory;

    protected $table = 'mermas_det';
    protected $primaryKey = 'id_mermas_det';
    public $timestamps = true;

    protected $fillable = [
        'id_mermas_cab',
        'id_productos_api',
        'cantidad',
        'id_u_medidas',
        'obs',
        'is_deleted'
    ];

    protected $casts = [
        'is_deleted' => 'boolean'
    ];

    // Relación con MermaCabecera
    public function mermaCabecera()
    {
        return $this->belongsTo(MermaCabecera::class, 'id_mermas_cab');
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
}