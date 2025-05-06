<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FiltrosFecha extends Component
{
    public $fechaInicio;
    public $fechaFin;
    public $ruta;
    
    public function __construct($fechaInicio = null, $fechaFin = null, $ruta = '')
    {
        $this->fechaInicio = $fechaInicio ?? now()->subWeek()->format('Y-m-d');
        $this->fechaFin = $fechaFin ?? now()->format('Y-m-d');
        $this->ruta = $ruta;
    }
    
    public function render()
    {
        return view('components.filtros-fecha');
    }
}