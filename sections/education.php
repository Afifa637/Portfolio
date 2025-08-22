<?php
if (!isset($is_included)) {
  $page_title = "Education | Afifa Sultana";
}

$conn = new mysqli("localhost", "root", "", "portfolio_db");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch education data
$sql = "SELECT * FROM education ORDER BY start_year DESC";
$result = $conn->query($sql);
?>

<!-- Education Section -->
<section class="education section" id="education">
  <h2 class="section-title">
    <i class="fa-solid fa-graduation-cap"></i> My <span>Education</span>
  </h2>

  <div class="education-container">
  <div class="timeline">
    <?php if ($result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="timeline-item">
          <div class="timeline-dot"></div>
          <div class="timeline-content glass-card">
            <h3 class="edu-degree"><?php echo htmlspecialchars($row['degree']); ?></h3>

            <?php if (!empty($row['major'])): ?>
              <h4 class="edu-major"><?php echo htmlspecialchars($row['major']); ?></h4>
            <?php endif; ?>

            <p class="edu-institute"><i class="fa-solid fa-building-columns"></i>
              <?php echo htmlspecialchars($row['institution']); ?>
            </p>

            <?php if (!empty($row['location'])): ?>
              <p class="edu-location"><i class="fa-solid fa-location-dot"></i>
                <?php echo htmlspecialchars($row['location']); ?>
              </p>
            <?php endif; ?>

            <?php if (!empty($row['grade'])): ?>
              <p class="edu-grade"><i class="fa-solid fa-star"></i>
                <?php echo htmlspecialchars($row['grade']); ?>
              </p>
            <?php endif; ?>

            <p class="edu-date"><i class="fa-solid fa-calendar-days"></i>
              <?php echo htmlspecialchars($row['start_year']); ?> – <?php echo htmlspecialchars($row['end_year']); ?>
            </p>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No education records found.</p>
    <?php endif; ?>
    <!-- Decorative Shape -->
    <div class="section-deco deco-left">
      <img src="assets/images/deco1.png" alt="" class="shape" />
    </div>
  </div>
</section>

<?php $conn->close(); ?>