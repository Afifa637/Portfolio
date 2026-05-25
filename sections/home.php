<?php
if (!isset($is_included)) {
  $page_title = "Home | Afifa Sultana";
}

if (!isset($conn)) {
  require_once __DIR__ . '/../includes/bootstrap.php';
}

$home_sql = "SELECT * FROM home_info LIMIT 1";
$home_result = $conn->query($home_sql);
$home = $home_result ? $home_result->fetch_assoc() : [];

$roles_sql = "SELECT role FROM home_roles";
$roles_result = $conn->query($roles_sql);
$roles = [];
if ($roles_result) {
  while ($row = $roles_result->fetch_assoc()) {
    $roles[] = $row['role'];
  }
}

$social_sql = "SELECT * FROM home_socials";
$social_result = $conn->query($social_sql);

$projectCount = (int)($conn->query("SELECT COUNT(*) AS total FROM projects")?->fetch_assoc()['total'] ?? 0);
$skillCount = (int)($conn->query("SELECT COUNT(*) AS total FROM skills")?->fetch_assoc()['total'] ?? 0);
$eduCount = (int)($conn->query("SELECT COUNT(*) AS total FROM education")?->fetch_assoc()['total'] ?? 0);
$availability = $home['availability'] ?? 'Available for work';
?>

<section class="home section" id="home">
  <div class="hero-backdrop" aria-hidden="true"></div>
  <canvas class="hero-particles" id="hero-particles" aria-hidden="true"></canvas>

  <div class="home-container container">
    <div class="home-content">
      <p class="eyebrow"><?= e($home['subtitle'] ?? 'Creative Developer & Designer'); ?></p>

      <h1 class="home-title">
        <span><?= e(explode(" ", $home['name'] ?? 'Afifa')[0]); ?></span>
        <?= e(explode(" ", $home['name'] ?? 'Sultana')[1] ?? ''); ?>
      </h1>

      <h3 class="home-job"><span class="typing-text"></span></h3>

      <p class="home-description"><?= nl2br(e($home['description'] ?? 'I craft cinematic, high-performance web experiences that blend elegant design with robust engineering.')); ?></p>

      <div class="home-info">
        <div class="info-item"><i class="fa-solid fa-location-dot"></i> <?= e($home['location'] ?? 'Remote'); ?></div>
        <div class="info-item"><i class="fa-solid fa-envelope"></i> <?= e($home['email'] ?? 'hello@example.com'); ?></div>
        <div class="info-item"><i class="fa-solid fa-circle-check"></i> <?= e($availability); ?></div>
      </div>

      <div class="home-socials social-links">
        <?php if ($social_result): ?>
          <?php while ($social = $social_result->fetch_assoc()): ?>
            <a href="<?= e($social['url']); ?>" target="_blank" rel="noopener" class="social-link" aria-label="<?= e($social['platform'] ?? 'Social'); ?>">
              <i class="<?= e($social['icon_class']); ?>"></i>
            </a>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>

      <div class="home-btns">
        <a href="#contact" class="btn primary-btn"><i class="fa-solid fa-paper-plane"></i> Hire Me</a>
        <a href="download_cv.php" class="btn outline-btn"><i class="fa-solid fa-download"></i> Download CV</a>
        <a href="#project" class="btn ghost-btn"><i class="fa-solid fa-grid"></i> View Projects</a>
      </div>

      <div class="home-stats">
        <div class="stat-card">
          <h4><?= e((string) $projectCount); ?>+</h4>
          <p>Projects Delivered</p>
        </div>
        <div class="stat-card">
          <h4><?= e((string) $skillCount); ?>+</h4>
          <p>Technologies</p>
        </div>
        <div class="stat-card">
          <h4><?= e((string) $eduCount); ?></h4>
          <p>Education Milestones</p>
        </div>
        <div class="stat-card">
          <h4><?= e($availability); ?></h4>
          <p>Availability</p>
        </div>
      </div>
    </div>

    <div class="home-banner">
      <div class="home-img-wrapper">
        <img src="<?= e($home['profile_image'] ?? 'assets/images/profile.jpg'); ?>" alt="<?= e($home['name'] ?? 'Profile'); ?>" class="home-profile" loading="lazy" />
        <div class="profile-ring" aria-hidden="true"></div>
        <div class="profile-glow" aria-hidden="true"></div>
      </div>
    </div>
  </div>

  <a href="#about" class="scroll-indicator" aria-label="Scroll to About">
    <span></span>
  </a>

  <div class="floating-shapes" aria-hidden="true">
    <span></span><span></span><span></span>
  </div>
</section>

<script>
  window.homeRoles = <?= json_encode($roles ?: ['Web Developer', 'App Developer', 'Designer']); ?>;
</script>