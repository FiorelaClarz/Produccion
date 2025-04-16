<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'personal_api';

    // Clave primaria personalizada
    protected $primaryKey = 'id_personal_api';

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Tipo de la clave primaria
    protected $keyType = 'int';

    // Campos asignables masivamente
    protected $fillable = [
        'codigo_personal',
        'dni_personal',
        'nombre',
        'id_areas',
        'sueldo',
        'id_tiendas_api'
    ];

    // Campos ocultos en las respuestas JSON
    protected $hidden = [];

    // Tipos de datos para los campos
    protected $casts = [
        'sueldo' => 'decimal:2',
    ];

    // Relación con el modelo Area (muchos a uno)
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_areas', 'id_areas');
    }

    // Relación con el modelo Tienda (muchos a uno)
    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tiendas_api', 'id_tiendas');
    }

    // Relación con el modelo Usuario (uno a uno)
    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_personal_api', 'id_personal_api');
    }

    // Relación con equipos (a través de equipos_det)
    public function equipos()
    {
        return $this->belongsToMany(
            EquipoCabecera::class,
            'equipos_det',
            'id_personal_api',
            'id_equipos_cab'
        )->withPivot('status', 'is_deleted');
    }

    // Scope para personal no eliminado
    public function scopeNoEliminado($query)
    {
        return $query->whereDoesntHave('usuario', function($q) {
            $q->where('is_deleted', true);
        });
    }

    // Mutador para el nombre (asegura mayúscula inicial)
    public function setNombreAttribute($value)
    {
        $this->attributes['nombre'] = ucwords(strtolower($value));
    }

    // Accesor para el código personal (formato uniforme)
    public function getCodigoPersonalFormateadoAttribute()
    {
        return strtoupper(trim($this->codigo_personal));
    }
}

