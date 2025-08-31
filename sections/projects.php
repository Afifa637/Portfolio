<?php
if (!isset($is_included)) {
    $page_title = "Projects | Afifa Sultana";
}

$conn = new mysqli("localhost", "root", "", "portfolio_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM projects ORDER BY order_no ASC";
$result = $conn->query($sql);
?>

<section class="project section" id="project">
    <h2 class="heading section-title">My <span>Projects</span></h2>

    <div class="project-filters">
        <span class="project-item btn" data-filter="all" data-mixitup-control>All</span>
        <span class="project-item btn" data-filter=".web" data-mixitup-control>Web</span>
        <span class="project-item btn" data-filter=".app" data-mixitup-control>App</span>
        <span class="project-item btn" data-filter=".terminal" data-mixitup-control>Terminal</span>
    </div>

    <div class="project-container container grid">
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php $cat = strtolower($row['category']); // ensure class is lower-case ?>
            <div class="project-card mix <?= htmlspecialchars($cat); ?>">
                <img src="<?= htmlspecialchars($row['image']); ?>" alt="<?= htmlspecialchars($row['title']); ?>" class="project-img">
                <h3 class="project-title"><?= htmlspecialchars($row['title']); ?></h3>

                <button type="button" class="btn project-button" data-id="<?= (int)$row['id']; ?>">
                    Demo <i class="uil uil-arrow-right project-button-icon"></i>
                </button>

                <div class="portfolio-item-details" style="display: none;">
                    <h3 class="details-title"><?= htmlspecialchars($row['details_title']); ?></h3>
                    <p class="details-description"><?= htmlspecialchars($row['description']); ?></p>
                    <ul class="details-info">
                        <li>Created - <span><?= htmlspecialchars($row['created_at']); ?></span></li>
                        <li>Skills - <span><?= htmlspecialchars($row['skills_used']); ?></span></li>
                        <li>Role - <span><?= htmlspecialchars($row['role']); ?></span></li>
                        <li>View - <span><a href="<?= htmlspecialchars($row['view_link']); ?>" target="_blank" rel="noopener"><?= htmlspecialchars($row['view_link']); ?></a></span></li>
                    </ul>
                </div>
            </div>
        <?php endwhile; ?>

        <div class="section-deco deco-left">
            <img src="assets/images/deco1.png" alt="" class="shape" />
        </div>
    </div>
</section>

<!-- Project Popup HTML -->
<div class="project-popup" aria-hidden="true">
  <div class="project-popup-inner">
    <div class="project-popup-content">
      <button type="button" class="project-popup-close" aria-label="Close popup">
        <i class="fa-solid fa-xmark"></i>
      </button>

      <div class="pp-left">
        <img src="" alt="" class="project-popup-img">
      </div>

      <div class="pp-right">
        <h3 class="popup-title"></h3>
        <div class="popup-category">Category: <span id="popup-category"></span></div>
        <div class="popup-description"></div>

        <ul class="popup-info"></ul>

        <div class="popup-links">
          <a href="#" target="_blank" class="btn view-link"><i class="fa-solid fa-link"></i> View Project</a>
        </div>
      </div>
    </div>
  </div>
</div>
