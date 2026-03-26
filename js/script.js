const taskInput = document.getElementById('taskInput');
const descInput = document.getElementById('descInput');
const dueDateInput = document.getElementById('dueDateInput');
const dueTimeInput = document.getElementById('dueTimeInput');
const addBtn = document.getElementById('addBtn');
const clearBtn = document.getElementById('clearBtn');
const infoMsg = document.getElementById('infoMsg');
const searchInput = document.getElementById('searchInput');
const filterStatus = document.getElementById('filterStatus');

const todoList = document.getElementById('todoList');
const inProgressList = document.getElementById('inProgressList');
const doneList = document.getElementById('doneList');

const urlParams = new URLSearchParams(window.location.search);
const taskId = urlParams.get('id');
const form = document.getElementById('editTaskForm');

let tasks = [];
let filterText = '';
let statusFilter = '';
let draggedTaskId = null;
const dragDepth = new WeakMap();

const API_BASE = 'api/tasks_api.php';

// ================= API =================
async function apiCall(action, payload = {}) {
    const res = await fetch(`${API_BASE}?action=${action}`, {
        method: action === 'list' ? 'GET' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: action === 'list' ? undefined : JSON.stringify(payload)
    });

    let data;
    try {
        data = await res.json();
    } catch {
        throw new Error('Erro ao interpretar resposta do servidor.');
    }

    if (!res.ok) {
        throw new Error(data.error || `Erro de API (HTTP ${res.status})`);
    }

    return data;
}

// ================= UI =================
function showInfo(text, type = '') {
    const msgElement = infoMsg || document.getElementById('editInfoMsg');
    if (!msgElement) return;

    msgElement.textContent = text;
    msgElement.className = 'msg ' + type + ' show';

    setTimeout(() => {
        msgElement.className = 'msg';
    }, 2000);
}

function formatData(date, time) {
    if (!date) return 'Sem prazo';

    const safeTime = time && String(time).trim() !== '' ? String(time).slice(0, 5) : '00:00';
    const iso = `${date}T${safeTime}`;
    const parsed = new Date(iso);

    if (Number.isNaN(parsed.getTime())) {
        return time ? `${date} ${String(time).slice(0, 5)}` : date;
    }

    return parsed.toLocaleString('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short'
    });
}

function statusLabel(status) {
    if (status === 'todo') return 'A fazer';
    if (status === 'inProgress') return 'Em progresso';
    if (status === 'done') return 'Concluída';
    return 'Sem status';
}

// ================= LOAD =================
async function loadTasks() {
    try {
        todoList.innerHTML = '<small>🔄 Carregando tarefas...</small>';
        inProgressList.innerHTML = '';
        doneList.innerHTML = '';

        const data = await apiCall('list');
        tasks = data.tasks || [];
        renderTasks();
    } catch (error) {
        showInfo(error.message, 'err');
    }
}

