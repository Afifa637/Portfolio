<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        crossorigin="anonymous" />

  <!-- Global CSS -->
  <link rel="stylesheet" href="css/global.css">

  <title><?php echo $page_title ?? "Portfolio | Afifa Sultana"; ?></title>

  <?php
    // Load page-specific CSS if set
    if (isset($page_css)) {
        if (is_array($page_css)) {
            foreach ($page_css as $css_file) {
                if ($css_file !== "css/global.css") {
                    echo '<link rel="stylesheet" href="' . htmlspecialchars($css_file) . '">' . "\n";
                }
            }
        } elseif ($page_css !== "css/global.css") {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($page_css) . '">' . "\n";
        }
    }
  ?>
</head>
<body>
<!-- HEADER -->
<header class="header" id="header">
  <nav class="nav container">
    <a href="#" class="nav-logo">Afifa Sultana</a>

    <!-- Navigation Menu -->
    <div class="nav-menu" id="nav-menu">
      <ul class="nav-list">
        <li class="nav-item"><a href="#home" class="nav-link">Home</a></li>
        <li class="nav-item"><a href="#about" class="nav-link">About</a></li>
        <li class="nav-item"><a href="#education" class="nav-link">Education</a></li>
        <li class="nav-item"><a href="#skills" class="nav-link">Skills</a></li>
        <li class="nav-item"><a href="#project" class="nav-link">Projects</a></li>
        <li class="nav-item"><a href="#contact" class="nav-link">Contact</a></li>
      </ul>

      <!-- Social Links (mobile only) -->
      <div class="social-links">
        <a href="https://www.facebook.com/share/1ECBdwaepb/" target="_blank" class="social-link"><i class="fab fa-facebook"></i></a>
        <a href="https://join.skype.com/invite/qIoH5oRmoO2e" target="_blank" class="social-link"><i class="fab fa-skype"></i></a>
        <a href="https://www.linkedin.com/in/afifa-sultana-346a13256/" target="_blank" class="social-link"><i class="fab fa-linkedin-in"></i></a>
        <a href="https://github.com/Afifa637?tab=repositories" target="_blank" class="social-link"><i class="fab fa-github"></i></a>
      </div>
    </div>

    <div class="nav-buttons">
  <div class="nav-settings">
    <i class="fa-solid fa-gear settings"></i>
  </div>
  <div class="nav-toggle" id="nav-toggle">
    <span></span>
    <span></span>
    <span></span>
  </div>
</div>
</nav> </header>
<main class="main">
