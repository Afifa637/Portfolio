<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    crossorigin="anonymous" />

  <link rel="stylesheet" href="css/global.css">

  <title><?php echo $page_title ?? "Portfolio | Afifa Sultana"; ?></title>

  <?php
  $theme = 'dark';

  if (isset($page_css)) {
    if (is_array($page_css)) {
      foreach ($page_css as $css_file) {
        if ($css_file !== "css/global.css") {
          echo '<link rel="stylesheet" href="' . e($css_file) . '">' . "\n";
        }
      }
    } elseif ($page_css !== "css/global.css") {
      echo '<link rel="stylesheet" href="' . e($page_css) . '">' . "\n";
    }
  }
  ?>
</head>

<body class="<?php echo e($theme); ?>">
  <div class="preloader" id="preloader" aria-hidden="true">
    <div class="boot-panel">
      <div class="boot-top"><span></span><span></span><span></span></div>
      <div class="boot-body">
        <p>$ initializing portfolio...</p>
        <p>$ loading dynamic modules...</p>
        <p>$ rendering futuristic interface...</p>
        <div class="boot-line"></div>
      </div>
    </div>
  </div>

  <div class="site-bg" aria-hidden="true"><span class="orb"></span><span class="orb"></span><span class="orb"></span></div>
  <div class="grid-overlay" aria-hidden="true"></div>
  <div class="noise-overlay" aria-hidden="true"></div>
  <div class="cursor-glow" aria-hidden="true"></div>
  <div class="custom-cursor" aria-hidden="true"></div>
  <div class="cursor-ring" aria-hidden="true"></div>

  <a class="skip-link" href="#home">Skip to content</a>
  <div class="scroll-progress" aria-hidden="true">
    <span class="scroll-progress-bar" id="scroll-progress-bar"></span>
  </div>

  <header class="header" id="header">
    <nav class="nav container" aria-label="Primary">
      <a href="#home" class="nav-logo">Afifa Sultana</a>

      <div class="nav-menu" id="nav-menu" role="dialog" aria-modal="true" aria-label="Mobile navigation">
        <ul class="nav-list" role="list">
          <li class="nav-item"><a href="#home" class="nav-link">Home</a></li>
          <li class="nav-item"><a href="#about" class="nav-link">About</a></li>
          <li class="nav-item"><a href="#experience" class="nav-link">Experience</a></li>
          <li class="nav-item"><a href="#education" class="nav-link">Education</a></li>
          <li class="nav-item"><a href="#skills" class="nav-link">Skills</a></li>
          <li class="nav-item"><a href="#project" class="nav-link">Projects</a></li>
          <li class="nav-item"><a href="#current-work" class="nav-link">Current</a></li>
          <li class="nav-item"><a href="#contact" class="nav-link">Contact</a></li>
        </ul>

        <div class="social-links" aria-label="Social links">
          <a href="https://www.facebook.com/share/1ECBdwaepb/" target="_blank" rel="noopener" class="social-link" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
          <a href="https://join.skype.com/invite/qIoH5oRmoO2e" target="_blank" rel="noopener" class="social-link" aria-label="Skype"><i class="fab fa-skype"></i></a>
          <a href="https://www.linkedin.com/in/afifa-sultana-346a13256/" target="_blank" rel="noopener" class="social-link" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
          <a href="https://github.com/Afifa637?tab=repositories" target="_blank" rel="noopener" class="social-link" aria-label="GitHub"><i class="fab fa-github"></i></a>
        </div>
      </div>

      <div class="nav-buttons">
        <button class="nav-settings" id="command-open" aria-label="Open command palette" type="button" title="Ctrl/Cmd + K">
          <i class="fa-solid fa-terminal settings"></i>
        </button>

        <button class="nav-settings" id="switcher-toggle" aria-label="Open style switcher" type="button">
          <i class="fa-solid fa-gear settings"></i>
        </button>

        <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="nav-menu" type="button">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
      <div class="style-switcher" id="style-switcher">
        <div class="switcher-header">
          <h2 class="style-switcher-title">Style Switcher</h2>
          <button id="switcher-close" class="switcher-close" type="button" aria-label="Close style switcher">&times;</button>
        </div>

        <div class="style-switcher-item">
          <h3 class="style-switcher-subtitle">Color</h3>
          <div class="style-switcher-colors">
            <span class="style-switcher-color active-color" style="--hue: 155"></span>
            <span class="style-switcher-color" style="--hue: 185"></span>
            <span class="style-switcher-color" style="--hue: 345"></span>
            <span class="style-switcher-color" style="--hue: 50"></span>
          </div>
        </div>

        <div class="style-switcher-item">
          <h3 class="style-switcher-subtitle">Theme Presets</h3>
          <div class="preset-grid">
            <button type="button" class="preset-btn" data-preset="cyber-blue">Cyber Blue</button>
            <button type="button" class="preset-btn" data-preset="purple-galaxy">Purple Galaxy</button>
            <button type="button" class="preset-btn" data-preset="emerald-matrix">Emerald Matrix</button>
            <button type="button" class="preset-btn" data-preset="crimson-neon">Crimson Neon</button>
            <button type="button" class="preset-btn" data-preset="solar-gold">Solar Gold</button>
            <button type="button" class="preset-btn" data-preset="minimal-light">Minimal Light</button>
          </div>
        </div>

        <div class="style-switcher-item">
          <h3 class="style-switcher-subtitle">Admin</h3>
          <a href="admin/login.php" class="admin-login-btn">
            <i class="fa-solid fa-user-shield"></i> Admin Login
          </a>
        </div>
      </div>
    </nav>
  </header>
  <main class="main">