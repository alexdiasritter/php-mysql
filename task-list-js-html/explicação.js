// ============================================================
// 1. CONFIGURAÇÃO DO AXIOS
// ============================================================
// axios é uma biblioteca que faz requisições HTTP (GET, POST, etc.)
// axios.create() cria uma "instância" já configurada — assim não
// precisamos repetir as configurações em cada chamada.
const api = axios.create({
    // baseURL: prefixo que será colado em toda URL.
    // Ex: se chamarmos api.get('/todos'), ele faz GET em http://api.example.com/todos
    baseURL: 'http://api.example.com',

    // timeout: tempo máximo (em milissegundos) que o axios espera
    // pela resposta. Se passar de 5s, ele aborta e dá erro.
    timeout: 5000,

    // headers: cabeçalhos HTTP enviados em toda requisição.
    // Estamos dizendo que o corpo é JSON.
    headers: { 'Content-Type': 'application/json' }
});

// ============================================================
// 2. MOCK SERVER (simula a API em memória)
// ============================================================
// Como não temos servidor real, usamos AxiosMockAdapter para
// interceptar as chamadas e responder "de mentirinha".
// Ele finge que é um servidor, com regras que nós definimos.
const mock = new AxiosMockAdapter(api, {
    // delayResponse: cada requisição demora 400ms para responder.
    // Isso simula a latência real da internet e nos ajuda a ver
    // o estado de "carregando" na tela.
    delayResponse: 400
});

// "Banco de dados" em memória. É um array de objetos.
// Cada objeto é uma tarefa com id, título e se está concluída.
let db = [
    { id: 1, title: 'Estudar async/await', completed: false },
    { id: 2, title: 'Praticar axios', completed: true },
    { id: 3, title: 'Entender event delegation', completed: false }
];

// Guarda o próximo ID a ser usado quando criarmos uma tarefa nova.
// Como não temos auto-incremento de banco, fazemos na mão.
let nextId = 4;

// --------------------------------------------
// ROTA: GET /todos — retorna todas as tarefas
// --------------------------------------------
// "Quando alguém fizer GET em /todos, responda com o array db e status 200"
mock.onGet('/todos').reply(() => {
    // O retorno é sempre [statusHTTP, corpoDaResposta]
    return [200, db];
});

// --------------------------------------------
// ROTA: POST /todos — cria uma nova tarefa
// --------------------------------------------
mock.onPost('/todos').reply((config) => {
    // config.data é o corpo da requisição como STRING JSON.
    // Precisamos converter para objeto com JSON.parse().
    const body = JSON.parse(config.data);

    // Monta a tarefa nova. O id vem do nextId e é incrementado.
    const newTask = {
        id: nextId++,
        title: body.title,
        completed: false
    };

    // Adiciona no "banco"
    db.push(newTask);

    // Retorna status 201 (Created) e a tarefa criada.
    // O cliente vai receber isso em response.data
    return [201, newTask];
});

// --------------------------------------------
// ROTA: PATCH /todos/:id — atualiza parcialmente
// --------------------------------------------
// A regex /\/todos\/\d+/ casa com qualquer URL tipo /todos/1, /todos/42, etc.
// \/ = barra escapada (o / é delimitador da regex, então precisa escapar)
// \d+ = um ou mais dígitos
mock.onPatch(/\/todos\/\d+/).reply((config) => {
    // config.url é tipo "/todos/2". O split('/') vira ['', 'todos', '2'].
    // .pop() pega o último elemento: '2'. Number() converte para número.
    const id = Number(config.url.split('/').pop());

    // O corpo da requisição tem as mudanças (ex: { completed: true })
    const changes = JSON.parse(config.data);

    // Procura a tarefa no "banco" pelo id
    const task = db.find(t => t.id === id);

    // Se não achou, retorna 404 (Not Found)
    if (!task) return [404, { message: 'Tarefa não encontrada' }];

    // Object.assign copia as propriedades de "changes" para "task".
    // Se changes = { completed: true }, task.completed vira true.
    Object.assign(task, changes);

    // Retorna 200 com a tarefa atualizada
    return [200, task];
});

