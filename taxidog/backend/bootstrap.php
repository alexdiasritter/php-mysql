<?php
/**
 * Ponto de entrada do backend.
 *
 * Toda página começa com:
 *     require_once __DIR__ . '/../backend/bootstrap.php';
 *
 * Aqui carregamos config, helpers, banco e autenticação — nesta ordem.
 */

define('APP_ROOT', dirname(__DIR__));
define('BACKEND_PATH', __DIR__);

$appConfig = require __DIR__ . '/config.php';
$GLOBALS['app_config'] = $appConfig;

error_reporting(E_ALL);
ini_set('display_errors', $appConfig['debug'] ? '1' : '0');
ini_set('display_startup_errors', $appConfig['debug'] ? '1' : '0');
date_default_timezone_set($appConfig['timezone']);

require_once __DIR__ . '/helpers.php';        // e(), money(), asset(), page_url()...
require_once __DIR__ . '/database.php';       // conexão PDO
require_once __DIR__ . '/auth.php';           // Session, Users, Auth
require_once __DIR__ . '/rides.php';          // consultas de corridas
require_once __DIR__ . '/earnings.php';       // números do dashboard
require_once __DIR__ . '/notifications.php';  // avisos

Session::start();

// Logout via ?action=logout continua funcionando em qualquer página
Auth::handleLogoutRequest();

// Se a sessão expirou mas o cookie "lembrar-me" é válido, reloga sozinho
Auth::attemptRememberLogin();
