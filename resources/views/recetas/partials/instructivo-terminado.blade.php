<div class="instructivo-terminado">
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Este pedido ya fue terminado
    </div>
    
    <h4 class="text-center mb-4">Resumen de producción terminada</h4>
    
    @include('recetas.partials.instructivo-base', [
        'showDetails' => false,
        'showSummary' => true,
        'receta' => $receta,
        'estado' => $estado
    ])
    
    <div class="mt-4 p-3 bg-light rounded">
        <h5><i class="fas fa-clipboard-check"></i> Verificación de calidad</h5>
        <p>Por favor verifique que el producto cumple con los estándares de calidad antes de enviarlo.</p>
    </div>
</div>