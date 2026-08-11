<?php
/** Dashboard de ganhos */
require_once __DIR__ . '/../backend/bootstrap.php';

Auth::requireLogin();

$dashboard  = Earnings::dashboard(Auth::id());
$weekStats  = $dashboard['week_stats'];
$monthStats = $dashboard['month_stats'];
$charts     = $dashboard['charts'];

$pageTitle   = 'Ganhos · Táxi Dog';
$pageStyles  = ['styles/pages/earnings.css'];
$pageScripts = ['js/earnings.js'];

require __DIR__ . '/header.php';
?>
<h2 class="text-center">Dashboard de Ganhos</h2>

<!-- Resumo -->
<div class="stats-grid">
    <div class="card stat-card">
        <h3 class="stat-label">Esta Semana</h3>
        <p class="stat-value stat-value-week">R$ <?php echo money($weekStats['total_earnings'] ?? 0); ?></p>
        <small><?php echo (int) ($weekStats['total_rides'] ?? 0); ?> corridas</small>
    </div>
    <div class="card stat-card">
        <h3 class="stat-label">Este Mês</h3>
        <p class="stat-value stat-value-month">R$ <?php echo money($monthStats['total_earnings'] ?? 0); ?></p>
        <small><?php echo (int) ($monthStats['total_rides'] ?? 0); ?> corridas</small>
    </div>
</div>

<div class="card chart-card">
    <h3>Tendência (Últimos 30 dias)</h3>
    <div class="chart-box"><canvas id="trendChart"></canvas></div>
</div>

<div class="card chart-card">
    <h3>Visão Geral</h3>
    <div class="chart-box"><canvas id="earningsChart"></canvas></div>
</div>

<div class="card chart-card">
    <h3>Comparativo Mensal (últimos 6 meses)</h3>
    <div class="chart-box"><canvas id="monthlyChart"></canvas></div>
</div>

<div class="card chart-card">
    <h3>Clínicas Mais Acionadas</h3>
    <?php if (empty($charts['clinics']['labels'])): ?>
        <p class="chart-empty">Nenhuma corrida concluída bateu com a lista de clínicas ainda.</p>
    <?php else: ?>
        <div class="chart-box" style="--chart-height: <?php echo max(220, count($charts['clinics']['labels']) * 42); ?>px">
            <canvas id="clinicsChart"></canvas>
        </div>
    <?php endif; ?>
</div>

<div class="card chart-card">
    <h3>Média de Valor por Corrida (por mês)</h3>
    <div class="chart-box chart-box-sm"><canvas id="avgChart"></canvas></div>
</div>

<div class="charts-2col">
    <div class="card chart-card">
        <h3>Bairros de Origem</h3>
        <canvas id="originsChart" height="120"></canvas>
    </div>
    <div class="card chart-card">
        <h3>Bairros de Destino</h3>
        <canvas id="destinationsChart" height="120"></canvas>
    </div>
</div>

<!-- Os dados vão para o JS por aqui, sem PHP dentro do JavaScript -->
<script type="application/json" id="earnings-data">
    <?php echo json_encode($charts, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php require __DIR__ . '/footer.php'; ?>
