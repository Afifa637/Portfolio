<?php
if (!isset($is_included)) {
    $page_title = "Skills | Afifa Sultana";
}

$conn = new mysqli("localhost", "root", "", "portfolio_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM skills ORDER BY order_no ASC";
$result = $conn->query($sql);
?>

<section class="skills section" id="skills">
  <h2 class="heading section-title" data-title="My Skills">Professional Skills</h2>
  <div class="skills-container container grid">

    <?php while($row = $result->fetch_assoc()): ?>
      <div class="skills-item">
        <div class="skills-header">
          <h3 class="skills-name"><?= htmlspecialchars($row['skill_name']); ?></h3>
          <span class="skills-value"><?= $row['percentage']; ?> <b>%</b></span>
        </div>

        <p class="skills-description">
          <?= htmlspecialchars($row['description']); ?>
        </p>

        <div class="skills-bar">
          <span class="skills-percentage" style="width: <?= $row['percentage']; ?>%;"></span>
        </div>
      </div>
    <?php endwhile; ?>

    <div class="section-deco deco-right">
      <img src="assets/images/deco2.png" alt="" class="shape" />
    </div>
  </div>
</section>
