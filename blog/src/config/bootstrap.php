<?php
declare(strict_types=1);

/**
 * Bootstrap da aplicação.
 * -------------------------------------------------------------
 * Este arquivo é o PRIMEIRO a ser carregado por qualquer endpoint.
 * Ele faz duas coisas:
 *   1. Registra o autoload do Composer.
 *   2. Carrega as variáveis do .env para dentro do PHP.
 */

// 1) Autoload do Composer.
// A partir daqui, todas as funções em src/helpers/functions.php
// já estão disponíveis, e qualquer dependência do Composer também.
require_once __DIR__ . '/../../vendor/autoload.php';

// 2) Carrega o .env.
// Dotenv::createImmutable() lê o arquivo .env e injeta as variáveis
// em $_ENV e $_SERVER. "Immutable" significa: se uma variável já
// existe no ambiente do sistema, ela NÃO é sobrescrita pelo .env.
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// 3) Valida que as variáveis críticas existem.
// Se alguém esquecer de preencher o .env em produção,
// o sistema falha AQUI, com mensagem clara, e não em algum lugar obscuro.
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);