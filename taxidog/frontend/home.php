<?php
/** Menu principal */
require_once __DIR__ . '/../backend/bootstrap.php';

Auth::requireLogin();

$username = Auth::username();

$pageTitle = 'Início · Táxi Dog';

require __DIR__ . '/header.php';
?>
<h2>Olá, <?php echo e($username); ?>!</h2>
<p class="text-muted">O que vamos fazer hoje?</p>

<div class="home-grid">
    <a href="schedule.php" class="app-tile">
        <span class="icon">📅</span>
        <h3>Agendar</h3>
        <p>Marcar corridas</p>
    </a>
    <a href="pendentes.php" class="app-tile">
        <span class="icon">✅</span>
        <h3>Combinados</h3>
        <p>Corridas agendadas</p>
    </a>
    <a href="earnings.php" class="app-tile">
        <span class="icon">💰</span>
        <h3>Ganhos</h3>
        <p>Faturamento</p>
    </a>
    <a href="history.php" class="app-tile">
        <span class="icon">🗂️</span>
        <h3>Histórico</h3>
        <p>Corridas passadas</p>
    </a>
</div>
<?php require __DIR__ . '/footer.php'; ?>
