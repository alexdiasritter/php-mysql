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