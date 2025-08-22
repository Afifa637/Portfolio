<?php
if (!isset($is_included)) {
    $page_title = "Projects | Afifa Sultana";
}

// Database connection
$conn = new mysqli("localhost", "root", "", "portfolio_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch projects
$sql = "SELECT * FROM projects ORDER BY order_no ASC";
$result = $conn->query($sql);
?>

<!-- Project Section -->
<section class="project section" id="project">
    <h2 class="heading section-title">My <span>Projects</span></h2>

    <!-- Filters -->
    <div class="project-filters">
        <span class="project-item btn" data-filter="*">All</span>
        <span class="project-item btn" data-filter=".web">Web</span>
        <span class="project-item btn" data-filter=".app">App</span>
        <span class="project-item btn" data-filter=".design">Design</span>
    </div>

    <!-- Project Cards -->
    <div class="project-container container grid">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="project-card mix <?= htmlspecialchars($row['category']); ?>">
                <img src="<?= htmlspecialchars($row['image']); ?>" alt="<?= htmlspecialchars($row['title']); ?>" class="project-img">
                <h3 class="project-title"><?= htmlspecialchars($row['title']); ?></h3>

                <span class="btn project-button" data-id="<?= $row['id']; ?>">Demo
                    <i class="uil uil-arrow-right project-button-icon"></i>
                </span>

                <!-- Hidden project details -->
                <div class="portfolio-item-details" style="display: none;">
                    <h3 class="details-title"><?= htmlspecialchars($row['details_title']); ?></h3>
                    <p class="details-description"><?= htmlspecialchars($row['description']); ?></p>
                    <ul class="details-info">
                        <li>Created - <span><?= htmlspecialchars($row['created_at']); ?></span></li>
                        <li>Skills - <span><?= htmlspecialchars($row['skills_used']); ?></span></li>
                        <li>Role - <span><?= htmlspecialchars($row['role']); ?></span></li>
                        <li>View - <span><a href="<?= htmlspecialchars($row['view_link']); ?>" target="_blank"><?= htmlspecialchars($row['view_link']); ?></a></span></li>
                    </ul>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Decorative Shape -->
    <div class="section-deco deco-left">
        <img src="assets/images/deco1.png" alt="" class="shape" />
    </div>
</section>

<!-- Project Popup -->
<div class="project-popup">
    <div class="project-popup-inner">
        <div class="project-popup-content grid">
            <span class="project-popup-close"><i class="fa-solid fa-xmark"></i></span>
            <div class="pp-thumbnail">
                <img src="" alt="" class="project-popup-img">
            </div>
            <div class="project-popup-info">
                <div class="project-popup-subtitle">Featured - <span id="popup-category"></span></div>
                <div class="project-popup-body">
                    <h3 class="details-title"></h3>
                    <p class="details-description"></p>
                    <ul class="details-info"></ul>
                </div>
            </div>
        </div>
    </div>
</div>
