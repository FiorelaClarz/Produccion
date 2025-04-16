<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UMedida extends Model
{
    use HasFactory;
    
    protected $table = 'u_medidas';
    protected $primaryKey = 'id_u_medidas';
    public $timestamps = true;
    
    protected $fillable = [
        'nombre',
        'is_deleted'
    ];
    
    protected $casts = [
        'is_deleted' => 'boolean'
    ];
}