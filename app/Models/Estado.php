<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;
    
    protected $table = 'estados';
    protected $primaryKey = 'id_estados';
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