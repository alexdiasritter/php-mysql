<?php
/**
 * Abertura da página. A página define antes do require:
 *   $pageTitle  - título da aba
 *   $pageStyles - CSS extra da página (além dos globais)
 */
$pageTitle  = $pageTitle  ?? 'Táxi Dog';
$pageStyles = $pageStyles ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title><?php echo e($pageTitle); ?></title>

    <!-- Ícone da aba / atalho na tela inicial -->
    <link rel="icon" href="<?php echo e(app_url('app-icon.png')); ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo e(app_url('app-icon.png')); ?>">

    <!-- PWA -->
    <link rel="manifest" href="<?php echo e(app_url('manifest.json')); ?>">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Estilos globais -->
    <link rel="stylesheet" href="<?php echo asset('styles/base.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('styles/components.css'); ?>">

    <!-- Estilos específicos desta página -->
    <?php foreach ($pageStyles as $style): ?>
        <link rel="stylesheet" href="<?php echo asset($style); ?>">
    <?php endforeach; ?>
</head>
<body>
    <?php if (Auth::check()): ?>
        <nav class="navbar">
            <a href="home.php" class="brand">🐕 Táxi Dog</a>
        </nav>
    <?php endif; ?>

    <div class="container">
