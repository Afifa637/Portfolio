<?php
if (!isset($is_included)) {
    $page_title = "About | Afifa Sultana";
}

// Database connection
$conn = new mysqli("localhost", "root", "", "portfolio_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch about data (only 1 row needed)
$sql = "SELECT * FROM about LIMIT 1";
$result = $conn->query($sql);
$about = $result->fetch_assoc();
?>

<!-- About Section -->
<section class="about section" id="about">
  <div class="about-container container">
    
    <!-- Section Title -->
    <h2 class="section-title">About <span>Me</span></h2>

    <div class="about-content-wrapper">
      <!-- Profile Image -->
      <div class="about-img">
        <img src="<?= $about['profile_image']; ?>" alt="Afifa Sultana Profile" class="about-profile" />
      </div>

      <!-- Content -->
      <div class="about-content">
        <p><?= $about['short_intro']; ?></p>

        <!-- Read More Content -->
        <div class="read-more-content" id="moreText">
          <?= $about['long_intro']; ?>
        </div>

        <!-- Read More Button -->
        <button class="btn" id="readMoreBtn" onclick="toggleReadMore()">Read More</button>
      </div>
    </div>

    <!-- Decorative Shapes -->
    <div class="section-deco deco-right">
      <img src="assets/images/deco2.png" alt="" class="shape" />
    </div>
  </div>
</section>
