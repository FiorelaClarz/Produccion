<?php

namespace App\View\Components;

use Illuminate\View\Component;

class GraficoProduccion extends Component
{
    public $titulo;
    public $tipo;
    public $datos;
    
    public function __construct($titulo = '', $tipo = 'bar', $datos = [])
    {
        $this->titulo = $titulo;
        $this->tipo = $tipo;
        $this->datos = $datos;
    }
    
    public function render()
    {
        return view('components.grafico-produccion');
    }
}