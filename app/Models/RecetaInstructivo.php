<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecetaInstructivo extends Model
{
    use HasFactory;

    protected $table = 'recetas_instructivos';
    protected $primaryKey = 'id_recetas_instructivos';
    public $timestamps = true;

    protected $fillable = [
        'id_recetas',
        'titulo',
        'instrucciones',
        'version',
        'is_active'
    ];

    protected $casts = [
        'instrucciones' => 'array',
        'is_active' => 'boolean'
    ];

    // Relación con RecetaCabecera
    public function receta()
    {
        return $this->belongsTo(RecetaCabecera::class, 'id_recetas');
    }
}