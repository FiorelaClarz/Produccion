<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoraLimite extends Model
{
    use HasFactory;

    protected $table = 'hora_limites';
    protected $primaryKey = 'id_hora_limite';
    public $timestamps = true;

    protected $fillable = [
        'hora_limite',
        'descripcion',
        'status',
        'is_deleted'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_deleted' => 'boolean',
        'hora_limite' => 'string'
    ];
}