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
        <span class="terminal-kicker"><i class="fa-solid fa-layer-group"></i> const projects = [...]</span>
        <h2 class="heading section-title">My <span>Projects</span></h2>
        <p class="projects-subtitle">A case-study style showcase with richer detail panels, motion, tech stacks, and live links.</p>

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
                    <div class="project-card mix <?= e($cat); ?> <?= $isFeatured ? 'featured' : ''; ?> scroll-morph" data-title="<?= e($row['title']); ?>" data-tech="<?= e($skillsUsed); ?>" data-role="<?= e($row['role']); ?>" data-category="<?= e(ucfirst($cat)); ?>" data-link="<?= e($row['view_link'] ?? ''); ?>">
                        <div class="project-media">
                            <img src="<?= e($row['image']); ?>" alt="<?= e($row['title']); ?>" class="project-img" loading="lazy">
                            <span class="project-badge"><?= e(ucfirst($cat)); ?></span>
                        </div>
                        <div class="project-card-body">
                            <div class="project-card-meta">
                                <span><i class="fa-solid fa-folder-open"></i> <?= e(ucfirst($cat)); ?></span>
                                <?php if (!empty($row['created_at'])): ?><span><i class="fa-regular fa-calendar"></i> <?= e($row['created_at']); ?></span><?php endif; ?>
                            </div>
                            <h3 class="project-title"><?= e($row['title']); ?></h3>
                            <?php if (!empty($row['description'])): ?>
                              <p class="project-excerpt"><?= e(strlen(strip_tags($row['description'])) > 150 ? substr(strip_tags($row['description']), 0, 150) . '...' : strip_tags($row['description'])); ?></p>
                            <?php endif; ?>
                        </div>

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
                            <h3 class="details-title"><?= e($row['details_title'] ?: $row['title']); ?></h3>
                            <p class="details-description"><?= e($row['description']); ?></p>
                            <span class="details-category"><?= e(ucfirst($cat)); ?></span>
                            <span class="details-image"><?= e($row['image']); ?></span>
                            <ul class="details-info">
                                <li>
                                    <span class="info-label">Created</span>
                                    <span class="info-value"><?= e($row['created_at']); ?></span>
                                </li>
                                <li>
                                    <span class="info-label">Skills</span>
                                    <span class="info-value"><?= e($row['skills_used']); ?></span>
                                </li>
                                <li>
                                    <span class="info-label">Role</span>
                                    <span class="info-value"><?= e($row['role']); ?></span>
                                </li>
                                <?php if (!empty($row['view_link'])): ?>
                                    <li class="info-link">
                                        <span class="info-label">View</span>
                                        <span class="info-value"><a href="<?= e($row['view_link']); ?>" target="_blank" rel="noopener">Open Project</a></span>
                                    </li>
                                <?php endif; ?>
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
<div class="project-popup" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Project details">
    <div class="project-popup-inner">
        <div class="project-popup-content case-study-modal">
            <button type="button" class="project-popup-close" aria-label="Close popup">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="case-study-topbar">
                <span></span><span></span><span></span>
                <strong>project.case-study</strong>
            </div>

            <div class="pp-left case-study-visual">
                <div class="case-study-image-frame">
                    <img src="" alt="" class="project-popup-img">
                </div>
                <div class="case-study-stats">
                    <div><small>Category</small><strong id="popup-category"></strong></div>
                    <div><small>Mode</small><strong>Dynamic</strong></div>
                    <div><small>Status</small><strong>Featured</strong></div>
                </div>
            </div>

            <div class="pp-right case-study-info">
                <span class="popup-kicker"><i class="fa-solid fa-diagram-project"></i> View Details</span>
                <h3 class="popup-title"></h3>
                <div class="popup-tags" id="popup-tags"></div>

                <div class="popup-description-wrap">
                    <h4>Overview</h4>
                    <div class="popup-description"></div>
                </div>

                <div class="popup-info-wrap">
                    <h4>Project Metadata</h4>
                    <ul class="popup-info"></ul>
                </div>

                <div class="popup-links">
                    <a href="#" target="_blank" rel="noopener" class="btn view-link"><i class="fa-solid fa-arrow-up-right-from-square"></i> Live / Source Link</a>
                </div>
            </div>
        </div>
    </div>
</div>