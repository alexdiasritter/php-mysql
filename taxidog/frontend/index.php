<?php
/** Tela de login */
require_once __DIR__ . '/../backend/bootstrap.php';

if (Auth::check()) {
    redirect(page_url('home.php'));
}

// O formulário envia JSON via fetch
if (is_post()) {
    $input = json_input();
    json_response(Auth::login(
        (string) ($input['username'] ?? ''),
        (string) ($input['password'] ?? '')
    ));
}

$pageTitle   = 'Entrar · Táxi Dog';
$pageStyles  = ['styles/pages/login.css'];
$pageScripts = ['js/login.js'];

require __DIR__ . '/header.php';
?>
<div class="login-wrapper">
    <div class="login-box">
        <h2 class="login-title">Táxi Dog</h2>

        <div id="alert-box" class="alert"></div>

        <form id="login-form">
            <div class="form-group">
                <label for="username">Usuário</label>
                <input type="text" id="username" autocomplete="username" required>
            </div>
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
