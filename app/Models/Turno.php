<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;
    
    protected $table = 'turnos';
    protected $primaryKey = 'id_turnos';
    public $timestamps = true;
    
    protected $fillable = [
        'nombre',
        'id_horas_limite',
        'create_date',
        'last_update',
        'status',
        'is_deleted'
    ];
    
    protected $casts = [
        'status' => 'boolean',
        'is_deleted' => 'boolean'
    ];

    // Relación con HoraLimite
    public function horaLimite()
    {
        return $this->belongsTo(HoraLimite::class, 'id_horas_limite');
    }
}