// ================= RENDER =================
function renderTasks() {
    todoList.innerHTML = '';
    inProgressList.innerHTML = '';
    doneList.innerHTML = '';

    const lowerFilter = filterText.toLowerCase();

    const filtered = tasks.filter(t => {
        const matchText =
            !filterText ||
            (t.title || '').toLowerCase().includes(lowerFilter) ||
            (t.description || '').toLowerCase().includes(lowerFilter);

        const matchStatus =
            !statusFilter || t.status === statusFilter;

        return matchText && matchStatus;
    });

    if (!filtered.length) {
        todoList.innerHTML = '<small>Nenhuma tarefa encontrada. Crie sua primeira tarefa no formulário acima.</small>';
        return;
    }

    filtered.forEach(task => {
        const item = document.createElement('div');
        item.className = 'task-item' + (task.status === 'done' ? ' done' : '');
        item.setAttribute('role', 'listitem');
        item.dataset.taskId = String(task.id);

        if (task.status !== 'done' && task.due_date) {
            const safeTime = task.due_time && String(task.due_time).trim() !== '' ? String(task.due_time).slice(0, 5) : '23:59';
            const dueDateTime = new Date(`${task.due_date}T${safeTime}`);
            if (!Number.isNaN(dueDateTime.getTime()) && new Date() > dueDateTime) {
                item.classList.add('overdue');
            }
        }

        const handle = document.createElement('div');
        handle.className = 'task-drag-handle';
        handle.setAttribute('aria-hidden', 'true');
        handle.setAttribute('draggable', 'true');
        handle.title = 'Arraste para mover esta tarefa';
        handle.textContent = '⠿';

        handle.addEventListener('dragstart', event => {
            draggedTaskId = task.id;
            event.dataTransfer.setData('text/plain', String(task.id));
            event.dataTransfer.effectAllowed = 'move';
            item.classList.add('dragging');
        });

        handle.addEventListener('dragend', () => {
            draggedTaskId = null;
            item.classList.remove('dragging');
            [todoList, inProgressList, doneList].forEach(list => {
                list.classList.remove('drag-over');
                dragDepth.set(list, 0);
            });
        });

        const titleEl = document.createElement('p');
        titleEl.className = 'task-title';
        titleEl.textContent = task.title || task.description || '';

        const body = document.createElement('div');
        body.className = 'task-body';
        body.append(titleEl);

        if (task.description && task.description !== task.title) {
            const descEl = document.createElement('p');
            descEl.className = 'task-desc';
            descEl.textContent = task.description;
            body.append(descEl);
        }

        const taskRow = document.createElement('div');
        taskRow.className = 'task-row';

        const chip = document.createElement('span');
        chip.className = 'task-status-chip';
        chip.textContent = statusLabel(task.status);

        const meta = document.createElement('span');
        meta.className = 'task-meta';
        meta.textContent = formatData(task.due_date, task.due_time);

        taskRow.append(chip, meta);
        body.append(taskRow);

        const actions = document.createElement('div');
        actions.className = 'task-buttons';

        const left = document.createElement('button');
        left.textContent = '<';
        left.className = 'btn-small';
        left.disabled = task.status === 'todo';
        left.setAttribute('aria-label', 'Mover tarefa para coluna anterior');
        left.title = 'Mover para coluna anterior';
        left.addEventListener('click', () => changeStatus(task, -1));

        const right = document.createElement('button');
        right.textContent = '>';
        right.className = 'btn-small';
        right.disabled = task.status === 'done';
        right.setAttribute('aria-label', 'Mover tarefa para próxima coluna');
        right.title = 'Mover para próxima coluna';
        right.addEventListener('click', () => changeStatus(task, 1));

        const edit = document.createElement('button');
        edit.textContent = '✏️';
        edit.className = 'btn-warning icon-btn';
        edit.setAttribute('aria-label', 'Editar tarefa');
        edit.title = 'Editar tarefa';
        edit.addEventListener('click', () => {
            window.location.href = `editTasks.php?id=${task.id}`;
        });

        const remove = document.createElement('button');
        remove.textContent = '🗑️';
        remove.className = 'btn-danger icon-btn';
        remove.setAttribute('aria-label', 'Excluir tarefa');
        remove.title = 'Excluir tarefa';
        remove.addEventListener('click', () => removeTask(task.id));

        actions.append(left, right, edit, remove);
        item.append(handle, body, actions);

        if (task.status === 'todo') todoList.appendChild(item);
        else if (task.status === 'inProgress') inProgressList.appendChild(item);
        else if (task.status === 'done') doneList.appendChild(item);
    });

    if (!todoList.children.length) {
        todoList.innerHTML = '<small>Nenhuma tarefa</small>';
    }

    if (!inProgressList.children.length) {
        inProgressList.innerHTML = '<small>Nada em progresso</small>';
    }

    if (!doneList.children.length) {
        doneList.innerHTML = '<small>Nada concluído</small>';
    }
}

// ================= DRAG AND DROP =================
function setupDragAndDrop() {
    const statusByListId = {
        todoList: 'todo',
        inProgressList: 'inProgress',
        doneList: 'done'
    };

    [todoList, inProgressList, doneList].forEach(list => {
        if (!list) return;

        dragDepth.set(list, 0);

        list.addEventListener('dragover', event => {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            list.classList.add('drag-over');
        });

        list.addEventListener('dragenter', event => {
            event.preventDefault();
            const depth = (dragDepth.get(list) || 0) + 1;
            dragDepth.set(list, depth);
            list.classList.add('drag-over');
        });

        list.addEventListener('dragleave', () => {
            const depth = Math.max((dragDepth.get(list) || 0) - 1, 0);
            dragDepth.set(list, depth);
            if (depth === 0) {
                list.classList.remove('drag-over');
            }
        });

        list.addEventListener('drop', async event => {
            event.preventDefault();
            list.classList.remove('drag-over');
            dragDepth.set(list, 0);

            const droppedId = parseInt(event.dataTransfer.getData('text/plain') || String(draggedTaskId), 10);
            if (!droppedId) return;

            const task = tasks.find(t => t.id === droppedId);
            if (!task) return;

            const targetStatus = statusByListId[list.id];
            if (!targetStatus || task.status === targetStatus) return;

            await moveTaskToStatus(task, targetStatus);
        });
    });
}

