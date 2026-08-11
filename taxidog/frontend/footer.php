<?php
/**
 * Fechamento da página. A página define antes do require:
 *   $pageScripts - JS extra da página (além do global)
 */
$pageScripts = $pageScripts ?? [];
?>
    </div><!-- /.container -->

    <!-- JS global: registra o Service Worker do PWA -->
    <script src="<?php echo asset('js/app.js'); ?>"></script>

    <!-- JS específico desta página -->
    <?php foreach ($pageScripts as $script): ?>
        <script src="<?php echo asset($script); ?>"></script>
    <?php endforeach; ?>
</body>
</html>
