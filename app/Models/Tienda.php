<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Tienda extends Model
{
    use HasFactory;
    
    protected $table = 'tiendas';
    protected $primaryKey = 'id_tiendas';
    public $timestamps = false;
    
    protected $fillable = [
        'nombre',
        'created_at_datetime',
        'updated_at_datetime',
        'deleted_at',
        'status',
        'is_deleted'
    ];
    
    protected $casts = [
        'status' => 'boolean',
        'is_deleted' => 'boolean',
        'created_at_datetime' => 'datetime',
        'updated_at_datetime' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $now = now()->timezone(config('app.timezone'));
            $model->created_at_datetime = $now;
            $model->updated_at_datetime = $now;
            $model->status = true;
            $model->is_deleted = false;
            $model->deleted_at = null;
        });

        static::updating(function ($model) {
            $model->updated_at_datetime = now()->timezone(config('app.timezone'));
        });

        static::deleting(function ($model) {
            $now = now()->timezone(config('app.timezone'));
            $model->update([
                'is_deleted' => true,
                'status' => false,
                'deleted_at' => $now,
                'updated_at_datetime' => $now
            ]);
            return false;
        });
    }
}