<?php
if (!isset($conn)) {
  require_once __DIR__ . '/../includes/bootstrap.php';
}

function portfolio_table_exists(mysqli $conn, string $table): bool {
  $safe = $conn->real_escape_string($table);
  $res = $conn->query("SHOW TABLES LIKE '{$safe}'");
  return $res && $res->num_rows > 0;
}

$experiences = [];
if (portfolio_table_exists($conn, 'experience')) {
  $res = $conn->query("SELECT * FROM experience ORDER BY is_current DESC, order_no ASC, start_date DESC");
  if ($res) while ($row = $res->fetch_assoc()) $experiences[] = $row;
}

if (!$experiences) {
  $experiences = [
    ['title' => 'Full-stack Portfolio System', 'company' => 'Personal Build', 'start_date' => '2026', 'end_date' => 'Present', 'location' => 'Remote', 'description' => 'Building a dynamic PHP/MySQL portfolio with admin CRUD, polished UI motion, secure forms, and modern frontend interactions.', 'tech_stack' => 'PHP, MySQL, JavaScript, CSS, Admin CRUD', 'is_current' => 1],
    ['title' => 'Creative Web Interfaces', 'company' => 'Selected Projects', 'start_date' => '2025', 'end_date' => '2026', 'location' => 'Web', 'description' => 'Designed responsive, animated user interfaces focused on clean visual hierarchy, smooth interactions, and practical user experience.', 'tech_stack' => 'HTML, CSS, JavaScript, Responsive UI', 'is_current' => 0],
  ];
}
?>
<section class="experience section" id="experience">
  <div class="container">
    <span class="terminal-kicker"><i class="fa-solid fa-code-branch"></i> npm run experience</span>
    <h2 class="section-title" data-title="Professional Track">Experience <span>Timeline</span></h2>
    <div class="experience-grid">
      <?php foreach ($experiences as $exp): ?>
        <?php $tags = array_filter(array_map('trim', explode(',', $exp['tech_stack'] ?? ''))); ?>
        <article class="experience-card">
          <p class="exp-company"><?= e($exp['company'] ?? 'Experience'); ?> <?= !empty($exp['is_current']) ? '• current' : ''; ?></p>
          <h3><?= e($exp['title'] ?? 'Role'); ?></h3>
          <div class="meta">
            <span><i class="fa-regular fa-calendar"></i> <?= e($exp['start_date'] ?? ''); ?> – <?= e($exp['end_date'] ?? 'Present'); ?></span>
            <span><i class="fa-solid fa-location-dot"></i> <?= e($exp['location'] ?? 'Remote'); ?></span>
          </div>
          <p><?= nl2br(e($exp['description'] ?? '')); ?></p>
          <?php if ($tags): ?><div class="tech-tags"><?php foreach ($tags as $tag): ?><span><?= e($tag); ?></span><?php endforeach; ?></div><?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
