// ============================================================
// 1. CONFIGURAÇÃO
// ============================================================
const API_URL = 'https://jsonplaceholder.typicode.com';

// ============================================================
// 2. ESTADO E ELEMENTOS
// ============================================================
let tasks = [];

const form    = document.getElementById('task-form');
const input   = document.getElementById('task-input');
const list    = document.getElementById('task-list');
const btnAdd  = document.getElementById('btn-add');
const counter = document.getElementById('counter');


// ============================================================
// 3. API — GET (buscar tarefas)
// ============================================================
async function fetchTasks() {
    // fetch faz a requisição; await espera a resposta HTTP
    const response = await fetch(`${API_URL}/todos?_limit=5`);

    // response.json() lê o corpo e converte de JSON para objeto JS.
    // Também é assíncrono, por isso o segundo await.
    const data = await response.json();

    return data;
}

// ============================================================
// 4. API — POST (criar tarefa)
// ============================================================
async function createTask(title) {
    // fetch com segundo argumento = configurações da requisição
    const response = await fetch(`${API_URL}/todos`, {
        // method diz o verbo HTTP. Sem isso, assume GET.
        method: 'POST',

        // headers avisam o servidor que estamos enviando JSON
        headers: { 'Content-Type': 'application/json' },

        // body é o corpo. Precisa ser string, por isso JSON.stringify.
        body: JSON.stringify({ title, completed: false })
    });

    // Lê a resposta do servidor (a tarefa criada, com id gerado)
    const data = await response.json();
    return data;
}

// ============================================================
// Desafio - Contador
// ============================================================
function updateCounter() {
    let quantidade = tasks.length;
    if (quantidade === 1) {
        counter.textContent = `${quantidade} Tarefa.`;
    } else {
        counter.textContent = `${quantidade} Tarefas`;
    }
}

// ============================================================
// 5. RENDERIZAÇÃO
// ============================================================
// Transforma um objeto de tarefa em um <li> HTML
function createTaskElement(task) {
    
    const li = document.createElement('li');
    li.textContent = task.title;
    return li;
}

// Desenha a lista inteira a partir do array tasks
function render() {
    list.innerHTML = '';
    updateCounter();
    tasks.forEach(task => {
        list.appendChild(createTaskElement(task));
    });
}

// ============================================================
// 6. HANDLER — adicionar tarefa
// ============================================================
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
        alert('Não foi possível adicionar.');
    } finally {
        btnAdd.disabled = false;
        input.focus();
    }
});

// ============================================================
// 7. INICIALIZAÇÃO
// ============================================================
async function init() {
    try {
        tasks = await fetchTasks();
        render();
    } catch (err) {
        console.error('Falha ao carregar:', err);
        list.innerHTML = '<li>Erro ao carregar tarefas.</li>';
    }
}

init();