// --------------------------------------------
// ROTA: DELETE /todos/:id — remove uma tarefa
// --------------------------------------------
mock.onDelete(/\/todos\/\d+/).reply((config) => {
    // Mesma lógica: pega o id da URL
    const id = Number(config.url.split('/').pop());

    // findIndex retorna o ÍNDICE da tarefa (não o objeto).
    // Se não achar, retorna -1.
    const index = db.findIndex(t => t.id === id);

    if (index === -1) return [404, { message: 'Tarefa não encontrada' }];

    // splice(index, 1) remove 1 elemento a partir do índice.
    db.splice(index, 1);

    // 204 = "No Content" (sucesso sem corpo de resposta)
    return [204];
});

// ============================================================
// 3. ESTADO DA APLICAÇÃO
// ============================================================
// "Estado" é o que representa a situação atual da tela.
// tasks = todas as tarefas em memória (espelho do servidor)
let tasks = [];

// currentFilter = filtro ativo: 'all', 'active' ou 'completed'
let currentFilter = 'all';

// ============================================================
// 4. SELEÇÃO DE ELEMENTOS DO DOM
// ============================================================
// DOM = a representação do HTML em memória, que o JS pode manipular.
// document.getElementById pega o elemento pelo id (do HTML).
// Guardamos em const para não precisar buscar de novo cada vez.

// O formulário inteiro (onde ficam o input e o botão Adicionar)
const form       = document.getElementById('task-form');

// O campo de texto onde o usuário digita
const input      = document.getElementById('task-input');

// O botão "Adicionar"
const btnAdd     = document.getElementById('btn-add');

// A <ul> onde as tarefas são exibidas
const list       = document.getElementById('task-list');

// O <p> que mostra o contador (ex: "3 tarefas • 1 concluída")
const counter    = document.getElementById('counter');

// querySelectorAll pega TODOS os elementos com a classe .filter-btn.
// Retorna uma NodeList (parecida com array).
const filterBtns = document.querySelectorAll('.filter-btn');

// O botão "Limpar concluídas"
const btnClear   = document.getElementById('btn-clear');

// ============================================================
// 5. CAMADA DE API (só fala HTTP, não toca no DOM)
// ============================================================

// Busca todas as tarefas do servidor.
// async = função assíncrona (pode usar await).
async function fetchTasks() {
    // await espera a Promise resolver.
    // api.get('/todos') retorna um objeto grande; pegamos só .data dele.
    // Isso se chama "desestruturação": const { data } = obj.
    const { data } = await api.get('/todos');

    // Devolve o array de tarefas
    return data;
}

// Cria uma nova tarefa no servidor.
async function createTask(title) {
    // api.post(url, corpo) envia um POST com o corpo em JSON
    const { data } = await api.post('/todos', { title, completed: false });
    return data;
}

// Atualiza parcialmente uma tarefa (PATCH).
// `id` e `changes` são os parâmetros.
// A crase (`) permite interpolar variáveis com ${...}.
async function updateTask(id, changes) {
    const { data } = await api.patch(`/todos/${id}`, changes);
    return data;
}

// Deleta uma tarefa. Não retorna nada porque a resposta é 204 (vazia).
async function deleteTask(id) {
    await api.delete(`/todos/${id}`);
}

// ============================================================
// 6. RENDERIZAÇÃO (só toca no DOM, não fala HTTP)
// ============================================================

// Cria um <li> HTML para uma tarefa específica.
// Recebe a tarefa e devolve o <li> montado (mas ainda fora do DOM).
function createTaskElement(task) {
    // Cria um <li> vazio em memória
    const li = document.createElement('li');
    li.className = 'task-item';

    // dataset.id escreve o atributo data-id="X" no <li>.
    // Guardamos o id ali para recuperar depois quando clicarem.
    li.dataset.id = task.id;

    // Se a tarefa está concluída, adiciona a classe .completed
    // (o CSS aplica o texto riscado)
    if (task.completed) li.classList.add('completed');

    // Cria o checkbox
    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.className = 'task-checkbox';
    checkbox.checked = task.completed;

    // Cria o <span> com o título da tarefa.
    // textContent insere texto puro (seguro contra XSS).
    // NUNCA use innerHTML para conteúdo do usuário.
    const span = document.createElement('span');
    span.className = 'task-text';
    span.textContent = task.title;

    // Cria o botão de excluir
    const btnDelete = document.createElement('button');
    btnDelete.className = 'btn-delete';
    btnDelete.textContent = '🗑';
    btnDelete.setAttribute('aria-label', 'Excluir tarefa');

    // Adiciona os 3 filhos de uma vez no <li>
    li.append(checkbox, span, btnDelete);

    return li;
}

