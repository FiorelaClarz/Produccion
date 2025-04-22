<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalApi extends Model
{
    use HasFactory;
    
    protected $table = 'personal_api';
    protected $primaryKey = 'id_personal_api';
    public $timestamps = true;
    
    protected $fillable = [
        'codigo_personal',
        'dni_personal',
        'nombre',
        'id_areas',
        'sueldo',
        'id_tiendas_api'
    ];
    
    protected $casts = [
        'sueldo' => 'decimal:2'
    ];
    
    // Relación con Áreas
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_areas');
    }
    
    // Relación con Tiendas
    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tiendas_api');
    }
    
    // Relación con Usuarios (si existe)
    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_personal_api');
    }
}