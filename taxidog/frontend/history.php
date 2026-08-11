<?php
/** Histórico semanal de corridas concluídas, detalhado por dia */
require_once __DIR__ . '/../backend/bootstrap.php';

Auth::requireLogin();

$offset = (int) ($_GET['offset'] ?? 0);
if ($offset > 0) {
    $offset = 0; // não dá para navegar para o futuro
}

$today = new DateTime();
$today->modify(($offset * 7) . ' days');
$monday = inicio_da_semana($today);
$sunday = (clone $monday)->modify('+6 days');

$erroConsulta = null;

try {
    $rides = Rides::completedBetween(Auth::id(), $monday->format('Y-m-d'), $sunday->format('Y-m-d'));
} catch (Exception $e) {
    $rides = [];
    $erroConsulta = $e->getMessage();
}

$ridesByDay = Rides::groupByDay($rides);
$weekTotal  = array_sum(array_column($rides, 'price'));
$diasSemana = nomes_dias_semana();

$pageTitle  = 'Histórico Semanal · Táxi Dog';
$pageStyles = ['styles/rides.css', 'styles/pages/history.css'];

require __DIR__ . '/header.php';
?>
<div class="page-head">
    <h2>Histórico Semanal (concluídas)</h2>
    <a href="history-month.php?month_offset=0" class="view-toggle">Mês</a>
</div>

<?php if ($erroConsulta): ?>
    <div class="query-error">ERRO na consulta: <?php echo e($erroConsulta); ?></div>
<?php endif; ?>

<div class="week-nav">
    <a href="history.php?offset=<?php echo $offset - 1; ?>" class="week-btn">&#8249;</a>
    <div class="week-label">
        <?php echo e(formatar_data($monday) . ' - ' . formatar_data($sunday)); ?>
        <small>
            <?php echo e($monday->format('Y')); ?><?php echo $offset === 0 ? ' · Semana atual' : ''; ?>
        </small>
    </div>
    <a href="history.php?offset=<?php echo $offset + 1; ?>"
       class="week-btn <?php echo $offset >= 0 ? 'disabled' : ''; ?>">&#8250;</a>
</div>

<?php
$cursor = clone $monday;
for ($i = 0; $i < 7; $i++):
    $dayKey   = $cursor->format('Y-m-d');
    $dayRides = $ridesByDay[$dayKey] ?? [];
    $dayTotal = array_sum(array_column($dayRides, 'price'));
?>
    <div class="day-block">
        <div class="day-block-header">
            <span class="day-name"><?php echo e($diasSemana[$i]); ?></span>
            <span class="day-date"><?php echo e(formatar_data($cursor)); ?></span>
            <?php if (!empty($dayRides)): ?>
                <span class="day-total">R$ <?php echo money($dayTotal); ?></span>
            <?php endif; ?>
        </div>

        <?php if (empty($dayRides)): ?>
            <div class="day-empty">Nenhuma corrida concluída nesse dia</div>
        <?php else: ?>
            <?php foreach ($dayRides as $ride): ?>
                <div class="ride-card">
                    <div class="ride-info">
                        <span class="ride-client"><?php echo e($ride['client']); ?></span>
                        <span class="ride-route">
                            <?php echo e($ride['origin']); ?> &rarr; <?php echo e($ride['destination']); ?>
                        </span>
                    </div>
                    <span class="ride-price">R$ <?php echo money($ride['price']); ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php
    $cursor->modify('+1 day');
endfor;
?>

<div class="period-total">Total da semana: R$ <?php echo money($weekTotal); ?></div>
<?php require __DIR__ . '/footer.php'; ?>
