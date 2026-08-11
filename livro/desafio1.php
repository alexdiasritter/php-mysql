<?php

// Desafio 1 com upgrades.

session_start();
date_default_timezone_set('America/Sao_Paulo');

function saudacao(): string
{
    if (date('H:i') >= '05:00' && date('H:i') < '12:00') {
        return 'Bom dia! ' . $_POST['nome'];

    } elseif (date('H:i') >= '12:00' && date('H:i') < '18:00') {
        return 'Boa tarde!';

    } else {
        return 'Boa noite, ' .  $_POST['nome'] . '. Agora são ' . date('H:i');
    }
}

// Verifica se o formulário foi enviado
if (isset($_POST['executar'])) {

    // Executa a função e guarda o resultado na sessão
    $_SESSION['mensagem'] = saudacao();

    // Redireciona para a própria página dando um get e n post, dessa forma, garantindo q o botão apareça
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Recupera a mensagem da sessão
$mensagem = $_SESSION['mensagem'] ?? null;
unset($_SESSION['mensagem']);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Desafio 1 - Saudações de acordo com o horário</title>
</head>

<body>

    <?php if ($mensagem): ?>

        <p>
            <?= $mensagem ?>
        </p>

    <?php else: ?>

        <form method="POST">

           <label for="valor1">Digite seu nome:</label>
            <input type="text"
                   id="nome"
                   name="nome"
                   placeholder="Seu nome"
                   required>

            <br> <br>
            <button type="submit" name="executar">
                Receber Saudação
            </button>

        </form>

    <?php endif; ?>

</body>

</html>