// ================= ACTIONS =================
async function moveTaskToStatus(task, targetStatus) {
    try {
        await apiCall('update', {
            id: task.id,
            title: task.title || task.description || 'Sem título',
            description: task.description || task.title || '',
            status: targetStatus,
            due_date: task.due_date || null,
            due_time: task.due_time || null
        });

        task.status = targetStatus;
        renderTasks();
        showInfo(`Tarefa movida para ${statusLabel(targetStatus)}.`, 'ok');
    } catch (error) {
        showInfo(error.message, 'err');
    }
}

async function addTask() {
    const title = taskInput.value.trim();
    const description = descInput ? descInput.value.trim() : '';
    const dueDate = dueDateInput ? dueDateInput.value : null;
    const dueTime = dueTimeInput ? dueTimeInput.value : null;

    if (!title) {
        showInfo('Digite o título da tarefa.', 'err');
        return;
    }

    addBtn.disabled = true;
    addBtn.textContent = 'Adicionando...';

    try {
        await apiCall('create', {
            title,
            description: description || title,
            due_date: dueDate || null,
            due_time: dueTime || null
        });

        taskInput.value = '';
        if (descInput) descInput.value = '';
        if (dueDateInput) dueDateInput.value = '';
        if (dueTimeInput) dueTimeInput.value = '';

        await loadTasks();
        showInfo('Tarefa criada!', 'ok');
        taskInput.focus();
    } catch (err) {
        showInfo(err.message, 'err');
    } finally {
        addBtn.disabled = false;
        addBtn.textContent = 'Adicionar tarefa';
    }
}

async function changeStatus(task, direction) {
    const order = ['todo', 'inProgress', 'done'];
    const idx = order.indexOf(task.status);
    const next = idx + direction;

    if (next < 0 || next >= order.length) return;

    const newStatus = order[next];
    await moveTaskToStatus(task, newStatus);
}

async function removeTask(id) {
    if (!confirm('Deseja remover esta tarefa?')) return;

    try {
        await apiCall('delete', { id });
        tasks = tasks.filter(t => t.id !== id);
        renderTasks();
        showInfo('Tarefa removida.', 'ok');
    } catch (err) {
        showInfo(err.message, 'err');
    }
}

async function clearTasks() {
    if (!confirm('Deseja apagar todas as tarefas da lista?')) return;

    try {
        for (const task of [...tasks]) {
            await apiCall('delete', { id: task.id });
        }
        tasks = [];
        renderTasks();
        showInfo('Todas as tarefas foram apagadas.', 'ok');
    } catch (err) {
        showInfo(err.message, 'err');
    }
}

async function loadTask() {
    if (!taskId) return;

    try {
        const task = await apiCall('get', { id: parseInt(taskId, 10) });

        const titleField = document.getElementById('taskTitle');
        const descField = document.getElementById('taskDescription');
        const dueField = document.getElementById('taskDueDate');
        const dueTimeField = document.getElementById('taskDueTime');
        const statusField = document.getElementById('taskStatus');

        if (titleField) titleField.value = task.title || '';
        if (descField) descField.value = task.description || '';
        if (dueField) dueField.value = task.due_date || '';
        if (dueTimeField) dueTimeField.value = task.due_time ? String(task.due_time).slice(0, 5) : '';
        if (statusField) statusField.value = task.status || 'todo';
    } catch (err) {
        showInfo(err.message, 'err');
    }
}

// ================= INIT =================
if (form) {
    form.addEventListener('submit', async e => {
        e.preventDefault();

        const formData = new FormData(form);
        const data = {
            id: taskId ? parseInt(taskId, 10) : null,
            title: (formData.get('title') || '').trim(),
            description: (formData.get('description') || '').trim(),
            status: formData.get('status'),
            due_date: formData.get('due_date') || null,
            due_time: formData.get('due_time') || null
        };

        if (!data.title) {
            showInfo('Informe o título da tarefa para salvar.', 'err');
            return;
        }

        try {
            if (taskId) {
                await apiCall('update', data);
                showInfo('Tarefa atualizada.', 'ok');
            } else {
                await apiCall('create', data);
                showInfo('Tarefa criada.', 'ok');
            }

            setTimeout(() => {
                window.location.href = 'tasks.php';
            }, 1200);
        } catch (err) {
            showInfo(err.message, 'err');
        }
    });
}

if (taskInput) {
    addBtn?.addEventListener('click', addTask);

    taskInput.addEventListener('keypress', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addTask();
        }
    });

    clearBtn?.addEventListener('click', clearTasks);

    searchInput?.addEventListener('input', () => {
        filterText = searchInput.value.trim();
        renderTasks();
    });

    filterStatus?.addEventListener('change', () => {
        statusFilter = filterStatus.value;
        renderTasks();
    });

    setupDragAndDrop();
    loadTasks();
}

if (taskId) {
    loadTask();
}