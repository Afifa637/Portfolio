<?php
if (!isset($is_included)) {
  $page_title = "About | Afifa Sultana";
}

if (!isset($conn)) {
  require_once __DIR__ . '/../includes/bootstrap.php';
}

$sql = "SELECT * FROM about LIMIT 1";
$result = $conn->query($sql);
$about = $result ? $result->fetch_assoc() : [];

$homeRow = $conn->query("SELECT * FROM home_info LIMIT 1")?->fetch_assoc() ?? [];
$projectCount = (int)($conn->query("SELECT COUNT(*) AS total FROM projects")?->fetch_assoc()['total'] ?? 0);
$skillCount = (int)($conn->query("SELECT COUNT(*) AS total FROM skills")?->fetch_assoc()['total'] ?? 0);

$allowed_tags = '<p><br><strong><em><span><ul><ol><li>';
$short_intro = $about['short_intro'] ?? 'I am a creative developer specializing in cinematic, high-impact digital experiences.';
$long_intro = $about['long_intro'] ?? 'I focus on crafting premium front-end experiences with optimized performance and strong visual storytelling.';
$short_intro = strip_tags($short_intro, $allowed_tags);
$long_intro = strip_tags($long_intro, $allowed_tags);
?>

<section class="about section" id="about">
  <div class="about-container container">
    <h2 class="section-title" data-title="Who I Am">About <span>Me</span></h2>

    <div class="about-grid">
      <div class="about-media">
        <div class="about-img">
          <img src="<?= e($about['profile_image'] ?? ($homeRow['profile_image'] ?? 'assets/images/profile.jpg')); ?>" alt="<?= e($homeRow['name'] ?? 'Profile'); ?>" class="about-profile" loading="lazy" />
        </div>
        <div class="about-counters">
          <div class="counter-card">
            <h4><?= e((string) $projectCount); ?>+</h4>
            <p>Projects</p>
          </div>
          <div class="counter-card">
            <h4><?= e((string) $skillCount); ?>+</h4>
            <p>Skills</p>
          </div>
          <div class="counter-card">
            <h4><?= e($homeRow['availability'] ?? 'Open'); ?></h4>
            <p>Availability</p>
          </div>
        </div>
      </div>

      <div class="about-content">
        <p class="about-intro"><?= $short_intro; ?></p>

        <div class="read-more-content" id="moreText">
          <?= $long_intro; ?>
        </div>

        <div class="about-details">
          <div class="detail-item"><span>Location</span>
            <p><?= e($homeRow['location'] ?? 'Remote'); ?></p>
          </div>
          <div class="detail-item"><span>Email</span>
            <p><?= e($homeRow['email'] ?? 'hello@example.com'); ?></p>
          </div>
          <div class="detail-item"><span>Focus</span>
            <p>UI/UX, Full-stack, Product Design</p>
          </div>
        </div>

        <div class="about-actions">
          <button class="btn outline-btn" id="readMoreBtn" onclick="toggleReadMore()">Read More</button>
          <a href="download_cv.php" class="btn primary-btn"><i class="fa-solid fa-download"></i> Download CV</a>
        </div>
      </div>
    </div>

    <div class="services-grid">
      <div class="service-card">
        <h3><i class="fa-solid fa-code"></i> Web Development</h3>
        <p>High-performance, responsive, SEO-optimized websites with cinematic polish.</p>
      </div>
      <div class="service-card">
        <h3><i class="fa-solid fa-layer-group"></i> UI/UX Design</h3>
        <p>Human-centered interfaces with strong visual hierarchy and immersive motion.</p>
      </div>
      <div class="service-card">
        <h3><i class="fa-solid fa-server"></i> Backend Development</h3>
        <p>Secure, scalable backends with clean APIs, validation, and strong performance.</p>
      </div>
      <div class="service-card">
        <h3><i class="fa-solid fa-mobile-screen"></i> App Development</h3>
        <p>Modern, cross-platform experiences with seamless interactions and speed.</p>
      </div>
    </div>

    <div class="section-deco deco-right">
      <img src="assets/images/deco2.png" alt="" class="shape" />
    </div>
  </div>
</section>