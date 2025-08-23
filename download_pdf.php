<?php
/**
 * PDF Download Handler
 * Securely serves generated PDF files
 */

if (!isset($_GET['file']) || empty($_GET['file'])) {
    http_response_code(400);
    die('File parameter required');
}

$filename = basename($_GET['file']); // Security: prevent directory traversal
$filepath = __DIR__ . '/generated_pdfs/' . $filename;

// Check if file exists
if (!file_exists($filepath)) {
    http_response_code(404);
    die('File not found');
}

// Validate file extension
$allowedExtensions = ['pdf', 'html'];
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions)) {
    http_response_code(403);
    die('File type not allowed');
}

// Set appropriate headers
if ($extension === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
} else {
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
}

header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Output file
readfile($filepath);
exit;
?>