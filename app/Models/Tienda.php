<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
    use HasFactory;
    
    protected $table = 'tiendas';
    protected $primaryKey = 'id_tiendas';
    public $timestamps = true;
    
    protected $fillable = [
        'nombre',
        'is_deleted'
    ];
    
    protected $casts = [
        'is_deleted' => 'boolean'
    ];
}