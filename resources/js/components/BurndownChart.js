import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export function renderBurndownChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.date),
            datasets: [
                {
                    label:           'Remaining Points',
                    data:            data.map(d => d.remaining),
                    borderColor:     '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.15)',
                    fill:            true,
                    tension:         0.3,
                    pointBackgroundColor: '#6366f1',
                    pointRadius:     4,
                },
                {
                    label:       'Ideal Burndown',
                    data:        data.map(d => d.ideal),
                    borderColor: '#6b7280',
                    borderDash:  [6, 4],
                    fill:        false,
                    tension:     0,
                    pointRadius: 0,
                },
            ],
        },
        options: {
            responsive:          true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend:  { labels: { color: '#d1d5db', font: { size: 12 } } },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor:      '#f9fafb',
                    bodyColor:       '#d1d5db',
                    borderColor:     '#374151',
                    borderWidth:     1,
                },
            },
            scales: {
                x: {
                    ticks: { color: '#9ca3af' },
                    grid:  { color: '#1f2937' },
                },
                y: {
                    ticks:       { color: '#9ca3af' },
                    grid:        { color: '#1f2937' },
                    beginAtZero: true,
                    title: { display: true, text: 'Story Points', color: '#6b7280' },
                },
            },
        },
    });
}
