<?php
$page_title = "Portfolio | Afifa Sultana";

// ================= FORM HANDLING FIRST =================
$conn = new mysqli("localhost", "root", "", "portfolio_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $purpose = $_POST['purpose'] ?? null;

    $options = isset($_POST['options']) ? implode(", ", $_POST['options']) : null;
    $created_at = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, purpose, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $subject, $message, $purpose, $created_at);
    $stmt->execute();

    if ($options) {
        $last_id = $conn->insert_id;
        $stmt2 = $conn->prepare("UPDATE contact_messages SET message = CONCAT(message, '\n\nOptions: ', ?) WHERE id=?");
        $stmt2->bind_param("si", $options, $last_id);
        $stmt2->execute();
    }

    header("Location: index.php?success=1#contact");
    exit;
}

// List all your CSS files here
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
?>
