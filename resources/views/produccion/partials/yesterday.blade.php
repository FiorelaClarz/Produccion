<div class="card shadow mb-4 production-card">
    <div class="card-header py-3 production-card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-clipboard-list mr-2"></i>Producción de Ayer - {{ now()->subDay()->format('d/m/Y') }}
            </h6>
            <div class="btn-group btn-group-toggle" data-toggle="buttons" id="statusFilterYesterday">
                <!-- Botones de filtro similares a today.blade.php -->
                 <label class="btn btn-sm btn-outline-secondary active">
                    <input type="radio" name="status" value="all" checked> Todos
                </label>
                <label class="btn btn-sm btn-outline-primary">
                    <input type="radio" name="status" value="pending"> Pendientes
                </label>
                <label class="btn btn-sm btn-outline-info">
                    <input type="radio" name="status" value="processing"> Procesándose
                </label>
                <label class="btn btn-sm btn-outline-success">
                    <input type="radio" name="status" value="completed"> Terminados
                </label>
                <label class="btn btn-sm btn-outline-danger">
                    <input type="radio" name="status" value="cancelled"> Cancelados
                </label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-hover production-table" id="yesterdayTable">
            <!-- Tabla con datos de ayer -->
        </table>
    </div>
</div>