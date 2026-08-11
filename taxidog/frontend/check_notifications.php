<?php
/** GET check_notifications.php — avisos pendentes do usuário logado */
require_once __DIR__ . '/../backend/bootstrap.php';

Auth::requireLoginJson();

json_response(['notifications' => Notifications::pendingFor(Auth::id())]);
