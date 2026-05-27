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
$name = $home['name'] ?? 'Afifa Sultana';
$nameParts = preg_split('/\s+/', trim($name));
$firstName = $nameParts[0] ?? 'Afifa';
$lastName = trim(str_replace($firstName, '', $name)) ?: 'Sultana';
$email = $home['email'] ?? 'hello@example.com';
?>

<section class="home section" id="home">
  <div class="hero-backdrop" aria-hidden="true"></div>
  <canvas class="hero-particles" id="hero-particles" aria-hidden="true"></canvas>

  <div class="home-container container terminal-layout">
    <div class="home-content">
      <p class="eyebrow"><?= e($home['subtitle'] ?? 'Creative Developer & Designer'); ?></p>

      <h1 class="home-title">
        <span><?= e($firstName); ?></span>
        <?= e($lastName); ?>
      </h1>

      <h3 class="home-job"><span class="typing-text"></span></h3>

      <p class="home-description"><?= nl2br(e($home['description'] ?? 'I craft cinematic, high-performance web experiences that blend elegant design with robust engineering.')); ?></p>

      <div class="home-info">
        <div class="info-item"><i class="fa-solid fa-location-dot"></i> <?= e($home['location'] ?? 'Remote'); ?></div>
        <div class="info-item"><i class="fa-solid fa-envelope"></i> <?= e($email); ?></div>
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
          <h4 data-count="<?= e((string) $projectCount); ?>"><?= e((string) $projectCount); ?>+</h4>
          <p>Projects Delivered</p>
        </div>
        <div class="stat-card">
          <h4 data-count="<?= e((string) $skillCount); ?>"><?= e((string) $skillCount); ?>+</h4>
          <p>Technologies</p>
        </div>
        <div class="stat-card">
          <h4 data-count="<?= e((string) $eduCount); ?>"><?= e((string) $eduCount); ?></h4>
          <p>Education Milestones</p>
        </div>
      </div>
    </div>

    <aside class="hero-console" aria-label="Developer profile console">
      <div class="console-tabs">
        <span class="console-tab active">portfolio.php</span>
        <span class="console-tab">profile.json</span>
        <span class="console-tab">terminal</span>
      </div>
      <div class="profile-terminal">
        <div class="home-img-wrapper">
          <img src="<?= e($home['profile_image'] ?? 'assets/images/profile1.png'); ?>" alt="<?= e($name); ?>" class="home-profile" loading="lazy" />
          <div class="profile-ring" aria-hidden="true"></div>
          <div class="profile-glow" aria-hidden="true"></div>
        </div>
      </div>
      <div class="console-body">
        <div class="code-line"><span class="line-no">01</span><span><span class="code-key">const</span> profile = {</span></div>
        <div class="code-line"><span class="line-no">02</span><span>&nbsp;&nbsp;name: <span class="code-string">"<?= e($name); ?>"</span>,</span></div>
        <div class="code-line"><span class="line-no">03</span><span>&nbsp;&nbsp;role: <span class="code-value">"<span class="typing-text-mirror"><?= e($roles[0] ?? 'Developer'); ?></span>"</span>,</span></div>
        <div class="code-line"><span class="line-no">04</span><span>&nbsp;&nbsp;projects: <span class="code-value"><?= e((string)$projectCount); ?></span>, skills: <span class="code-value"><?= e((string)$skillCount); ?></span>,</span></div>
        <div class="code-line"><span class="line-no">05</span><span>&nbsp;&nbsp;status: <span class="code-string">"<?= e($availability); ?>"</span></span></div>
        <div class="code-line"><span class="line-no">06</span><span>};</span></div>
        <div class="terminal-output"><pre id="terminal-typing"></pre><span class="cursor-blink"></span></div>
      </div>
      <div class="dev-badges">
        <span class="dev-badge">PHP</span><span class="dev-badge">MySQL</span><span class="dev-badge">JavaScript</span><span class="dev-badge">UI Motion</span>
      </div>
    </aside>
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
