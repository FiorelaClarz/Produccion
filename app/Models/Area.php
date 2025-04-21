<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    
    protected $table = 'areas';
    protected $primaryKey = 'id_areas';
    public $timestamps = false;
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'create_date',
        'last_update',
        'created_at_datetime',
        'updated_at_datetime',
        'deleted_at',
        'status',
        'is_deleted'
    ];
    
    protected $casts = [
        'status' => 'boolean',
        'is_deleted' => 'boolean'
    ];

    // Añadir scope activos
    public function scopeActivos($query)
    {
        return $query->where('status', true)
                    ->where('is_deleted', false);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->create_date = now()->toDateString();
            $model->last_update = now()->toDateString();
            $model->created_at_datetime = now();
            $model->updated_at_datetime = now();
            $model->status = true;
            $model->is_deleted = false;
            $model->deleted_at = null;
        });

        static::updating(function ($model) {
            $model->last_update = now()->toDateString();
            $model->updated_at_datetime = now();
        });

        static::deleting(function ($model) {
            $model->update([
                'is_deleted' => true,
                'status' => false,
                'deleted_at' => now(),
                'last_update' => now()->toDateString(),
                'updated_at_datetime' => now()
            ]);
            return false;
        });
    }
}