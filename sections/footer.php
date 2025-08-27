<?php
if (!isset($is_included)) {
    $page_title = "Footer | Afifa Sultana";
}

// DB connection
$conn = new mysqli("localhost", "root", "", "portfolio_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch footer data
$sql = "SELECT * FROM footer";
$result = $conn->query($sql);
?>

</main>
<footer class="footer">
  <div class="footer-container container">

    <!-- Social Links -->
    <div class="footer-socials">
      <?php while ($row = $result->fetch_assoc()): ?>
        <?php if (!empty($row['social_link'])): ?>
          <a href="<?php echo $row['social_link']; ?>" target="_blank">
            <i class="<?php echo $row['social_icon']; ?>"></i>
          </a>
        <?php endif; ?>
      <?php endwhile; ?>
    </div>

    <!-- Footer Text -->
    <div class="footer-text">
      <?php
        // Get first row with footer_text
        $textResult = $conn->query("SELECT footer_text FROM footer WHERE footer_text <> '' LIMIT 1");
        if ($textResult->num_rows > 0) {
            $footerRow = $textResult->fetch_assoc();
            echo "<p>" . $footerRow['footer_text'] . "</p>";
        }
      ?>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/mixitup@3"></script>
<script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>
<script src="main.js"></script>
</body>
</html>
