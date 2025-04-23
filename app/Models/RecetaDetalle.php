<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecetaDetalle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recetas_det';
    protected $primaryKey = 'id_recetas_det';
    public $timestamps = true;

    protected $fillable = [
        'id_recetas_cab',
        'id_productos_api',
        'nombre',
        'cantidad',
        'cant_presentacion',
        'id_u_medidas',
        'costo_unitario',
        'subtotal_receta'
    ];

    // Relación con RecetaCabecera
    public function cabecera()
    {
        return $this->belongsTo(RecetaCabecera::class, 'id_recetas_cab');
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