<div class="card shadow mb-4 production-card">
    <div class="card-header py-3 production-card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-clipboard-list mr-2"></i>Producción de la Última Semana
            </h6>
            <div>
                <form id="weekFilterForm" class="form-inline">
                    <div class="form-group mr-2">
                        <label for="startDate" class="mr-2">Desde:</label>
                        <input type="date" class="form-control form-control-sm" id="startDate" 
                               value="{{ $fechaInicio }}" name="startDate">
                    </div>
                    <div class="form-group mr-2">
                        <label for="endDate" class="mr-2">Hasta:</label>
                        <input type="date" class="form-control form-control-sm" id="endDate" 
                               value="{{ $fechaFin }}" name="endDate">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-hover production-table" id="weekTable">
            <!-- Tabla con datos de la semana -->
        </table>
    </div>
</div>