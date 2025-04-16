<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuarios';

    protected $fillable = [
        'id_personal_api',
        'clave',
        'id_tiendas_api',
        'id_areas',
        'id_roles',
        'create_date',
        'status',
        'is_deleted',
        'last_update'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_deleted' => 'boolean',
        'create_date' => 'date',
        'last_update' => 'date'
    ];

    protected $hidden = [
        'clave' // Ocultamos la contraseña en las respuestas JSON
    ];

    /**
     * Relación con Personal
     */
    public function personal()
    {
        return $this->belongsTo(Personal::class, 'id_personal_api', 'id_personal_api');
    }

    /**
     * Relación con Tienda
     */
    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tiendas_api', 'id_tiendas');
    }

    /**
     * Relación con Area
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_areas', 'id_areas');
    }

    /**
     * Relación con Rol
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_roles', 'id_roles');
    }

    /**
     * Scope para usuarios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('status', true)->where('is_deleted', false);
    }

    /**
     * Mutador para la contraseña (podrías añadir encriptación aquí)
     */
    public function setClaveAttribute($value)
    {
        $this->attributes['clave'] = bcrypt($value); // Encriptación automática
    }

    /**
     * Verificar si el usuario está eliminado
     */
    public function estaEliminado()
    {
        return $this->is_deleted || $this->trashed();
    }
}