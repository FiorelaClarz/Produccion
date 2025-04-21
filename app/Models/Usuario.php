<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

class Usuario extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre_personal',
        'id_personal_api',
        'clave',
        'id_tiendas_api',
        'id_areas',
        'id_roles',
        'status',
        'is_deleted',
        'create_date',
        'last_update',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at',
        'create_date',
        'last_update',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'clave',
    ];

    /**
     * Hash the password automatically when setting it.
     *
     * @param string $value
     * @return void
     */
    public function setClaveAttribute($value)
    {
        $this->attributes['clave'] = Hash::make($value);
    }

    /**
     * Relationship with PersonalApi
     */
    public function personal()
    {
        return $this->belongsTo(PersonalApi::class, 'id_personal_api');
    }

    /**
     * Relationship with Tienda
     */
    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tiendas_api');
    }

    /**
     * Relationship with Area
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_areas');
    }

    /**
     * Relationship with Rol
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_roles');
    }

    /**
     * Scope a query to only include active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        return $query->where('status', true)
                    ->where('is_deleted', false);
    }
}