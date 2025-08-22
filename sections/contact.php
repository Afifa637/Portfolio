<?php
if (!isset($is_included)) {
  $page_title = "Contact | Afifa Sultana";
}

// Database connection
$conn = new mysqli("localhost", "root", "", "portfolio_db");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch all contact info
$sql = "SELECT * FROM contact_info ORDER BY id ASC";
$result = $conn->query($sql);

// Group results by type
$cards = [];
$radios = [];
$checkboxes = [];
$terms = [];

while ($row = $result->fetch_assoc()) {
  switch ($row['input_type']) {
    case 'card':
      $cards[] = $row;
      break;
    case 'radio':
      $radios[] = $row;
      break;
    case 'checkbox':
      $checkboxes[] = $row;
      break;
    case 'terms':
      $terms[] = $row;
      break;
  }
}
?>

<!-- Contact Section -->
<section class="contact section" id="contact">
  <h3 class="section-title">
    <i class="fa-regular fa-address-book"></i> Contact <span>Me</span>
  </h3>

  <div class="contact-container container grid">
    <!-- Contact Info Cards -->
    <div class="contact-content">
      <?php foreach ($cards as $card): ?>
        <div class="contact-card">
          <span class="contact-icon"><i class="<?= htmlspecialchars($card['icon']); ?>"></i></span>
          <div class="contact-info">
            <h3 class="contact-title"><?= htmlspecialchars($card['type']); ?></h3>
            <p class="contact-data"><?= htmlspecialchars($card['data']); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Contact Form -->
    <form action="#" method="POST" class="contact-form grid" onsubmit="return validateForm();">
      <div class="contact-form-group grid">
        <div class="contact-form-div">
          <label class="contact-form-label">Your Name<b>*</b></label>
          <input type="text" class="contact-form-input" name="name" placeholder="Enter your name" required>
        </div>

        <div class="contact-form-div">
          <label class="contact-form-label">Your Email<b>*</b></label>
          <input type="email" class="contact-form-input" name="email" placeholder="Enter your email" required>
        </div>
      </div>

      <div class="contact-form-div">
        <label class="contact-form-label">Your Subject<b>*</b></label>
        <input type="text" class="contact-form-input" name="subject" placeholder="Enter your subject" required>
      </div>

      <div class="contact-form-div">
        <label class="contact-form-label">Your Message<b>*</b></label>
        <textarea class="contact-form-input contact-form-area" name="message" placeholder="Enter your message" required></textarea>
      </div>

      <!-- Radio Buttons -->
      <?php if (!empty($radios)): ?>
        <div class="contact-form-div radio-group">
          <label class="contact-form-label">Purpose<b>*</b>:</label>
          <?php foreach ($radios as $radio): ?>
            <label>
              <input type="radio" name="purpose" value="<?= htmlspecialchars($radio['value']); ?>" required>
              <?= htmlspecialchars($radio['label']); ?>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      
      <!-- Checkboxes -->
      <?php if (!empty($checkboxes)): ?>
        <div class="contact-form-div">
          <?php foreach ($checkboxes as $cb): ?>
            <label>
              <input type="checkbox" name="options[]" value="<?= htmlspecialchars($cb['value']); ?>">
              <?= htmlspecialchars($cb['label']); ?>
            </label><br>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Terms & Conditions -->
      <?php if (!empty($terms)): ?>
        <div class="contact-form-div">
          <?php foreach ($terms as $term): ?>
            <label>
              <input type="checkbox" id="terms" required>
              <?= htmlspecialchars($term['label']); ?>
            </label>
            <p class="terms-text"><?= htmlspecialchars($term['description']); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Submit -->
      <div class="contact-submit">
        <span>* Please fill all required fields.</span>
        <button type="submit" class="btn">
          <i class="fa-solid fa-paper-plane"></i> Send Message
        </button>
      </div>

      <p class="message" id="message"></p>
    </form>

    <!-- Decorative Shape -->
    <div class="section-deco deco-right">
      <img src="assets/images/deco2.png" alt="" class="shape">
    </div>
  </div>
</section>

<script>
  function validateForm() {
    const terms = document.getElementById("terms");
    if (terms && !terms.checked) {
      alert("You must agree to the Terms & Conditions before submitting.");
      return false;
    }
    return true;
  }
</script>