<?php
/**
 * A raiz só existe para mandar quem abre /taxidog/ para o app,
 * que fica inteiro dentro de frontend/.
 */
require_once __DIR__ . '/backend/bootstrap.php';

redirect(page_url(Auth::check() ? 'home.php' : 'index.php'));
