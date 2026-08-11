<?php
/** Histórico mensal de corridas concluídas, agrupado por semana */
require_once __DIR__ . '/../backend/bootstrap.php';

Auth::requireLogin();

$monthOffset = (int) ($_GET['month_offset'] ?? 0);

$ref = new DateTime();
$ref->modify($monthOffset . ' months');
$firstDay = new DateTime($ref->format('Y-m-01'));
$lastDay  = new DateTime($ref->format('Y-m-t'));

$erroConsulta = null;

try {
    $rides = Rides::completedBetween(Auth::id(), $firstDay->format('Y-m-d'), $lastDay->format('Y-m-d'));
} catch (Exception $e) {
    $rides = [];
    $erroConsulta = $e->getMessage();
}

// Agrupa por semana (segunda-feira)
$weeks = [];
foreach ($rides as $ride) {
    $weekStart = inicio_da_semana(new DateTime($ride['ride_date']));
    $weekKey   = $weekStart->format('Y-m-d');

    if (!isset($weeks[$weekKey])) {
        $weeks[$weekKey] = [
            'start' => clone $weekStart,
            'end'   => (clone $weekStart)->modify('+6 days'),
            'total' => 0,
            'rides' => [],
        ];
    }

    $weeks[$weekKey]['total'] += (float) $ride['price'];
    $weeks[$weekKey]['rides'][] = $ride;
}
ksort($weeks);

$monthTotal = array_sum(array_column($rides, 'price'));

$pageTitle  = 'Histórico do Mês · Táxi Dog';
$pageStyles = ['styles/rides.css', 'styles/pages/history.css'];

require __DIR__ . '/header.php';
?>
<div class="page-head">
    <h2>Histórico do Mês (concluídas)</h2>
    <a href="history.php?offset=0" class="view-toggle">Semana</a>
</div>

<?php if ($erroConsulta): ?>
    <div class="query-error">ERRO na consulta: <?php echo e($erroConsulta); ?></div>
<?php endif; ?>

<div class="week-nav">
    <a href="history-month.php?month_offset=<?php echo $monthOffset - 1; ?>" class="week-btn">&#8249;</a>
    <div class="week-label">
        <?php echo e(mes_e_ano($firstDay)); ?>
        <small><?php echo $monthOffset === 0 ? 'Mês atual' : '&nbsp;'; ?></small>
    </div>
    <a href="history-month.php?month_offset=<?php echo $monthOffset + 1; ?>" class="week-btn">&#8250;</a>
</div>

<?php if (empty($weeks)): ?>
    <div class="day-empty text-center">Nenhuma corrida concluída neste mês.</div>
<?php else: ?>
    <?php foreach ($weeks as $weekData): ?>
        <div class="day-block">
            <div class="day-block-header">
                <span class="day-name">Semana de <?php echo e(formatar_data($weekData['start'])); ?></span>
                <span class="day-date">até <?php echo e(formatar_data($weekData['end'])); ?></span>
                <span class="day-total">R$ <?php echo money($weekData['total']); ?></span>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="period-total">Total do mês: R$ <?php echo money($monthTotal); ?></div>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