// Retorna apenas as tarefas visíveis segundo o filtro atual
function getVisibleTasks() {
    // "tasks" é plural (o array todo). "task" no callback é singular (cada item).
    if (currentFilter === 'active')    return tasks.filter(t => !t.completed);
    if (currentFilter === 'completed') return tasks.filter(t => t.completed);
    return tasks;
}

// Atualiza o texto do contador no topo
function updateCounter() {
    // length = quantidade de itens do array
    const total     = tasks.length;
    const completed = tasks.filter(t => t.completed).length;

    // Arrow function que retorna singular ou plural conforme n
    const plural = (n, s, p) => n === 1 ? s : p;

    // Template literal (com crase) permite juntar variáveis com texto
    counter.textContent =
        `${total} ${plural(total, 'tarefa', 'tarefas')} • ` +
        `${completed} ${plural(completed, 'concluída', 'concluídas')}`;
}

// Desenha a lista inteira na tela, do zero
function render() {
    // Zera o conteúdo do <ul>
    list.innerHTML = '';

    const visible = getVisibleTasks();

    // Se não tem nada para mostrar, exibe mensagem
    if (visible.length === 0) {
        const empty = document.createElement('li');
        empty.className = 'empty-state';
        // Operador ternário: condicao ? valorSeVerdadeiro : valorSeFalso
        empty.textContent = currentFilter === 'all'
            ? 'Nenhuma tarefa por aqui. Adicione a primeira!'
            : 'Nada para mostrar neste filtro.';
        list.appendChild(empty);
    } else {
        // createDocumentFragment cria um "container fantasma" fora do DOM.
        // Vantagem: adicionar vários filhos ao fragment é rápido, e só
        // depois colocamos tudo de uma vez na tela (evita re-renderizar
        // a lista a cada item).
        const fragment = document.createDocumentFragment();

        // Para cada tarefa visível, cria o <li> e coloca no fragment
        visible.forEach(t => fragment.appendChild(createTaskElement(t)));

        // Coloca o fragment no <ul> de uma vez
        list.appendChild(fragment);
    }

    // Atualiza o contador
    updateCounter();
}

// ============================================================
// 7. HANDLERS (o que fazer quando algo acontece)
// ============================================================

// --------------------------------------------
// 7.1 Adicionar tarefa
// --------------------------------------------
// addEventListener registra uma função que será chamada quando
// o evento acontecer. 'submit' = quando o form for enviado.
form.addEventListener('submit', async (e) => {
    // Por padrão, enviar um form RECARREGA a página.
    // preventDefault() cancela esse comportamento.
    e.preventDefault();

    // input.value é o texto digitado.
    // .trim() remove espaços no começo e fim.
    const title = input.value.trim();

    // Se ficou vazio, sai da função sem fazer nada
    if (!title) return;

    // Desabilita o botão enquanto a requisição está rodando
    // (evita cliques duplicados). OBS: é disabled, com D no final.
    btnAdd.disabled = true;

    // try = bloco onde podem acontecer erros
    try {
        // Chama a API e espera a resposta
        const task = await createTask(title);

        // Adiciona a tarefa retornada no array em memória
        tasks.push(task);

        // Limpa o input
        input.value = '';

        // Redesenha a tela
        render();
    } catch (err) {
        // Se algo deu errado, cai aqui
        console.error('Erro ao criar tarefa:', err);
        alert('Não foi possível criar a tarefa.');
    } finally {
        // finally executa SEMPRE, com ou sem erro
        // Reabilita o botão e devolve o foco ao input
        btnAdd.disabled = false;
        input.focus();
    }
});

