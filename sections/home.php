<?php
if (!isset($is_included)) {
    $page_title = "Home | Afifa Sultana";
}

$conn = new mysqli("localhost", "root", "", "portfolio_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$home_sql = "SELECT * FROM home_info LIMIT 1";
$home_result = $conn->query($home_sql);
$home = $home_result->fetch_assoc();

$roles_sql = "SELECT role FROM home_roles";
$roles_result = $conn->query($roles_sql);
$roles = [];
while ($row = $roles_result->fetch_assoc()) {
    $roles[] = $row['role'];
}

$social_sql = "SELECT * FROM home_socials";
$social_result = $conn->query($social_sql);
?>

<section class="home section" id="home">
  <div class="home-container container">

    <div class="home-content">
      <h2 class="home-subtitle"><?= htmlspecialchars($home['subtitle']); ?></h2>
      <h1 class="home-title">
        <span><?= htmlspecialchars(explode(" ", $home['name'])[0]); ?></span>
        <?= htmlspecialchars(explode(" ", $home['name'])[1] ?? ''); ?>
      </h1>

      <h3 class="home-job"><span class="typing-text"></span></h3>

      <p class="home-description"><?= nl2br(htmlspecialchars($home['description'])); ?></p>

      <div class="home-info">
        <div class="info-item"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($home['location']); ?></div>
        <div class="info-item"><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($home['email']); ?></div>
        <div class="info-item"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($home['availability']); ?></div>
      </div>

      <div class="home-socials social-links">
        <?php while ($social = $social_result->fetch_assoc()): ?>
          <a href="<?= htmlspecialchars($social['url']); ?>" target="_blank" class="social-link">
            <i class="<?= htmlspecialchars($social['icon_class']); ?>"></i>
          </a>
        <?php endwhile; ?>
      </div>

      <div class="home-btns">
        <a href="#contact" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Hire Me</a>
        <a download href="<?= htmlspecialchars($home['cv_link']); ?>" class="btn"><i class="fa-solid fa-download"></i> Download CV</a>
      </div>
    </div>

    <div class="home-banner">
      <div class="home-img-wrapper">
        <img src="<?= htmlspecialchars($home['profile_image']); ?>" alt="<?= htmlspecialchars($home['name']); ?>" class="home-profile" />
      </div>
    </div>
  </div>
  <div class="shape-bg"></div>
</section>
