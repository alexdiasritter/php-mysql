<?php
session_start();

include "banco.php";
include "helpers.php";

// Exibir a tabela após a edição (se não redirecionar)
$exibir_tabela = true;

if (isset($_POST['nome']) && $_POST['nome'] != '') {
    $tarefa = array();
    $tarefa['id'] = $_POST['id'];
    $tarefa['nome'] = $_POST['nome'];
    $tarefa['descricao'] = $_POST['descricao'] ?? '';
    $tarefa['prazo'] = conversor($_POST['prazo'] ?? '');
    $tarefa['prioridade'] = $_POST['prioridade'] ?? 1;
    $tarefa['concluida'] = isset($_POST['concluida']) ? 1 : 0;

    editar_tarefa($conexao, $tarefa);

    // Redireciona para a lista principal para evitar reenvio
    header('Location: tarefas.php');
    exit;
}

// Busca os dados da tarefa para preencher o formulário
// O ID vem da URL via GET (ex: editar.php?id=5)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $tarefa = buscar_tarefa($conexao, $_GET['id']);
    if (!$tarefa) {
        // Se não encontrar, redireciona para a lista
        header('Location: tarefas.php');
        exit;
    }
} else {
    // Se não tiver ID, redireciona
    header('Location: tarefas.php');
    exit;
}

// A variável $tarefa já contém os dados para o formulário
include 'template.php';
?>