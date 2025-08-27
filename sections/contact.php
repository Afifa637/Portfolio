<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Page title (if not included elsewhere)
if (!isset($is_included)) {
    $page_title = "Contact | Afifa Sultana";
}

// DB connection
$conn = new mysqli("localhost", "root", "", "portfolio_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Load PHPMailer
require __DIR__ . '/../vendor/autoload.php'; 

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $_SESSION['form_data'] = $_POST;

    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['subject']) || empty($_POST['message'])) {
        $_SESSION['error'] = "⚠️ Please fill in all required fields.";
        header("Location: contact.php#contact");
        exit;
    }

    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $subject, $message);
    $stmt->execute();
    $stmt->close();

    // ===== SEND EMAILS WITH PHPMailer =====
    $mail = new PHPMailer(true);
    try {
        // SMTP settings (example: Gmail SMTP)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com'; // your Gmail
        $mail->Password   = 'your-app-password';    // Gmail app password, NOT your normal pass
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // 1. Send to YOU
        $mail->setFrom($email, $name);
        $mail->addAddress('your-email@gmail.com', 'Afifa Sultana'); // replace with your real email
        $mail->Subject = "📩 New Contact Form Message: " . $subject;
        $mail->Body    = "You received a new message from your portfolio contact form.\n\n" .
                         "👤 Name: $name\n" .
                         "📧 Email: $email\n" .
                         "📌 Subject: $subject\n\n" .
                         "📝 Message:\n$message\n\n" .
                         "---------------------------\n" .
                         "This message was also saved in your database.";

        $mail->send();

        // 2. Send Confirmation to Sender
        $confirm = new PHPMailer(true);
        $confirm->isSMTP();
        $confirm->Host       = 'smtp.gmail.com';
        $confirm->SMTPAuth   = true;
        $confirm->Username   = 'your-email@gmail.com';
        $confirm->Password   = 'your-app-password';
        $confirm->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $confirm->Port       = 587;

        $confirm->setFrom('your-email@gmail.com', 'Afifa Sultana');
        $confirm->addAddress($email, $name);
        $confirm->Subject = "✅ We received your message - Afifa Sultana Portfolio";
        $confirm->Body    = "Hello $name,\n\n" .
                            "Thank you for reaching out through my portfolio website. 🙏\n\n" .
                            "I’ve received your message and will get back to you as soon as possible.\n\n" .
                            "Here’s a copy of your submission:\n" .
                            "---------------------------\n" .
                            "📌 Subject: $subject\n" .
                            "📝 Message:\n$message\n" .
                            "---------------------------\n\n" .
                            "Best regards,\n" .
                            "Afifa Sultana";

        $confirm->send();

        unset($_SESSION['form_data']);
        $_SESSION['success'] = "✅ Message sent successfully! A confirmation email has also been sent to you.";
    } catch (Exception $e) {
        $_SESSION['error'] = "⚠️ Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

    // ===== Redirect back with success =====
    unset($_SESSION['form_data']);
    $_SESSION['success'] = "✅ Message sent successfully! A confirmation email has also been sent to you.";
    header("Location: contact.php#contact");
    exit;
}

// Fetch contact info fields
$sql = "SELECT * FROM contact_info ORDER BY id ASC";
$result = $conn->query($sql);

$cards = $radios = $checkboxes = $terms = [];
while ($row = $result->fetch_assoc()) {
    switch ($row['input_type']) {
        case 'card': $cards[] = $row; break;
        case 'radio': $radios[] = $row; break;
        case 'checkbox': $checkboxes[] = $row; break;
        case 'terms': $terms[] = $row; break;
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
      
      <!-- Show error/success messages -->
      <?php if (!empty($_SESSION['error'])): ?>
        <p class="error-msg"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
      <?php endif; ?>

      <?php if (!empty($_SESSION['success'])): ?>
        <p class="success-msg"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
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
    document.cookie = "terms_accepted=true; path=/; max-age=" + 60*60*24*365; // 1 year
  }
}
</script>
