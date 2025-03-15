<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php"); // Include encryption function

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login_page.php');
    exit();
}

// Get the file path from the query parameter
if (!isset($_GET['file'])) {
    die("File not specified.");
}

$file_path = $_GET['file'];

// Ensure the file exists
if (!file_exists($file_path)) {
    die("File not found.");
}

// Get the file type
$file_type = mime_content_type($file_path);

// Display the file based on its type
switch ($file_type) {
    case 'application/pdf':
        header('Content-Type: application/pdf');
        readfile($file_path);
        break;

    case 'image/jpeg':
    case 'image/png':
    case 'image/gif':
        header('Content-Type: ' . $file_type);
        readfile($file_path);
        break;

    case 'text/plain':
        header('Content-Type: text/plain');
        echo nl2br(file_get_contents($file_path)); // Display text with line breaks
        break;

    default:
        die("Unsupported file type.");
}
?>