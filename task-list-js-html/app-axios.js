// ============================================================
// 1. CONFIGURAÇÃO DO AXIOS
// ============================================================
const api = axios.create({
    baseURL: 'http://api.example.com',
    timeout: 5000,
    headers: { 'Content-Type': 'application/json' }
});

// ============================================================
// 2. MOCK SERVER (simula a API em memória)
// ============================================================
const mock = new AxiosMockAdapter(api, {
    delayResponse: 400
});

let db = [
    { id: 1, title: 'Estudar async/await', completed: false },
    { id: 2, title: 'Praticar axios', completed: true },
    { id: 3, title: 'Entender event delegation', completed: false }
];
let nextId = 4;

mock.onGet('/todos').reply(() => {
    return [200, db];
});

mock.onPost('/todos').reply((config) => {
    const body = JSON.parse(config.data);
    const newTask = {
        id: nextId++,
        title: body.title,
        completed: false
    };
    db.push(newTask);
    return [201, newTask];
});

mock.onPatch(/\/todos\/\d+/).reply((config) => {
    const id = Number(config.url.split('/').pop());
    const changes = JSON.parse(config.data);
    const task = db.find(t => t.id === id);

    if (!task) return [404, { message: 'Tarefa não encontrada' }];

    Object.assign(task, changes);
    return [200, task];
});

mock.onDelete(/\/todos\/\d+/).reply((config) => {
    const id = Number(config.url.split('/').pop());
    const index = db.findIndex(t => t.id === id);

    if (index === -1) return [404, { message: 'Tarefa não encontrada' }];

    db.splice(index, 1);
    return [204];
});

// ============================================================
// 3. ESTADO DA APLICAÇÃO
// ============================================================
let tasks = [];
let currentFilter = 'all';

// ============================================================
// 4. SELEÇÃO DE ELEMENTOS
// ============================================================
const form       = document.getElementById('task-form');
const input      = document.getElementById('task-input');
const btnAdd     = document.getElementById('btn-add');
const list       = document.getElementById('task-list');
const counter    = document.getElementById('counter');
const filterBtns = document.querySelectorAll('.filter-btn');
const btnClear   = document.getElementById('btn-clear');

// ============================================================
// 5. CAMADA DE API
// ============================================================
async function fetchTasks() {
    const { data } = await api.get('/todos');
    return data;
}

async function createTask(title) {
    const { data } = await api.post('/todos', { title, completed: false });
    return data;
}

async function updateTask(id, changes) {
    const { data } = await api.patch(`/todos/${id}`, changes);
    return data;
}

async function deleteTask(id) {
    await api.delete(`/todos/${id}`);
}

// ============================================================
// 6. RENDERIZAÇÃO
// ============================================================
function createTaskElement(task) {
    const li = document.createElement('li');
    li.className = 'task-item';
    li.dataset.id = task.id;

    if (task.completed) li.classList.add('completed');

    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.className = 'task-checkbox';
    checkbox.checked = task.completed;

    const span = document.createElement('span');
    span.className = 'task-text';
    span.textContent = task.title;

    const btnDelete = document.createElement('button');
    btnDelete.className = 'btn-delete';
    btnDelete.textContent = '🗑';
    btnDelete.setAttribute('aria-label', 'Excluir tarefa');

    li.append(checkbox, span, btnDelete);
    return li;
}

function getVisibleTasks() {
    if (currentFilter === 'active')    return tasks.filter(t => !t.completed);
    if (currentFilter === 'completed') return tasks.filter(t => t.completed);
    return tasks;
}

function updateCounter() {
    const total     = tasks.length;
    const completed = tasks.filter(t => t.completed).length;
    const plural    = (n, s, p) => n === 1 ? s : p;
    counter.textContent =
        `${total} ${plural(total, 'tarefa', 'tarefas')} • ` +
        `${completed} ${plural(completed, 'concluída', 'concluídas')}`;
}

function render() {
    list.innerHTML = '';

    const visible = getVisibleTasks();

    if (visible.length === 0) {
        const empty = document.createElement('li');
        empty.className = 'empty-state';
        empty.textContent = currentFilter === 'all'
            ? 'Nenhuma tarefa por aqui. Adicione a primeira!'
            : 'Nada para mostrar neste filtro.';
        list.appendChild(empty);
    } else {
        const fragment = document.createDocumentFragment();
        visible.forEach(t => fragment.appendChild(createTaskElement(t)));
        list.appendChild(fragment);
    }

    updateCounter();
}

// ============================================================
// 7. HANDLERS
// ============================================================

// 7.1 Adicionar tarefa
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const title = input.value.trim();
    if (!title) return;

    btnAdd.disabled = true;
    try {
        const task = await createTask(title);
        tasks.push(task);
        input.value = '';
        render();
    } catch (err) {
        console.error('Erro ao criar tarefa:', err);
        alert('Não foi possível criar a tarefa.');
    } finally {
        btnAdd.disabled = false;
        input.focus();
    }
});

// 7.2 Marcar/desmarcar concluída
list.addEventListener('change', async (e) => {
    if (!e.target.classList.contains('task-checkbox')) return;

    const li = e.target.closest('.task-item');
    const id = Number(li.dataset.id);
    const task = tasks.find(t => t.id === id);
    if (!task) return;

    const completed = e.target.checked;

    li.classList.add('loading');
    try {
        await updateTask(id, { completed });
        task.completed = completed;
        render();
    } catch (err) {
        e.target.checked = !completed;
        console.error('Erro ao atualizar:', err);
    } finally {
        li.classList.remove('loading');
    }
});

// 7.3 Excluir tarefa
list.addEventListener('click', async (e) => {
    if (!e.target.classList.contains('btn-delete')) return;

    const li = e.target.closest('.task-item');
    const id = Number(li.dataset.id);

    li.classList.add('loading');
    try {
        await deleteTask(id);
        tasks = tasks.filter(t => t.id !== id);
        render();
    } catch (err) {
        li.classList.remove('loading');
        console.error('Erro ao excluir:', err);
    }
});

// 7.4 Filtros
filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.dataset.filter;
        render();
    });
});

// 7.5 Limpar concluídas
btnClear.addEventListener('click', async () => {
    const completed = tasks.filter(t => t.completed);
    if (completed.length === 0) return;

    try {
        await Promise.all(completed.map(t => deleteTask(t.id)));
        tasks = tasks.filter(t => !t.completed);
        render();
    } catch (err) {
        console.error('Erro ao limpar concluídas:', err);
        tasks = await fetchTasks();
        render();
    }
});

// ============================================================
// 8. INICIALIZAÇÃO
// ============================================================
async function init() {
    try {
        tasks = await fetchTasks();
        render();
    } catch (err) {
        console.error('Falha ao carregar tarefas:', err);
        counter.textContent = 'Erro ao carregar';
        list.innerHTML = '<li class="error-state">Não foi possível carregar as tarefas.</li>';
    }
}

init();