<?php
/**
 * Configuração central da aplicação.
 *
 * Para não versionar/expor senha em produção, crie um arquivo
 * backend/config.local.php devolvendo só o que quer sobrescrever.
 * Veja backend/config.local.php.example.
 */

$config = [
    // Conexão com o MySQL
    'db' => [
        'host'    => 'localhost',
        'name'    => 'u149104682_dogsys',
        'user'    => 'u149104682_dogsys',
        'pass'    => 'Dogsys@321',
        'charset' => 'utf8mb4',
    ],

    'timezone' => 'America/Sao_Paulo',

    // true = mostra erros do PHP na tela e mensagens de login detalhadas.
    // Coloque false quando estiver em produção.
    'debug' => true,

    // Cookies só trafegam em HTTPS
    'secure_cookies' => true,

    // 30 dias
    'session_lifetime' => 60 * 60 * 24 * 30,

    // "Lembrar login"
    'remember' => [
        'cookie'   => 'dogsys_remember',
        'lifetime' => 60 * 60 * 24 * 30,
    ],

    // Clínicas reconhecidas nos campos origem/destino (dashboard de ganhos)
    'clinicas' => [
        'Boa Ventura', 'Popular Mumbuca', 'Animal Way', 'Vet Lagoa',
        'Personal Pet', 'Txai', 'Vet Maricá', 'Apaixonados', 'HVM',
        'Popular Boqueirão', 'Vet Boqueirão', 'Bustamante', 'Vet São José',
    ],
];

$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    $config = array_replace_recursive($config, require $localConfig);
}

return $config;
