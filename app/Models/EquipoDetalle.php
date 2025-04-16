<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipoDetalle extends Model
{
    use HasFactory;

    protected $table = 'equipos_det';
    protected $primaryKey = 'id_equipos_det';
    public $timestamps = true;

    protected $fillable = [
        'id_equipos_cab',
        'id_personal_api',
        'status',
        'is_deleted'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_deleted' => 'boolean'
    ];

    // Relación con EquipoCabecera
    public function equipoCabecera()
    {
        return $this->belongsTo(EquipoCabecera::class, 'id_equipos_cab');
    }

    // Relación con Personal
    public function personal()
    {
        return $this->belongsTo(Personal::class, 'id_personal_api');
    }
}