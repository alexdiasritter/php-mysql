<?php

$servidor = 'localhost';
$banco = 'u149104682_dogsys';
$usuario = 'u149104682_dogsys';
$senha = 'Dogsys@321';

$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);

if (mysqli_connect_errno()) {
    echo 'Erro ao conectar: '. mysqli_connect_error();
    die();
}

function buscar_tarefas ($conexao)
{
    $sqlBusca = 'select * from tarefas';
    $resultado = mysqli_query($conexao, $sqlBusca);

    $tarefas = array();

    while ($tarefa = mysqli_fetch_assoc($resultado)) {
        $tarefas[] = $tarefa;
    }

    //print_r($tarefas);

    return $tarefas;
}

function gravar_tarefa($conexao, $tarefa)
{
    $sql = "
        INSERT INTO tarefas
        (nome, descricao, prioridade)
        VALUES (?, ?, ?)
    ";

    $stmt = mysqli_prepare($conexao, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssi",
        $tarefa['nome'],
        $tarefa['descricao'],
        $tarefa['prioridade']
    );

    mysqli_stmt_execute($stmt);
}

function buscar_tarefa($conexao, $id) 
{
    $sqlBusca = 'SELECT * FROM tarefas WHERE id = ' . $id;
    $resultado = mysqli_query($conexao, $sqlBusca);
    return mysqli_fetch_assoc($resultado);
}

function editar_tarefa($conexao, $tarefa) {
    $sql = "
        UPDATE tarefas SET
        nome = ?,
        descricao = ?,
        prioridade = ?,
        prazo = ?,
        concluida = ?
        WHERE id = ?
    ";

    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        'ssissi',  // s=string, i=integer (prazo é string pois é DATE)
        $tarefa['nome'],
        $tarefa['descricao'],
        $tarefa['prioridade'],
        $tarefa['prazo'],
        $tarefa['concluida'],
        $tarefa['id']
    );
    mysqli_stmt_execute($stmt);
}

function remover_tarefa($conexao, $id)
{
    $sqlRemover = "DELETE FROM tarefas WHERE id = {$id}";
    mysqli_query($conexao, $sqlRemover);
}