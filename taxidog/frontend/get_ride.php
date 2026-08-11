<?php
/** GET get_ride.php?id=123 — dados de uma corrida para o modal de edição */
require_once __DIR__ . '/../backend/bootstrap.php';

Auth::requireLoginJson();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    json_response(['error' => 'ID inválido'], 400);
}

$ride = Rides::find((int) $_GET['id'], Auth::id());

if (!$ride) {
    json_response(['error' => 'Corrida não encontrada ou não pertence a você'], 404);
}

json_response([
    'id'              => (int) $ride['id'],
    'ride_date'       => $ride['ride_date'],
    'client'          => $ride['client'],
    'origin'          => $ride['origin'],
    'destination'     => $ride['destination'],
    'animal_quantity' => (int) $ride['animal_quantity'],
    'period'          => $ride['period'],
    'price'           => $ride['price'],
]);
