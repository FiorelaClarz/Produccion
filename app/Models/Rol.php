<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Añadir este import

class Rol extends Model
{
    use HasFactory, SoftDeletes; // Añadir SoftDeletes

    protected $table = 'rols';
    protected $primaryKey = 'id_roles';
    public $timestamps = true; // Esto es redundante, es el valor por defecto

    protected $fillable = [
        'nombre',
        'status',  // Agregado para que sea asignable en masa
        'is_deleted'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_deleted' => 'boolean'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now()->timezone(config('app.timezone'));
        });

        static::updating(function ($model) {
            $model->updated_at = now()->timezone(config('app.timezone'));
        });

        static::deleting(function ($model) {
            if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($model))) {
                $model->deleted_at = now()->timezone(config('app.timezone'));
                $model->save();
            }
        });
    }

    /**
     * Scope para roles no eliminados
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
}
