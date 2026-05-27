<?php
if (!isset($conn)) {
  require_once __DIR__ . '/../includes/bootstrap.php';
}
if (!function_exists('portfolio_table_exists')) {
  function portfolio_table_exists(mysqli $conn, string $table): bool {
    $safe = $conn->real_escape_string($table);
    $res = $conn->query("SHOW TABLES LIKE '{$safe}'");
    return $res && $res->num_rows > 0;
  }
}
$currentItems = [];
if (portfolio_table_exists($conn, 'current_work')) {
  $res = $conn->query("SELECT * FROM current_work ORDER BY order_no ASC, created_at DESC");
  if ($res) while ($row = $res->fetch_assoc()) $currentItems[] = $row;
}
if (!$currentItems) {
  $currentItems = [
    ['title' => 'Futuristic Portfolio Upgrade', 'type' => 'Project', 'description' => 'A terminal-inspired dynamic portfolio with motion design, command palette, minimap navigation, and premium project showcases.', 'technologies' => 'PHP, MySQL, CSS, JavaScript', 'status' => 'Building', 'progress' => 85],
    ['title' => 'Advanced UI Motion System', 'type' => 'Learning', 'description' => 'Practicing card tilt, magnetic buttons, scroll-linked reveals, accessible modals, and high-performance interaction patterns.', 'technologies' => 'Animation, Accessibility, Performance', 'status' => 'Active', 'progress' => 70],
    ['title' => 'Admin Dashboard Polish', 'type' => 'Project', 'description' => 'Improving content management screens with a glass sidebar, metric cards, improved tables, and premium login visuals.', 'technologies' => 'PHP CRUD, MySQL, Dashboard UI', 'status' => 'In Progress', 'progress' => 65],
    ['title' => 'Interface Research Lab', 'type' => 'Research', 'description' => 'Exploring how terminal-inspired interfaces, roadmap motion, and case-study project views can make technical portfolios more memorable.', 'technologies' => 'UX Research, Motion Design, UI Systems', 'status' => 'Exploring', 'progress' => 55],
  ];
}
$types = [];
foreach ($currentItems as $item) {
  $type = strtolower(trim((string)($item['type'] ?? 'Project')));
  if ($type !== '') $types[$type] = ucfirst($type);
}
?>
<section class="current-work section" id="current-work">
  <div class="container">
    <span class="terminal-kicker"><i class="fa-solid fa-bolt"></i> ./currently-building</span>
    <h2 class="section-title" data-title="Now Loading">Current <span>Work</span></h2>
    <p class="current-subtitle">Scroll through a live build board. Each item grows into focus near the center of the screen and shrinks back as you move away.</p>

    <div class="current-filters" aria-label="Current work filters">
      <button class="current-filter active" type="button" data-current-filter="all">All</button>
      <?php foreach ($types as $slug => $label): ?>
        <button class="current-filter" type="button" data-current-filter="<?= e($slug); ?>"><?= e($label); ?></button>
      <?php endforeach; ?>
    </div>

    <div class="current-stage">
      <div class="current-orbit" aria-hidden="true"><span></span><span></span><span></span></div>
      <div class="current-grid">
        <?php foreach ($currentItems as $index => $item): ?>
          <?php
            $tags = array_filter(array_map('trim', explode(',', $item['technologies'] ?? '')));
            $progress = max(0, min(100, (int)($item['progress'] ?? 50)));
            $typeSlug = strtolower(trim((string)($item['type'] ?? 'Project')));
          ?>
          <article class="current-card scroll-morph" data-current-type="<?= e($typeSlug); ?>" style="--progress: <?= $progress; ?>%; --delay-index: <?= (int)$index; ?>">
            <div class="current-card-head">
              <p class="current-type"><i class="fa-solid fa-code-branch"></i> <?= e($item['type'] ?? 'Project'); ?></p>
              <span class="current-status"><?= e($item['status'] ?? 'Active'); ?></span>
            </div>
            <h3><?= e($item['title'] ?? 'Current Work'); ?></h3>
            <div class="meta"><span><?= e((string)$progress); ?>% progress</span><span>Live module</span></div>
            <p><?= nl2br(e($item['description'] ?? '')); ?></p>
            <div class="progress-track"><span></span></div>
            <?php if ($tags): ?><div class="tech-tags"><?php foreach ($tags as $tag): ?><span><?= e($tag); ?></span><?php endforeach; ?></div><?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
