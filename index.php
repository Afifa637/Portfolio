<?php

/**
 * Front controller for the home page.
 *
 * Handles the contact POST, then renders the sections. Each section reads its
 * own content, so reordering or removing one is a one-line change here.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ContactHandler::handle();
    exit;
}

$isHome = true;

?>
<!doctype html>
<html lang="en" class="no-js" data-theme="dark">
<head>
    <?php view('layout/head'); ?>
</head>
<body data-home="<?= e(base_path()) ?>/">
    <?php view('layout/header', compact('isHome')); ?>

    <main class="site" id="main">
        <?php
        foreach (['hero', 'think', 'stack', 'featured', 'hood', 'archive', 'lab', 'activity', 'journey', 'career', 'contact'] as $section) {
            view('sections/' . $section);
        }
        ?>
    </main>

    <?php view('layout/footer', compact('isHome')); ?>
</body>
</html>
