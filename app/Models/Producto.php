<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    
    protected $table = 'productos';
    protected $primaryKey = 'id_item';
    public $timestamps = true;
    public $incrementing = false; // Importante para claves primarias que no son autoincrementales
    
    protected $fillable = [
        'id_item',
        'id_area',
        'area',
        'codigo',
        'nombre',
        'costo',
        'ref_venta',
        'margen',
        'id_impuesto',
        'unspsc',
        'impuesto',
        'id_categoria',
        'id_presentacion',
        'presentacion',
        'percepcion',
        'id_marca',
        'marca',
        'categoria',
        'id_sub_categoria',
        'sub_categoria',
        'url',
        'condicion',
        'id_item_relacion',
        'item_cantidad_relacion',
        'arti_por',
        'aplicacion',
        'estatus_mayor',
        'precio_mayor',
        'costo_anterior',
        'descuento1',
        'descuento2',
        'venta'
    ];
    
    protected $casts = [
        'fecha_actualizacion' => 'datetime'
    ];
}