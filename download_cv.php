<?php
// Absolute server path to the file
$file = __DIR__ . '/assets/pdf/CV.pdf';

if (file_exists($file)) {
    // Force browser to download the file
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    flush(); // clear buffer before output
    readfile($file);
    exit;
} else {
    http_response_code(404);
    echo "File not found: " . $file;
}
