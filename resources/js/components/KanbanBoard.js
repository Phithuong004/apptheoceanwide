export class KanbanBoard {
    constructor(projectId, workspaceSlug, projectSlug) {
        this.projectId     = projectId;
        this.workspaceSlug = workspaceSlug;
        this.projectSlug   = projectSlug;
        this.draggedTask   = null;
        this.fromStatus    = null;
        this.init();
    }

    init() {
        this.bindDragEvents();
        this.listenRealtime();
        this.bindQuickCreate();
    }

    bindDragEvents() {
        // Task cards — drag start
        document.querySelectorAll('.task-card').forEach(card => {
            card.addEventListener('dragstart', e => {
                this.draggedTask = {
                    id:       card.dataset.taskId,
                    position: parseInt(card.dataset.position),
                };
                this.fromStatus = card.closest('[data-status]').dataset.status;
                card.classList.add('opacity-50');
                e.dataTransfer.effectAllowed = 'move';
            });

            card.addEventListener('dragend', () => {
                card.classList.remove('opacity-50');
            });
        });

        // Columns — drag over / drop
        document.querySelectorAll('.kanban-column').forEach(col => {
            col.addEventListener('dragover', e => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                col.classList.add('ring-2','ring-indigo-400','bg-indigo-500/5');
            });

            col.addEventListener('dragleave', e => {
                if (!col.contains(e.relatedTarget)) {
                    col.classList.remove('ring-2','ring-indigo-400','bg-indigo-500/5');
                }
            });

            col.addEventListener('drop', async e => {
                e.preventDefault();
                col.classList.remove('ring-2','ring-indigo-400','bg-indigo-500/5');
                const toStatus = col.dataset.status;

                if (!this.draggedTask || this.fromStatus === toStatus) return;

                const cards    = [...col.querySelectorAll('.task-card')];
                const position = this.getDropPosition(e, cards);

                await this.moveTask(this.draggedTask.id, toStatus, position);
            });
        });
    }

    getDropPosition(event, cards) {
        for (let i = 0; i < cards.length; i++) {
            const rect   = cards[i].getBoundingClientRect();
            const midY   = rect.top + rect.height / 2;
            if (event.clientY < midY) return i;
        }
        return cards.length;
    }

    async moveTask(taskId, toStatus, position) {
        try {
            const res = await fetch(
                `/${this.workspaceSlug}/projects/${this.projectSlug}/tasks/${taskId}/move`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type':  'application/json',
                        'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':        'application/json',
                    },
                    body: JSON.stringify({ status: toStatus, position }),
                }
            );

            if (!res.ok) throw new Error('Move failed');

            const { task } = await res.json();
            this.updateCardInDOM(task, toStatus);
            this.showToast(`Task moved to ${toStatus.replace('_',' ')}`, 'success');

        } catch (err) {
            this.showToast('Failed to move task', 'error');
            console.error(err);
        }
    }

    updateCardInDOM(task, newStatus) {
        const card = document.querySelector(`[data-task-id="${task.id}"]`);
        if (!card) return;

        // Move card to new column
        const targetCol = document.querySelector(`[data-status="${newStatus}"]`);
        if (targetCol) {
            targetCol.appendChild(card);
        }

        // Update status badge color
        const badge = card.querySelector('.status-badge');
        if (badge) badge.textContent = newStatus.replace('_',' ');

        // Update column count
        this.updateColumnCounts();
    }

    updateColumnCounts() {
        document.querySelectorAll('[data-status]').forEach(col => {
            const count   = col.querySelectorAll('.task-card').length;
            const counter = col.previousElementSibling?.querySelector('.column-count');
            if (counter) counter.textContent = count;
        });
    }

    listenRealtime() {
        window.Echo.private(`project.${this.projectId}`)
            .listen('TaskUpdated', e => this.onTaskUpdated(e.task))
            .listen('TaskCreated', e => this.onTaskCreated(e.task))
            .listen('.task.moved',  e => this.onTaskMoved(e));
    }

    onTaskUpdated(task) {
        const card = document.querySelector(`[data-task-id="${task.id}"]`);
        if (!card) return;

        // Update title
        const titleEl = card.querySelector('.task-title');
        if (titleEl) titleEl.textContent = task.title;

        // Update assignee avatar
        if (task.assignee) {
            const avatar = card.querySelector('.task-assignee');
            if (avatar) {
                avatar.src   = task.assignee.avatar_url;
                avatar.title = task.assignee.name;
            }
        }

        this.pulseCard(card);
    }

    onTaskCreated(task) {
        // Inject new card into correct column
        const col = document.querySelector(`[data-status="${task.status}"]`);
        if (!col) return;

        const html = this.renderTaskCard(task);
        col.insertAdjacentHTML('beforeend', html);
        this.updateColumnCounts();

        // Re-bind drag on new card
        const newCard = col.querySelector(`[data-task-id="${task.id}"]`);
        if (newCard) {
            newCard.addEventListener('dragstart', e => {
                this.draggedTask = { id: task.id, position: 0 };
                this.fromStatus  = task.status;
            });
        }
    }

    onTaskMoved(data) {
        if (data.user_id === window.currentUserId) return; // Skip own moves
        this.updateCardInDOM(data.task, data.status);
    }

    renderTaskCard(task) {
        const priorityColors = {
            critical: 'bg-red-900/50 text-red-400',
            urgent:   'bg-orange-900/50 text-orange-400',
            high:     'bg-yellow-900/50 text-yellow-400',
            medium:   'bg-blue-900/50 text-blue-400',
            low:      'bg-gray-700 text-gray-400',
        };
        const priorityClass = priorityColors[task.priority] || priorityColors.low;

        return `
        <div class="task-card bg-gray-800 rounded-xl p-4 shadow-sm hover:shadow-md
                    border border-gray-700 hover:border-gray-600 transition-all cursor-grab"
             draggable="true" data-task-id="${task.id}" data-position="0">
            <p class="text-sm font-medium text-gray-100 task-title">${task.title}</p>
            <div class="flex items-center justify-between mt-3">
                <span class="text-xs px-1.5 py-0.5 rounded font-medium ${priorityClass}">
                    ${task.priority}
                </span>
                ${task.assignee ? `
                <img src="${task.assignee.avatar_url}"
                     class="task-assignee w-6 h-6 rounded-full object-cover"
                     title="${task.assignee.name}">` : ''}
            </div>
        </div>`;
    }

    bindQuickCreate() {
        document.querySelectorAll('.quick-create-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const status = btn.dataset.status;
                document.getElementById('taskDefaultStatus').value = status;
                document.getElementById('createTaskModal').classList.remove('hidden');
            });
        });
    }

    pulseCard(card) {
        card.classList.add('ring-2','ring-indigo-400');
        setTimeout(() => card.classList.remove('ring-2','ring-indigo-400'), 1500);
    }

    showToast(message, type = 'info') {
        const colors = { success: 'bg-green-700', error: 'bg-red-700', info: 'bg-gray-700' };
        const toast  = document.createElement('div');
        toast.className  = `fixed bottom-6 right-6 z-50 px-4 py-3 rounded-xl text-white text-sm
                            shadow-xl ${colors[type]} transition-all`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
}
