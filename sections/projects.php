<?php
if (!isset($is_included)) {
    $page_title = "Projects | Afifa Sultana";
}

if (!isset($conn)) {
    require_once __DIR__ . '/../includes/bootstrap.php';
}

$sql = "SELECT * FROM projects ORDER BY order_no ASC";
$result = $conn->query($sql);
?>

<section class="project section" id="project">
    <div class="container">
        <h2 class="heading section-title">My <span>Projects</span></h2>

        <div class="project-controls">
            <div class="project-filters">
                <button class="project-item btn" data-filter="all" data-mixitup-control type="button">All</button>
                <button class="project-item btn" data-filter=".web" data-mixitup-control type="button">Web</button>
                <button class="project-item btn" data-filter=".app" data-mixitup-control type="button">App</button>
                <button class="project-item btn" data-filter=".terminal" data-mixitup-control type="button">Terminal</button>
            </div>

            <div class="project-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="project-search" placeholder="Search projects, tech, role..." aria-label="Search projects">
            </div>
        </div>

        <div class="project-container grid">
            <?php if ($result): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $cat = strtolower($row['category'] ?? 'web');
                    $skillsUsed = $row['skills_used'] ?? '';
                    $tags = array_filter(array_map('trim', explode(',', $skillsUsed)));
                    $isFeatured = !empty($row['featured'] ?? '');
                    ?>
                    <div class="project-card mix <?= e($cat); ?> <?= $isFeatured ? 'featured' : ''; ?>" data-title="<?= e($row['title']); ?>" data-tech="<?= e($skillsUsed); ?>" data-role="<?= e($row['role']); ?>">
                        <div class="project-media">
                            <img src="<?= e($row['image']); ?>" alt="<?= e($row['title']); ?>" class="project-img" loading="lazy">
                            <span class="project-badge"><?= e(ucfirst($cat)); ?></span>
                        </div>
                        <h3 class="project-title"><?= e($row['title']); ?></h3>

                        <div class="project-tags">
                            <?php foreach ($tags as $tag): ?>
                                <span><?= e($tag); ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="project-actions">
                            <button type="button" class="btn project-button" data-id="<?= (int)$row['id']; ?>">
                                View Details <i class="fa-solid fa-arrow-right"></i>
                            </button>
                            <?php if (!empty($row['view_link'])): ?>
                                <a class="btn outline-btn" href="<?= e($row['view_link']); ?>" target="_blank" rel="noopener">Live Preview</a>
                            <?php endif; ?>
                        </div>

                        <div class="portfolio-item-details" style="display: none;">
                            <h3 class="details-title"><?= e($row['details_title']); ?></h3>
                            <p class="details-description"><?= e($row['description']); ?></p>
                            <ul class="details-info">
                                <li>Created - <span><?= e($row['created_at']); ?></span></li>
                                <li>Skills - <span><?= e($row['skills_used']); ?></span></li>
                                <li>Role - <span><?= e($row['role']); ?></span></li>
                                <li>View - <span><a href="<?= e($row['view_link']); ?>" target="_blank" rel="noopener"><?= e($row['view_link']); ?></a></span></li>
                            </ul>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

            <div class="section-deco deco-left">
                <img src="assets/images/deco1.png" alt="" class="shape" />
            </div>
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