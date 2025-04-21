<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rol extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rols';
    protected $primaryKey = 'id_roles';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'status',
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

    // Cambiar el nombre del scope de active() a activos() para consistencia
    public function scopeActivos($query)
    {
        return $query->where('status', true)
                    ->where('is_deleted', false);
    }

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
}