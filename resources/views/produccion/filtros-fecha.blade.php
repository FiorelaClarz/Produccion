<div class="row mb-4">
    <div class="col-md-3">
        <label for="fecha_inicio">Fecha Inicio</label>
        <input type="date" name="fecha_inicio" id="fecha_inicio" 
               class="form-control" value="{{ $fechaInicio }}">
    </div>
    <div class="col-md-3">
        <label for="fecha_fin">Fecha Fin</label>
        <input type="date" name="fecha_fin" id="fecha_fin" 
               class="form-control" value="{{ $fechaFin }}">
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <button type="button" class="btn btn-primary" onclick="aplicarFiltros()">
            <i class="fas fa-filter"></i> Aplicar
        </button>
    </div>
</div>

<script>
function aplicarFiltros() {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const url = '{{ $ruta }}?fecha_inicio=' + fechaInicio + '&fecha_fin=' + fechaFin;
    window.location.href = url;
}
</script>