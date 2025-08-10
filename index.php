<?php
$page_title = "Portfolio | Afifa Sultana";
// List all your CSS files here
$page_css = [
    "css/global.css",
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
