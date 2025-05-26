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
        'id_areas',      // Nueva columna agregada - área de la merma
        'id_recetas',    // Nueva columna agregada - receta relacionada
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

    // No usamos relación con productos_api directamente
    // En su lugar, obtenemos el producto de la receta
    public function producto()
    {
        // Devolvemos un accessor que obtiene el ID del producto desde la receta
        // Esto evita tener que acceder a la tabla productos_api
        return $this->receta ? $this->receta->producto : null;
    }

    // Relación con UMedida
    public function uMedida()
    {
        return $this->belongsTo(UMedida::class, 'id_u_medidas');
    }

    // Relación con Área
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_areas');
    }

    // Relación con Receta
    public function receta()
    {
        return $this->belongsTo(RecetaCabecera::class, 'id_recetas');
    }
}