// --------------------------------------------
// 7.2 Marcar/desmarcar como concluída
// --------------------------------------------
// Aqui usamos "event delegation": escutamos no <ul> pai, e não em
// cada checkbox individual. Isso funciona porque eventos "borbulham"
// do filho para o pai. Vantagem: funciona para checkboxes criados
// depois, sem precisar registrar listener neles individualmente.
list.addEventListener('change', async (e) => {
    // e.target = o elemento que disparou o evento.
    // Só continua se foi um checkbox (não um clique em outro lugar).
    if (!e.target.classList.contains('task-checkbox')) return;

    // closest sobe na árvore do DOM até achar um ancestral com
    // a classe .task-item. Retorna o <li> que contém o checkbox.
    const li = e.target.closest('.task-item');

    // Pega o id do data-id do <li> e converte para número
    const id = Number(li.dataset.id);

    // Procura a tarefa no array pelo id
    const task = tasks.find(t => t.id === id);
    if (!task) return;

    // checked = estado atual do checkbox (true/false)
    const completed = e.target.checked;

    // Adiciona classe .loading (deixa opaco e bloqueia cliques)
    li.classList.add('loading');

    try {
        // Atualiza no servidor
        await updateTask(id, { completed });

        // Atualiza no array em memória
        task.completed = completed;

        // Redesenha
        render();
    } catch (err) {
        // Se o servidor falhou, desfaz o clique visual
        // (rollback: se estava marcado, desmarca; se desmarcado, marca)
        e.target.checked = !completed;
        console.error('Erro ao atualizar:', err);
    } finally {
        // Sempre remove a classe .loading
        li.classList.remove('loading');
    }
});

// --------------------------------------------
// 7.3 Excluir tarefa
// --------------------------------------------
// Mesmo princípio: delegation no <ul>. Aqui escutamos 'click'
// porque o alvo é um <button>, não um checkbox.
list.addEventListener('click', async (e) => {
    // Só continua se o clique foi no botão de excluir
    if (!e.target.classList.contains('btn-delete')) return;

    // Acha o <li> que contém o botão clicado
    const li = e.target.closest('.task-item');
    const id = Number(li.dataset.id);

    // Mostra o estado de loading
    li.classList.add('loading');

    try {
        // Deleta no servidor
        await deleteTask(id);

        // Remove do array em memória (filter cria novo array sem ela)
        tasks = tasks.filter(t => t.id !== id);

        // Redesenha (a tarefa sumiu)
        render();
    } catch (err) {
        // Se deu erro, remove o loading para o usuário tentar de novo
        li.classList.remove('loading');
        console.error('Erro ao excluir:', err);
    }
});

// --------------------------------------------
// 7.4 Filtros (Todas / Ativas / Concluídas)
// --------------------------------------------
// Aqui NÃO usamos delegation porque os 3 botões são fixos no HTML,
// e cada um tem seu próprio comportamento (sabe seu próprio data-filter).
filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        // Remove a classe .active de TODOS os botões
        filterBtns.forEach(b => b.classList.remove('active'));

        // Adiciona .active só no botão clicado
        btn.classList.add('active');

        // btn.dataset.filter lê o data-filter do botão:
        // 'all', 'active' ou 'completed'
        currentFilter = btn.dataset.filter;

        // Redesenha com o novo filtro
        render();
    });
});

// --------------------------------------------
// 7.5 Limpar concluídas
// --------------------------------------------
btnClear.addEventListener('click', async () => {
    // Filtra as concluídas
    const completed = tasks.filter(t => t.completed);

    // Se não tem nenhuma, não faz nada
    if (completed.length === 0) return;

    try {
        // Promise.all dispara VÁRIAS requisições em paralelo
        // e espera todas terminarem.
        // .map(t => deleteTask(t.id)) transforma cada tarefa em uma Promise.
        await Promise.all(completed.map(t => deleteTask(t.id)));

        // Remove todas as concluídas do array em memória
        tasks = tasks.filter(t => !t.completed);

        // Redesenha
        render();
    } catch (err) {
        // Se alguma requisição falhou, o estado local pode estar ambíguo
        // (não sabemos quais foram deletadas). Recarregamos TUDO do servidor
        // para reconciliar.
        console.error('Erro ao limpar concluídas:', err);
        tasks = await fetchTasks();
        render();
    }
});

// ============================================================
// 8. INICIALIZAÇÃO (o "ponto de entrada" da aplicação)
// ============================================================
async function init() {
    try {
        // Carrega as tarefas do servidor
        tasks = await fetchTasks();

        // Desenha na tela
        render();
    } catch (err) {
        // Se falhou ao carregar, mostra erro na tela
        console.error('Falha ao carregar tarefas:', err);
        counter.textContent = 'Erro ao carregar';
        list.innerHTML = '<li class="error-state">Não foi possível carregar as tarefas.</li>';
    }
}

// Chama init() para dar o pontapé inicial na aplicação.
// Sem essa linha, nada aconteceria — o código ficaria só "definido".
init();