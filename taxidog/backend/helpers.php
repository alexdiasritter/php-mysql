<?php
/**
 * Funções utilitárias usadas por controllers e views.
 */

/** Lê uma chave da config: config('db.host') */
function config(string $key, $default = null)
{
    $value = $GLOBALS['app_config'] ?? [];
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

/** Escapa texto para HTML */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Formata valor no padrão brasileiro: 1.234,50 */
function money($value): string
{
    return number_format((float) $value, 2, ',', '.');
}

/**
 * Caminho de um asset com "cache busting" pelo mtime do arquivo.
 * asset('styles/base.css') => /taxidog/styles/base.css?v=1712345678
 * Absoluto porque as páginas ficam em frontend/ e os assets na raiz.
 */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $version = @filemtime(APP_ROOT . '/' . $path) ?: time();
    return e(app_url($path) . '?v=' . $version);
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Corpo da requisição em JSON => array */
function json_input(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/** Responde JSON e encerra */
function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Caminho absoluto (a partir do domínio) de um arquivo do app.
 * Serve para redirecionar corretamente de qualquer subpasta.
 */
function app_url(string $path = ''): string
{
    static $base = null;

    if ($base === null) {
        $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
        $appRoot = rtrim(str_replace('\\', '/', APP_ROOT), '/');

        $base = ($docRoot !== '' && str_starts_with($appRoot, $docRoot))
            ? rtrim(substr($appRoot, strlen($docRoot)), '/')
            : '';
    }

    return $base . '/' . ltrim($path, '/');
}

/**
 * URL de uma página do app — todas moram em frontend/.
 * page_url('home.php') => /taxidog/frontend/home.php
 */
function page_url(string $page = ''): string
{
    return app_url('frontend/' . ltrim($page, '/'));
}

function redirect(string $url): void
{
    // Caminhos relativos são resolvidos a partir da raiz do app
    if ($url !== '' && $url[0] !== '/' && !preg_match('#^(https?:)?//#', $url)) {
        $url = app_url($url);
    }

    header('Location: ' . $url);
    exit;
}

/** Nomes de dias e meses em português */
function nomes_dias_semana(): array
{
    return ['Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado', 'Domingo'];
}

function meses_abreviados(): array
{
    return ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
}

/** "Março 2026" — o format('F Y') do PHP sai em inglês */
function mes_e_ano(DateTimeInterface $date): string
{
    $meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
              'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    return $meses[(int) $date->format('n') - 1] . ' ' . $date->format('Y');
}

/** "07 mar" */
function formatar_data(DateTimeInterface $date): string
{
    $meses = meses_abreviados();
    return $date->format('d') . ' ' . $meses[(int) $date->format('n') - 1];
}

/** Segunda-feira da semana da data informada */
function inicio_da_semana(DateTimeInterface $date): DateTime
{
    $d = DateTime::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d H:i:s'));
    $d->modify('-' . ((int) $d->format('N') - 1) . ' days');
    return $d;
}
