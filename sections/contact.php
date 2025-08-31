<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// -------- .env loader --------
$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

// -------- robust env() helper --------
function env($key, $default = null) {
    if (isset($_ENV[$key])) return $_ENV[$key];
    if (isset($_SERVER[$key])) return $_SERVER[$key];
    $v = getenv($key);
    return ($v !== false && $v !== null) ? $v : $default;
}

// -------- DB connection --------
$conn = new mysqli(
    env('DB_HOST', '127.0.0.1'),
    env('DB_USERNAME', 'root'),
    env('DB_PASSWORD', ''),
    env('DB_DATABASE', 'portfolio_db'),
    (int) env('DB_PORT', 3306)
);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// -------- helper: build mailer --------
function build_mailer(): PHPMailer {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
    $mail->SMTPAuth   = true;
    $mail->Username   = env('MAIL_USERNAME');       // Gmail address for auth
    $mail->Password   = env('MAIL_PASSWORD');       // Gmail App Password

    // Map encryption properly
    $enc = strtolower((string) env('MAIL_ENCRYPTION', 'tls'));
    $mail->SMTPSecure = $enc === 'ssl'
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;

    // Port as int (465 for ssl, 587 for tls by default)
    $mail->Port = (int) env('MAIL_PORT', $enc === 'ssl' ? 465 : 587);

    $mail->CharSet = 'UTF-8';

    // Optional: allow self-signed certs in local dev (XAMPP + antivirus proxies)
    if (filter_var(env('MAIL_ALLOW_SELF_SIGNED', 'false'), FILTER_VALIDATE_BOOLEAN)) {
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];
    }

    // Log SMTP transcript to file (won't break redirects)
    $logFile = __DIR__ . '/../storage/logs/mail.log';
    if (!is_dir(dirname($logFile))) {
        @mkdir(dirname($logFile), 0777, true);
    }
    $mail->SMTPDebug  = 3; // verbose to file
    $mail->Debugoutput = function($str, $level) use ($logFile) {
        @file_put_contents($logFile, '[' . date('c') . "] ($level) $str\n", FILE_APPEND);
    };

    // Use MAIL_FROM_ADDRESS (same as Gmail) to satisfy Gmail/DMARC rules
    $fromAddress = env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME'));
    $fromName    = env('MAIL_FROM_NAME', 'Website');
    $mail->setFrom($fromAddress, $fromName);

    // Admin notification recipient (you)
    $mail->addAddress('afifasultana637@gmail.com', 'Admin');

    return $mail;
}

// -------- handle form submission --------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect/sanitize
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $subject  = trim($_POST['subject'] ?? '');
    $message  = trim($_POST['message'] ?? '');
    $purpose  = $_POST['purpose'] ?? '';
    $options  = isset($_POST['options']) ? implode(", ", (array)$_POST['options']) : '';

    // Preserve form data on error
    $_SESSION['form_data'] = [
        'name'    => $name,
        'email'   => $email,
        'subject' => $subject,
        'message' => $message,
    ];

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO messages (name, email, subject, message, purpose, options) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        $_SESSION['error'] = "❌ Failed to prepare DB statement.";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
    $stmt->bind_param("ssssss", $name, $email, $subject, $message, $purpose, $options);

    $dbOk = $stmt->execute();
    $stmt->close();

    if ($dbOk) {
        // Build and send mail
        $mail = build_mailer();

        // Safe body (avoid HTML injection)
        $safeName    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeEmail   = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $safeMsgHtml = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        $safePurpose = htmlspecialchars($purpose, ENT_QUOTES, 'UTF-8');
        $safeOptions = htmlspecialchars($options, ENT_QUOTES, 'UTF-8');

        // Reply-to: validate user email; fallback if invalid
        if (PHPMailer::validateAddress($email)) {
            $mail->addReplyTo($email, $name ?: 'Visitor');
        } else {
            // fallback reply-to so PHPMailer doesn't choke
            $mail->addReplyTo('no-reply@localhost', 'Website Visitor');
        }

        $mail->isHTML(true);
        $mail->Subject = "📩 New Contact Form Submission";
        $mail->Body = "
            <h2>New Contact Message Received</h2>
            <p><b>Name:</b> {$safeName}</p>
            <p><b>Email:</b> {$safeEmail}</p>
            <p><b>Subject:</b> {$safeSubject}</p>
            <p><b>Message:</b><br>{$safeMsgHtml}</p>
            <p><b>Purpose:</b> {$safePurpose}</p>
            <p><b>Options:</b> {$safeOptions}</p>
            <hr>
            <small>Sent from {$safeEmail}</small>
        ";
        $mail->AltBody =
            "New contact submission\n" .
            "Name: {$name}\n" .
            "Email: {$email}\n" .
            "Subject: {$subject}\n" .
            "Message:\n{$message}\n" .
            "Purpose: {$purpose}\n" .
            "Options: {$options}\n";

        try {
            $ok = $mail->send();
            if ($ok) {
                unset($_SESSION['form_data']);
                $_SESSION['success'] = "✅ Your message has been sent successfully!";
            } else {
                $_SESSION['error'] = "⚠️ Message saved but email could not be sent.";
            }
        } catch (Exception $e) {
            // Detailed error also in storage/logs/mail.log
            $_SESSION['error'] = "⚠️ Message saved but email could not be sent. Error: " . $mail->ErrorInfo;
        }
    } else {
        $_SESSION['error'] = "❌ Failed to save message.";
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// -------- Fetch contact info fields --------
$sql = "SELECT * FROM contact_info ORDER BY id ASC";
$result = $conn->query($sql);

$cards = $radios = $checkboxes = $terms = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        switch ($row['input_type']) {
            case 'card':     $cards[] = $row; break;
            case 'radio':    $radios[] = $row; break;
            case 'checkbox': $checkboxes[] = $row; break;
            case 'terms':    $terms[] = $row; break;
        }
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