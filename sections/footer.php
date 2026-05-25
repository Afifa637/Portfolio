<?php
if (!isset($is_included)) {
  $page_title = "Footer | Afifa Sultana";
}

if (!isset($conn)) {
  require_once __DIR__ . '/../includes/bootstrap.php';
}

$sql = "SELECT * FROM footer";
$result = $conn->query($sql);
?>

</main>
<footer class="footer">
  <div class="footer-accent" aria-hidden="true"></div>
  <div class="footer-container container">

    <div class="footer-brand">
      <h3>Afifa Sultana</h3>
      <p>Building premium, modern digital experiences.</p>
    </div>

    <div class="footer-links">
      <h4>Quick Links</h4>
      <a href="#home">Home</a>
      <a href="#about">About</a>
      <a href="#project">Projects</a>
      <a href="#contact">Contact</a>
    </div>

    <div class="footer-socials" aria-label="Footer social links">
      <?php if ($result): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php if (!empty($row['social_link'])): ?>
            <a href="<?php echo e($row['social_link']); ?>" target="_blank" rel="noopener" aria-label="Social link">
              <i class="<?php echo e($row['social_icon']); ?>"></i>
            </a>
          <?php endif; ?>
        <?php endwhile; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="footer-text">
    <?php
    $textResult = $conn->query("SELECT footer_text FROM footer WHERE footer_text <> '' LIMIT 1");
    if ($textResult && $textResult->num_rows > 0) {
      $footerRow = $textResult->fetch_assoc();
      echo "<p>" . e($footerRow['footer_text']) . "</p>";
    } else {
      echo "<p>© " . date('Y') . " Afifa Sultana. All rights reserved.</p>";
    }
    ?>
  </div>
</footer>

<button class="back-to-top" id="back-to-top" aria-label="Back to top" type="button">
  <i class="fa-solid fa-arrow-up"></i>
</button>

<a href="#contact" class="floating-contact" aria-label="Contact me">
  <i class="fa-solid fa-paper-plane"></i>
</a>

<script src="https://unpkg.com/scrollreveal"></script>

<script src="https://cdn.jsdelivr.net/npm/mixitup@3"></script>
<script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>
<script src="main.js"></script>
</body>

</html>