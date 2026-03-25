@extends('layouts.app')
@section('title', 'Burndown Chart')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">📉 Burndown Chart</h1>
    <p class="text-gray-400">{{ $project->name }} — {{ $sprint->name }}</p>
</div>

<div class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
    <canvas id="burndownChart" height="100"></canvas>
</div>

<div class="grid grid-cols-3 gap-4 mt-6">
    <div class="bg-gray-800 rounded-2xl p-5 border border-gray-700">
        <p class="text-gray-400 text-sm">Total Points</p>
        <p class="text-2xl font-bold text-white">{{ $sprint->total_points }}</p>
    </div>
    <div class="bg-gray-800 rounded-2xl p-5 border border-gray-700">
        <p class="text-gray-400 text-sm">Completed</p>
        <p class="text-2xl font-bold text-green-400">{{ $sprint->completed_points }}</p>
    </div>
    <div class="bg-gray-800 rounded-2xl p-5 border border-gray-700">
        <p class="text-gray-400 text-sm">Days Remaining</p>
        <p class="text-2xl font-bold text-yellow-400">{{ $sprint->days_remaining }}</p>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const data = @json($data);

new Chart(document.getElementById('burndownChart'), {
    type: 'line',
    data: {
        labels: data.map(d => d.date),
        datasets: [
            {
                label: 'Remaining',
                data: data.map(d => d.remaining),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.1)',
                fill: true,
                tension: 0.3,
            },
            {
                label: 'Ideal',
                data: data.map(d => d.ideal),
                borderColor: '#6b7280',
                borderDash: [5, 5],
                fill: false,
                tension: 0,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { labels: { color: '#d1d5db' } }
        },
        scales: {
            x: { ticks: { color: '#9ca3af' }, grid: { color: '#374151' } },
            y: { ticks: { color: '#9ca3af' }, grid: { color: '#374151' }, beginAtZero: true }
        }
    }
});
</script>
@endpush
