<?php
if (!isset($conn)) {
  require_once __DIR__ . '/../includes/bootstrap.php';
}

$sql = "SELECT * FROM contact_info ORDER BY id ASC";
$result = $conn->query($sql);

$cards = $radios = $checkboxes = $terms = [];
if ($result) {
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
}

$successMsg = $_SESSION['contact_success'] ?? '';
$errorMsg = $_SESSION['contact_error'] ?? '';
unset($_SESSION['contact_success'], $_SESSION['contact_error']);
?>
<section class="contact section" id="contact">
  <h3 class="section-title"><i class="fa-regular fa-address-book"></i> Contact <span>Me</span></h3>

  <div class="contact-container container grid">
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

      <div class="contact-side-actions">
        <button type="button" class="btn ghost-btn" id="copy-email" data-email="<?= e($cards[0]['data'] ?? ''); ?>">
          <i class="fa-regular fa-copy"></i> Copy Email
        </button>
        <a href="#project" class="btn outline-btn"><i class="fa-solid fa-folder-open"></i> View Projects</a>
      </div>
    </div>

    <form action="" method="POST" class="contact-form grid" id="contact-form">

      <?php if (!empty($errorMsg)): ?>
        <p class="form-message error-msg" role="alert"><?= e($errorMsg); ?></p>
      <?php endif; ?>

      <?php if (!empty($successMsg)): ?>
        <p class="form-message success-msg" role="status"><?= e($successMsg); ?></p>
      <?php endif; ?>

      <?= csrf_field(); ?>
      <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">

      <div class="contact-form-group grid">
        <div class="contact-form-div">
          <label class="contact-form-label" for="contact-name">Your Name<b>*</b></label>
          <div class="input-icon">
            <i class="fa-solid fa-user"></i>
            <input type="text" class="contact-form-input" id="contact-name" name="name" required>
          </div>
        </div>
        <div class="contact-form-div">
          <label class="contact-form-label" for="contact-email">Your Email<b>*</b></label>
          <div class="input-icon">
            <i class="fa-solid fa-envelope"></i>
            <input type="email" class="contact-form-input" id="contact-email" name="email" required>
          </div>
        </div>
      </div>

      <div class="contact-form-div">
        <label class="contact-form-label" for="contact-subject">Your Subject<b>*</b></label>
        <div class="input-icon">
          <i class="fa-solid fa-pen"></i>
          <input type="text" class="contact-form-input" id="contact-subject" name="subject" required>
        </div>
      </div>

      <div class="contact-form-div">
        <label class="contact-form-label" for="contact-message">Your Message<b>*</b></label>
        <div class="input-icon textarea-icon">
          <i class="fa-solid fa-message"></i>
          <textarea class="contact-form-input contact-form-area" id="contact-message" name="message" required></textarea>
        </div>
      </div>

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

      <?php if (!empty($terms) && !isset($_COOKIE['terms_accepted'])): ?>
        <div class="contact-form-div">
          <?php foreach ($terms as $term): ?>
            <label>
              <input type="checkbox" id="terms" onchange="acceptTerms()" required>
              <?= htmlspecialchars($term['label']); ?>
            </label>
            <p class="terms-text"><?= htmlspecialchars($term['description']); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="contact-submit">
        <span>* Please fill all required fields.</span>
        <button type="submit" class="btn" id="contact-submit">
          <span class="btn-text"><i class="fa-solid fa-paper-plane"></i> Send Message</span>
          <span class="btn-loader" aria-hidden="true"></span>
        </button>
      </div>
    </form>

    <div class="section-deco deco-right">
      <img src="assets/images/deco2.png" alt="" class="shape">
    </div>
  </div>
</section>