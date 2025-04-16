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
        'create_date',
        'last_update',
        'status',
        'is_deleted'
    ];
    
    protected $casts = [
        'status' => 'boolean',
        'is_deleted' => 'boolean'
    ];
}