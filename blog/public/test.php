<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../src/config/bootstrap.php';
require_once __DIR__ . '/../src/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = db();

    $result = $pdo->query('SELECT 1 AS ping')->fetch();

    echo "OK — conexão funcionando.\n";
    echo "Resposta do MySQL: " . $result['ping'] . "\n";
    echo "Versão do MySQL: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";

} catch (Throwable $e) {
    http_response_code(500);
    echo "FALHOU: " . $e->getMessage() . "\n";
}
