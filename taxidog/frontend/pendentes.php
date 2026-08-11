<?php
/** Corridas agendadas / combinados */
require_once __DIR__ . '/../backend/bootstrap.php';

Auth::requireLogin();

// Concluir ou cancelar via link
if (isset($_GET['action'], $_GET['id'])) {
    $status = $_GET['action'] === 'complete' ? 'completed' : 'cancelled';
    Rides::updateStatus((int) $_GET['id'], Auth::id(), $status);
    redirect(page_url('pendentes.php'));
}

// Mensagem vinda do update_ride.php
$flash = null;
if (isset($_GET['sucesso'])) {
    $flash = ['type' => 'alert-success', 'message' => 'Corrida atualizada com sucesso!'];
} elseif (isset($_GET['erro'])) {
    $mensagens = [
        'campos_vazios' => 'Preencha todos os campos para salvar.',
        'permissao'     => 'Essa corrida não é sua.',
    ];
    $flash = [
        'type'    => 'alert-error',
        'message' => $mensagens[$_GET['erro']] ?? 'Não foi possível salvar a corrida.',
    ];
}

$ridesByDay = Rides::groupByDay(Rides::pending(Auth::id()));
$hoje       = date('Y-m-d');
$diasSemana = nomes_dias_semana();

$pageTitle   = 'Corridas Agendadas · Táxi Dog';
$pageStyles  = ['styles/rides.css', 'styles/pages/pendentes.css'];
$pageScripts = ['js/pendentes.js'];

require __DIR__ . '/header.php';
?>
<h2>Corridas Agendadas</h2>

<div id="alert-box" class="alert<?php echo $flash ? ' ' . e($flash['type']) : ''; ?>"
     <?php echo $flash ? 'style="display:block"' : ''; ?>><?php echo $flash ? e($flash['message']) : ''; ?></div>

<?php if (empty($ridesByDay)): ?>
    <div class="no-rides">Nenhuma corrida pendente.</div>
<?php else: ?>
    <?php foreach ($ridesByDay as $dayKey => $dayRides): ?>
        <?php
            $dt       = new DateTime($dayKey);
            $dayName  = $diasSemana[(int) $dt->format('N') - 1];
            $dayTotal = array_sum(array_column($dayRides, 'price'));
            $isToday  = ($dayKey === $hoje);
        ?>
        <div class="day-block">
            <div class="day-block-header">
                <span>
                    <span class="day-name"><?php echo e($dayName); ?></span>
                    <span class="day-date"><?php echo e(formatar_data($dt)); ?></span>
                    <?php if ($isToday): ?>
                        <span class="ride-today-badge">HOJE</span>
                    <?php endif; ?>
                </span>
                <span class="day-total">R$ <?php echo money($dayTotal); ?></span>
            </div>

            <?php foreach ($dayRides as $ride): ?>
                <div class="ride-entry">
                    <!-- Informações -->
                    <div class="ride-content">
                        <div class="ride-top-row">
                            <span class="ride-client"><?php echo e($ride['client'] ?? 'Cliente'); ?></span>

                            <div class="ride-time-price-group">
                                <span class="ride-time-highlight"><?php echo e(date('H:i', strtotime($ride['ride_date']))); ?></span>
                                <span class="ride-price">R$ <?php echo money($ride['price']); ?></span>
                            </div>
                        </div>

                        <div class="ride-route">
                            <?php echo e($ride['origin']); ?> ➔ <?php echo e($ride['destination']); ?>
                        </div>

                        <div class="ride-meta">
                            <span>🐾 <?php echo (int) $ride['animal_quantity']; ?> animais</span>
                            <span>📌 <?php echo e(ucfirst($ride['period'])); ?></span>
                        </div>
                    </div>

                    <!-- Ações: concluir, editar, cancelar -->
                    <div class="ride-actions">
                        <a href="pendentes.php?action=complete&id=<?php echo (int) $ride['id']; ?>" class="btn-sm btn-success" title="Concluir">✓</a>
                        <button type="button" class="btn-sm btn-edit" data-id="<?php echo (int) $ride['id']; ?>" title="Editar">✎</button>
                        <a href="pendentes.php?action=cancel&id=<?php echo (int) $ride['id']; ?>" class="btn-sm btn-danger" title="Cancelar">✕</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Modal de edição (preenchido por js/pendentes.js) -->
<div id="editModal" class="modal-overlay">
    <div class="modal-box">
        <h3>Editar Corrida</h3>

        <form id="editForm" action="update_ride.php" method="POST">
            <input type="hidden" name="id" id="edit_id">

            <div class="form-group">
                <label for="edit_ride_date">Data e Hora</label>
                <input type="datetime-local" name="ride_date" id="edit_ride_date" required>
            </div>
            <div class="form-group">
                <label for="edit_client">Cliente</label>
                <input type="text" name="client" id="edit_client" required>
            </div>
            <div class="form-group">
                <label for="edit_origin">Origem</label>
                <input type="text" name="origin" id="edit_origin" required>
            </div>
            <div class="form-group">
                <label for="edit_destination">Destino</label>
                <input type="text" name="destination" id="edit_destination" required>
            </div>
            <div class="form-group">
                <label for="edit_animal_quantity">Quantidade de Animais</label>
                <input type="number" name="animal_quantity" id="edit_animal_quantity" min="1" required>
            </div>
            <div class="form-group">
                <label for="edit_period">Período</label>
                <select name="period" id="edit_period" required>
                    <option value="dia">Dia</option>
                    <option value="noite">Noite</option>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_price">Preço (R$)</label>
                <input type="number" step="0.01" name="price" id="edit_price" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" id="closeModalBtn">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
