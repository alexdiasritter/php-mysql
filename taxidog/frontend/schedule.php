<?php
/** Agendamento de corrida */
require_once __DIR__ . '/../backend/bootstrap.php';

Auth::requireLogin();

if (is_post()) {
    $input = json_input();

    try {
        Rides::create(Auth::id(), [
            'client'          => trim($input['client'] ?? '') ?: 'Cliente',
            'origin'          => trim($input['origin'] ?? ''),
            'destination'     => trim($input['destination'] ?? ''),
            'price'           => str_replace(',', '.', (string) ($input['price'] ?? '0')),
            'period'          => $input['period'] ?? 'dia',
            'animal_quantity' => (int) ($input['animal_quantity'] ?? 1),
            'ride_date'       => $input['ride_date'] ?? date('Y-m-d H:i:s'),
        ]);

        json_response(['success' => true, 'message' => 'Corrida agendada com sucesso!']);
    } catch (Exception $e) {
        $detail = config('debug') ? ': ' . $e->getMessage() : '';
        json_response(['success' => false, 'message' => 'Erro ao salvar' . $detail]);
    }
}

// Preenche o campo de data/hora com o momento atual
$currentDatetime = date('Y-m-d\TH:i');

$pageTitle   = 'Agendar · Táxi Dog';
$pageScripts = ['js/schedule.js'];

require __DIR__ . '/header.php';
?>
<h2>Agendar Corrida</h2>

<div id="alert-box" class="alert"></div>

<div class="card">
    <form id="schedule-form">
        <div class="form-group">
            <label for="client">Nome Cliente</label>
            <input type="text" id="client" required>
        </div>
        <div class="form-group">
            <label for="origin">Bairro de Origem</label>
            <input type="text" id="origin" required>
        </div>
        <div class="form-group">
            <label for="destination">Bairro de Destino</label>
            <input type="text" id="destination" required>
        </div>
        <div class="form-group">
            <label for="price">Valor (R$)</label>
            <input type="text" id="price" placeholder="Ex: 25.50" required>
        </div>
        <div class="form-group">
            <label for="period">Período</label>
            <select id="period">
                <option value="dia">Dia</option>
                <option value="noite">Noite</option>
            </select>
        </div>
        <div class="form-group">
            <label for="animal_quantity">Quantidade de Animais</label>
            <input type="number" id="animal_quantity" value="1" min="1" required>
        </div>
        <div class="form-group">
            <label for="ride_date">Data e Hora</label>
            <input type="datetime-local" id="ride_date" value="<?php echo e($currentDatetime); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Agendar Corrida</button>
    </form>
</div>
<?php require __DIR__ . '/footer.php'; ?>
