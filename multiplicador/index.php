<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Multiplicação</title> <!-- Título mais descritivo -->
    <style>
        .wrapper {
            width: 600px;
            margin: 50px auto;
            font-family: Arial, sans-serif; /* Adicionado para melhor visualização */
        }

        label {
            display: block; /* Cada label em sua própria linha */
            margin-top: 15px;
            font-weight: bold;
        }

        input[type=text] {
            width: 100%;
            padding: 10px; /* Aumentado para melhor usabilidade */
            margin: 5px 0px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Evita que padding aumente a largura */
        }

        input[type=submit] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
            font-size: 16px;
        }

        input[type=submit]:hover {
            background-color: #45a049;
        }

        /* Adicionado: placeholder mais suave */
        input::placeholder {
            color: #999;
            font-style: italic;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <h1>Multiplicador de Números</h1>
        <p>Digite dois números para multiplicar:</p>

        <form action="tratamento.php" method="post">
            <label for="valor1">Valor 1:</label>
            <input type="text"
                   id="valor1"
                   name="text_valor_1"
                   placeholder="Ex: 5, 10.5, 100"
                   required>

            <label for="valor2">Valor 2:</label>
            <input type="text"
                   id="valor2"
                   name="text_valor_2"
                   placeholder="Ex: 3, 7.2, 50"
                   required>
                   
            <input type="submit" value="Multiplicar">
        </form>
    </div>

</body>
</html>
