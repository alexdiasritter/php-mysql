<?php
declare(strict_types=1);

/**
 * Conexão única com o MySQL via PDO.
 * -------------------------------------------------------------
 * Padrão: singleton procedural.
 * A conexão é criada na primeira chamada e reutilizada nas seguintes.
 */

function db(): PDO
{
    // "static" dentro de uma função persiste o valor entre chamadas.
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $_ENV['DB_HOST'],
        $_ENV['DB_NAME']
    );

    // Opções do PDO — cada uma tem um motivo específico:
    $options = [
        // 1) Lança exceções em erros (em vez de retornar false silenciosamente).
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        // 2) Retorna resultados como arrays associativos por padrão.
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        // 3) Desativa emulação de prepared statements.
        //    Com OFF, a query é preparada de verdade no servidor — mais
        //    seguro contra SQL Injection e mais rápido em muitos casos.
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Falha na conexão com o banco: ' . $e->getMessage());
        http_response_code(500);
        exit('Erro interno. Tente novamente em alguns instantes.');
    }
}