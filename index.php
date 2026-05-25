<?php
require_once __DIR__ . '/includes/bootstrap.php';

$page_title = "Portfolio | Afifa Sultana";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    $honeypot = trim($_POST['website'] ?? '');

    if (!csrf_verify($csrf)) {
        $_SESSION['contact_error'] = 'Security verification failed. Please try again.';
        header("Location: index.php#contact");
        exit;
    }

    if (!rate_limit_ok('contact_rate', 5, 120)) {
        $_SESSION['contact_error'] = 'Too many requests. Please try again in a few minutes.';
        header("Location: index.php#contact");
        exit;
    }

    if ($honeypot !== '') {
        $_SESSION['contact_success'] = 'Thanks! Your message has been received.';
        header("Location: index.php#contact");
        exit;
    }

    $name    = trim((string)($_POST['name'] ?? ''));
    $email   = trim((string)($_POST['email'] ?? ''));
    $subject = trim((string)($_POST['subject'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));
    $purpose = trim((string)($_POST['purpose'] ?? ''));

    $optionsRaw = isset($_POST['options']) ? (array) $_POST['options'] : [];
    $options = $optionsRaw ? implode(", ", array_map('trim', $optionsRaw)) : null;

    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        $_SESSION['contact_error'] = 'Please fill in all required fields.';
        header("Location: index.php#contact");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact_error'] = 'Please enter a valid email address.';
        header("Location: index.php#contact");
        exit;
    }

    $created_at = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, purpose, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssssss", $name, $email, $subject, $message, $purpose, $created_at);
        $ok = $stmt->execute();
        $stmt->close();
    } else {
        $ok = false;
    }

    if ($ok && $options) {
        $last_id = $conn->insert_id;
        $stmt2 = $conn->prepare("UPDATE contact_messages SET message = CONCAT(message, '\n\nOptions: ', ?) WHERE id=?");
        if ($stmt2) {
            $stmt2->bind_param("si", $options, $last_id);
            $stmt2->execute();
            $stmt2->close();
        }
    }

    $_SESSION['contact_' . ($ok ? 'success' : 'error')] = $ok
        ? 'Your message has been sent successfully.'
        : 'Something went wrong. Please try again.';

    header("Location: index.php#contact");
    exit;
}

$page_css = [
    "css/global.css",
    "css/header&navbar.css",
    "css/home.css",
    "css/about.css",
    "css/education.css",
    "css/skills.css",
    "css/projects.css",
    "css/contact.css",
    "css/footer.css",
];

include 'sections/header.php';
include 'sections/home.php';
include 'sections/about.php';
include 'sections/education.php';
include 'sections/skills.php';
include 'sections/projects.php';
include 'sections/contact.php';

include 'sections/footer.php';
