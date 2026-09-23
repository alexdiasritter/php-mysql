<?php

session_start();

include("banco.php");
include("helpers.php");

$exibir_tabela = true;

if (isset($_POST['nome']) && $_POST['nome'] != '') {

    $tarefa = array();

    $tarefa['nome'] = $_POST['nome'];
    $tarefa['descricao'] = $_POST['descricao'] ?? '';
    $tarefa['prazo'] = $_POST['prazo'] ?? '';
    $tarefa['prioridade'] = $_POST['prioridade'];
    $tarefa['concluida'] = $_POST['concluida'] ?? '';

    gravar_tarefa($conexao, $tarefa);
    header('Location: tarefas.php');
    exit;
}

$lista_tarefas = buscar_tarefas($conexao);
$tarefa = array(
    'id' => 0,
    'nome' => '',
    'descricao' => '',
    'prazo' => '',
    'prioridade' => 1,
    'concluida' => ''
);

include "template.php";