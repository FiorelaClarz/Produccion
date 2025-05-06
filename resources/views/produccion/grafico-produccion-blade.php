<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ $titulo }}</h6>
    </div>
    <div class="card-body">
        <div class="chart-area">
            <canvas id="{{ 'chart'.Str::slug($titulo) }}"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('{{ 'chart'.Str::slug($titulo) }}').getContext('2d');
    const chart = new Chart(ctx, {
        type: '{{ $tipo }}',
        data: {
            labels: {!! json_encode($datos->pluck('fecha')->unique()) !!},
            datasets: [
                @foreach($datos->groupBy('area') as $area => $items)
                {
                    label: '{{ $area }} - Producido',
                    data: {!! json_encode($items->pluck('total_producido')) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: '{{ $area }} - Esperado',
                    data: {!! json_encode($items->pluck('total_esperado')) !!},
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                @endforeach
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush