import './bootstrap';
import './echo';
import Alpine from 'alpinejs';
import { KanbanBoard }       from './components/KanbanBoard';
import { renderBurndownChart } from './components/BurndownChart';

window.Alpine = Alpine;
Alpine.start();

// ── Kanban ──────────────────────────────────────────────────
const kanbanEl = document.getElementById('kanban-board');
if (kanbanEl) {
    new KanbanBoard(
        kanbanEl.dataset.projectId,
        kanbanEl.dataset.workspace,
        kanbanEl.dataset.project,
    );
}

// ── Burndown Chart ───────────────────────────────────────────
const burndownEl = document.getElementById('burndownChart');
if (burndownEl) {
    const data = JSON.parse(burndownEl.dataset.burndown || '[]');
    renderBurndownChart('burndownChart', data);
}

// ── Notification Bell ─────────────────────────────────────────
if (window.Echo && window.currentUserId) {
    window.Echo.private(`App.Models.User.${window.currentUserId}`)
        .notification(notification => {
            const badge = document.querySelector('#notification-count');
            if (badge) {
                const count = parseInt(badge.textContent || '0') + 1;
                badge.textContent = count;
                badge.classList.remove('hidden');
            }

            // Toast
            const toast = document.createElement('div');
            toast.className = 'fixed top-6 right-6 z-50 bg-indigo-700 text-white px-4 py-3 rounded-xl shadow-xl text-sm max-w-xs';
            toast.innerHTML = `<strong>🔔 Thông báo mới</strong><p class="mt-1 text-indigo-200">${notification.message}</p>`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 5000);
        });
}
