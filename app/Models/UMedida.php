<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UMedida extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'u_medidas';
    protected $primaryKey = 'id_u_medidas';
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

    // Scope para unidades activas
    public function scopeActivos($query)
    {
        return $query->where('status', true)
                    ->where('is_deleted', false);
    }

    // Configuración de timestamps con timezone
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
            $model->is_deleted = true;
            $model->status = false;
            $model->deleted_at = now()->timezone(config('app.timezone'));
            $model->save();
        });
    }
}