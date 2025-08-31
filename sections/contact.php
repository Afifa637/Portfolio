<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

$mail = new PHPMailer(true);

try {
  $mail->SMTPDebug = 2;
  $mail->Debugoutput = 'html';
  $mail->isSMTP();
  $mail->Host       = $_ENV['MAIL_HOST'];
  $mail->SMTPAuth   = true;
  $mail->Username   = $_ENV['MAIL_USERNAME'];
  $mail->Password   = $_ENV['MAIL_PASSWORD'];
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port       = $_ENV['MAIL_PORT'];

  $mail->setFrom($_ENV['MAIL_USERNAME'], $_ENV['MAIL_FROM_NAME']);
  $mail->addAddress($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);

  $mail->isHTML(true);
  $mail->Subject = "Test Mail";
  $mail->Body    = "<b>This is a test email</b>";
  $mail->AltBody = "This is a test email";

  echo "📤 Trying to send...<br>";
  $mail->send();
  echo "✅ Mail sent!";
} catch (Exception $e) {
  echo "⚠️ Mailer Error: {$mail->ErrorInfo}";
}

// Fetch contact info fields
$sql = "SELECT * FROM contact_info ORDER BY id ASC";
$result = $conn->query($sql);

$cards = $radios = $checkboxes = $terms = [];
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
    </div>

    <form action="" method="POST" class="contact-form grid" onsubmit="return validateForm();">

      <?php if (!empty($_SESSION['error'])): ?>
        <p class="error-msg"><?= $_SESSION['error'];
                              unset($_SESSION['error']); ?></p>
      <?php endif; ?>

      <?php if (!empty($_SESSION['success'])): ?>
        <p class="success-msg"><?= $_SESSION['success'];
                                unset($_SESSION['success']); ?></p>
      <?php endif; ?>

      <div class="contact-form-group grid">
        <div class="contact-form-div">
          <label class="contact-form-label">Your Name<b>*</b></label>
          <input type="text" class="contact-form-input" name="name"
            value="<?= $_SESSION['form_data']['name'] ?? '' ?>" required>
        </div>
        <div class="contact-form-div">
          <label class="contact-form-label">Your Email<b>*</b></label>
          <input type="email" class="contact-form-input" name="email"
            value="<?= $_SESSION['form_data']['email'] ?? '' ?>" required>
        </div>
      </div>

      <div class="contact-form-div">
        <label class="contact-form-label">Your Subject<b>*</b></label>
        <input type="text" class="contact-form-input" name="subject"
          value="<?= $_SESSION['form_data']['subject'] ?? '' ?>" required>
      </div>

      <div class="contact-form-div">
        <label class="contact-form-label">Your Message<b>*</b></label>
        <textarea class="contact-form-input contact-form-area" name="message" required><?= $_SESSION['form_data']['message'] ?? '' ?></textarea>
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
        <button type="submit" class="btn"><i class="fa-solid fa-paper-plane"></i> Send Message</button>
      </div>
    </form>

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

  function acceptTerms() {
    if (document.getElementById("terms").checked) {
      document.cookie = "terms_accepted=true; path=/; max-age=" + 60 * 60 * 24 * 365; // 1 year
    }
  }
</script>