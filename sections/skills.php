<?php
if (!isset($is_included)) {
  $page_title = "Skills | Afifa Sultana";
}

if (!isset($conn)) {
  require_once __DIR__ . '/../includes/bootstrap.php';
}

$sql = "SELECT * FROM skills ORDER BY order_no ASC";
$result = $conn->query($sql);

if (!function_exists('skill_category')) {
  function skill_category(string $name): string
  {
    $n = strtolower($name);
    if (preg_match('/html|css|javascript|react|vue|angular|frontend|tailwind|bootstrap/', $n)) return 'frontend';
    if (preg_match('/php|node|laravel|django|backend|api|express|spring/', $n)) return 'backend';
    if (preg_match('/mysql|postgres|mongodb|database|sql/', $n)) return 'database';
    if (preg_match('/figma|ui|ux|design|illustrator|photoshop/', $n)) return 'design';
    if (preg_match('/git|docker|devops|aws|linux|tools/', $n)) return 'tools';
    return 'general';
  }
}
?>

<section class="skills section" id="skills">
  <div class="container">
    <h2 class="heading section-title" data-title="My Skills">Professional Skills</h2>

    <div class="skills-tabs" role="tablist">
      <button class="tab-btn active" data-filter="all" type="button" role="tab" aria-selected="true">All</button>
      <button class="tab-btn" data-filter="frontend" type="button" role="tab">Frontend</button>
      <button class="tab-btn" data-filter="backend" type="button" role="tab">Backend</button>
      <button class="tab-btn" data-filter="database" type="button" role="tab">Database</button>
      <button class="tab-btn" data-filter="tools" type="button" role="tab">Tools</button>
      <button class="tab-btn" data-filter="design" type="button" role="tab">Design</button>
    </div>

    <div class="skills-container grid">
      <?php if ($result): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php $category = skill_category($row['skill_name'] ?? ''); ?>
          <div class="skills-item" data-category="<?= e($category); ?>">
            <div class="skills-header">
              <h3 class="skills-name"><?= e($row['skill_name']); ?></h3>
              <span class="skills-value"><?= e($row['percentage']); ?> <b>%</b></span>
            </div>

            <p class="skills-description">
              <?= e($row['description']); ?>
            </p>

            <div class="skills-bar">
              <span class="skills-percentage" style="width: <?= e($row['percentage']); ?>%;"></span>
            </div>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>

      <div class="section-deco deco-right">
        <img src="assets/images/deco2.png" alt="" class="shape" />
      </div>
    </div>
  </div>
</section>