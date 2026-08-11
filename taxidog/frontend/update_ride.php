<?php
/** Salva a edição feita no modal de pendentes.php */
require_once __DIR__ . '/../backend/bootstrap.php';

Auth::requireLogin();

if (!is_post()) {
    redirect(page_url('pendentes.php'));
}

$required = ['id', 'ride_date', 'client', 'origin', 'destination', 'animal_quantity', 'period', 'price'];
foreach ($required as $field) {
    if (!isset($_POST[$field]) || $_POST[$field] === '') {
        redirect(page_url('pendentes.php?erro=campos_vazios'));
    }
}

$id = (int) $_POST['id'];

// A corrida precisa existir e ser do usuário logado
if (!Rides::find($id, Auth::id())) {
    redirect(page_url('pendentes.php?erro=permissao'));
}

Rides::update($id, Auth::id(), [
    'ride_date'       => $_POST['ride_date'],
    'client'          => trim($_POST['client']),
    'origin'          => trim($_POST['origin']),
    'destination'     => trim($_POST['destination']),
    'animal_quantity' => (int) $_POST['animal_quantity'],
    'period'          => $_POST['period'],
    'price'           => (float) $_POST['price'],
]);

redirect(page_url('pendentes.php?sucesso=atualizado'));
