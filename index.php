<?php

/**
 * Front controller.
 *
 * Handles the contact POST, then renders the single-page portfolio. Every
 * section is self-contained and reads from Content, so a section can be
 * reordered or removed here without touching anything else.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ContactHandler::handle();
    exit;
}

$navItems = [
    'about'    => 'About',
    'skills'   => 'Skills',
    'projects' => 'Projects',
    'github'   => 'Activity',
    'resume'   => 'Resume',
    'contact'  => 'Contact',
];

?>
<!doctype html>
<html lang="en" data-theme="dark">
<head>
    <?php view('layout/head'); ?>
</head>
<body>
    <?php view('layout/header', compact('navItems')); ?>

    <main id="main">
        <?php view('sections/hero'); ?>
        <?php view('sections/about'); ?>
        <?php view('sections/skills'); ?>
        <?php view('sections/projects'); ?>
        <?php view('sections/github'); ?>
        <?php view('sections/resume'); ?>
        <?php view('sections/contact'); ?>
    </main>

    <?php view('layout/footer', compact('navItems')); ?>
</body>
</html>
