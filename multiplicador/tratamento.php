<?php
session_start(); // INICIAR SESSÃO

// Limpar erros anteriores
unset($_SESSION['erro']);
$erros = []; // Array para coletar erros

// lógica de tratamento do formulário
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST)) {
    http_response_code(403);
    die("acesso negado");
}

// CORREÇÃO: Usar aspas nas chaves do array POST
if (empty($_POST['text_valor_1']) || empty($_POST['text_valor_2'])) {
    $erros[] = "Os dois valores são obrigatórios";
} else {
    $valor1 = $_POST['text_valor_1'];
    $valor2 = $_POST['text_valor_2'];
    
    if(!is_numeric($valor1)) {
        $erros[] = "Valor 1 ('$valor1') não é um número válido";
    }
    
    if(!is_numeric($valor2)) {
        $erros[] = "Valor 2 ('$valor2') não é um número válido";
    }
    
    if(is_numeric($valor1) && is_numeric($valor2)) {
        if($valor1 < 1 || $valor2 < 1) {
            $erros[] = "Os dois valores têm que ser positivos (maiores que 0)";
        }
    }
}

// Se não houver erros, calcular resultado
if(empty($erros)) {
    $resultado = $valor1 * $valor2;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Resultado do Cálculo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .resultado-box {
            background-color: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .resultado-box h2 {
            color: #155724;
            margin-top: 0;
        }
        
        .resultado-numero {
            font-size: 48px;
            color: #28a745;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .erros-box {
            background-color: #f8d7da;
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .erros-box h2 {
            color: #721c24;
            margin-top: 0;
        }
        
        .lista-erros {
            color: #721c24;
            padding-left: 20px;
        }
        
        .lista-erros li {
            margin-bottom: 8px;
        }
        
        .btn-voltar {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .btn-voltar:hover {
            background-color: #0056b3;
        }
        
        .info {
            background-color: #e2e3e5;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <h1>📊 Resultado do Processamento</h1>

    <?php if (!empty($erros)): ?>
        <div class="erros-box">
            <h2>⚠️ Foram encontrados <?php echo count($erros); ?> erro(s):</h2>
            <ul class="lista-erros">
                <?php foreach ($erros as $erro): ?>
                    <li><?php echo htmlspecialchars($erro); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
    <?php else: ?>
        <div class="resultado-box">
            <h2>✅ Cálculo realizado com sucesso!</h2>
            <p><?php echo htmlspecialchars($valor1); ?> × <?php echo htmlspecialchars($valor2); ?> =</p>
            <p class="resultado-numero"><?php echo htmlspecialchars($resultado); ?></p>
        </div>
        
    <?php endif; ?>
    
    <a href="index.php" class="btn-voltar">← Voltar ao formulário</a>
    
    <div class="info">
        <strong>📝 Debug (valores recebidos):</strong><br>
        Valor 1: "<?php echo htmlspecialchars($valor1 ?? 'não informado'); ?>"<br>
        Valor 2: "<?php echo htmlspecialchars($valor2 ?? 'não informado'); ?>"<br>
        Quantidade de erros: <?php echo count($erros); ?>
    </div>

</body>
</html>
