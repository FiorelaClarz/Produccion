<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    
    protected $table = 'areas';
    protected $primaryKey = 'id_areas';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'create_date',       // Mantener el campo date original
        'last_update',       // Mantener el campo date original
        'created_at_datetime', // Nuevo campo datetime
        'updated_at_datetime', // Nuevo campo datetime
        'deleted_at',
        'status',
        'is_deleted'
    ];
    
    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($model) {
            $model->create_date = now()->toDateString(); // Mantener formato date
            $model->last_update = now()->toDateString(); // Mantener formato date
            $model->created_at_datetime = now(); // Nuevo campo datetime
            $model->updated_at_datetime = now(); // Nuevo campo datetime
            $model->status = true;
            $model->is_deleted = false;
            $model->deleted_at = null;
        });
    
        static::updating(function ($model) {
            $model->last_update = now()->toDateString(); // Mantener formato date
            $model->updated_at_datetime = now(); // Nuevo campo datetime
        });
    
        static::deleting(function ($model) {
            $model->update([
                'is_deleted' => true,
                'deleted_at' => now(),
                'last_update' => now()->toDateString(),
                'updated_at_datetime' => now()
            ]);
            return false;
        });
    }
    
}