<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;
    
    protected $table = 'rols';
    protected $primaryKey = 'id_roles';
    public $timestamps = true;
    
    protected $fillable = [
        'nombre',
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