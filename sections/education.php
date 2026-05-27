<?php
if (!isset($is_included)) {
  $page_title = "Education | Afifa Sultana";
}

if (!isset($conn)) {
  require_once __DIR__ . '/../includes/bootstrap.php';
}

$sql = "SELECT * FROM education ORDER BY start_year DESC";
$result = $conn->query($sql);
$educationRows = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $educationRows[] = $row;
  }
}
$totalEducation = count($educationRows);
?>

<section class="education section" id="education">
  <div class="container">
    <span class="terminal-kicker"><i class="fa-solid fa-route"></i> ./academic-roadmap</span>
    <h2 class="section-title" data-title="Academic Journey">
      <i class="fa-solid fa-graduation-cap"></i> My <span>Education</span>
    </h2>
    <p class="roadmap-subtitle">A milestone-style learning path showing the academic foundation behind my technical growth.</p>

    <?php if ($educationRows): ?>
      <div class="education-roadmap" aria-label="Education roadmap">
        <div class="roadmap-spine" aria-hidden="true"><span></span></div>
        <?php foreach ($educationRows as $index => $row): ?>
          <?php
            $side = $index % 2 === 0 ? 'roadmap-left' : 'roadmap-right';
            $stepNumber = str_pad((string)($totalEducation - $index), 2, '0', STR_PAD_LEFT);
          ?>
          <article class="roadmap-item <?php echo e($side); ?> scroll-morph" data-roadmap-step="<?php echo e($stepNumber); ?>">
            <div class="roadmap-year">
              <span><?php echo e($row['start_year']); ?></span>
              <small><?php echo !empty($row['end_year']) ? e($row['end_year']) : 'Now'; ?></small>
            </div>
            <div class="roadmap-node" aria-hidden="true">
              <span class="node-pulse"></span>
              <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div class="roadmap-card glass-card">
              <div class="roadmap-card-top">
                <span class="roadmap-step">Checkpoint <?php echo e($stepNumber); ?></span>
                <span class="roadmap-status"><i class="fa-solid fa-circle-check"></i> Completed</span>
              </div>

              <h3 class="edu-degree"><?php echo e($row['degree']); ?></h3>

              <?php if (!empty($row['major'])): ?>
                <h4 class="edu-major"><?php echo e($row['major']); ?></h4>
              <?php endif; ?>

              <p class="edu-institute"><i class="fa-solid fa-building-columns"></i>
                <?php echo e($row['institution']); ?>
              </p>

              <div class="edu-meta roadmap-meta">
                <span><i class="fa-solid fa-calendar-days"></i> <?php echo e($row['start_year']); ?> – <?php echo e($row['end_year']); ?></span>
                <?php if (!empty($row['location'])): ?>
                  <span><i class="fa-solid fa-location-dot"></i> <?php echo e($row['location']); ?></span>
                <?php endif; ?>
                <?php if (!empty($row['grade'])): ?>
                  <span><i class="fa-solid fa-star"></i> <?php echo e($row['grade']); ?></span>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state glass-card">No education records found.</div>
    <?php endif; ?>

    <div class="section-deco deco-left" aria-hidden="true">
      <img src="assets/images/deco1.png" alt="" class="shape" />
    </div>
  </div>
</section>
