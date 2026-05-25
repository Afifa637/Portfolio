<?php
if (!isset($is_included)) {
  $page_title = "Education | Afifa Sultana";
}

if (!isset($conn)) {
  require_once __DIR__ . '/../includes/bootstrap.php';
}

$sql = "SELECT * FROM education ORDER BY start_year DESC";
$result = $conn->query($sql);
?>

<section class="education section" id="education">
  <div class="container">
    <h2 class="section-title" data-title="Academic Journey">
      <i class="fa-solid fa-graduation-cap"></i> My <span>Education</span>
    </h2>

    <div class="timeline">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content glass-card">
              <div class="timeline-header">
                <h3 class="edu-degree"><?php echo e($row['degree']); ?></h3>
                <span class="edu-date"><i class="fa-solid fa-calendar-days"></i>
                  <?php echo e($row['start_year']); ?> – <?php echo e($row['end_year']); ?>
                </span>
              </div>

              <?php if (!empty($row['major'])): ?>
                <h4 class="edu-major"><?php echo e($row['major']); ?></h4>
              <?php endif; ?>

              <p class="edu-institute"><i class="fa-solid fa-building-columns"></i>
                <?php echo e($row['institution']); ?>
              </p>

              <div class="edu-meta">
                <?php if (!empty($row['location'])): ?>
                  <p class="edu-location"><i class="fa-solid fa-location-dot"></i>
                    <?php echo e($row['location']); ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($row['grade'])): ?>
                  <p class="edu-grade"><i class="fa-solid fa-star"></i>
                    <?php echo e($row['grade']); ?>
                  </p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty-state">No education records found.</div>
      <?php endif; ?>

      <div class="section-deco deco-left">
        <img src="assets/images/deco1.png" alt="" class="shape" />
      </div>
    </div>
  </div>
</section>