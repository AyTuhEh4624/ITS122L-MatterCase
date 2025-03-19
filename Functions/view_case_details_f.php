<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php"); // Include decryption function
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php"); // Include encryption function

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['id'];
$usertype = $_SESSION['usertype'];

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if case_id is provided in the URL
if (!isset($_GET['case_id'])) {
    die("Case ID is missing.");
}

$case_id = $_GET['case_id'];

// Fetch case details
$query = "SELECT * FROM cases WHERE case_id = $case_id";
$result = $conn->query($query);

if (!$result) {
    die("Database query failed: " . $conn->error);
}

$case = $result->fetch_assoc();

if (!$case) {
    die("No case found with the provided ID.");
}

// Decrypt case title and court
$case['case_title'] = decryptData($case['case_title'], $key, $method);
$case['court'] = decryptData($case['court'], $key, $method);

// Fetch related data
$case_updates = $conn->query("SELECT * FROM case_updates WHERE case_id = $case_id");
if (!$case_updates) {
    die("Failed to fetch case updates: " . $conn->error);
}
$case_updates = $case_updates->fetch_all(MYSQLI_ASSOC);

$case_fees = $conn->query("SELECT * FROM case_fees WHERE case_id = $case_id");
if (!$case_fees) {
    die("Failed to fetch case fees: " . $conn->error);
}
$case_fees = $case_fees->fetch_all(MYSQLI_ASSOC);

$evidence = $conn->query("SELECT * FROM evidence WHERE case_id = $case_id");
if (!$evidence) {
    die("Failed to fetch evidence: " . $conn->error);
}
$evidence = $evidence->fetch_all(MYSQLI_ASSOC);

$forms = $conn->query("SELECT * FROM forms WHERE case_id = $case_id");
if (!$forms) {
    die("Failed to fetch forms: " . $conn->error);
}
$forms = $forms->fetch_all(MYSQLI_ASSOC);

$invoices = $conn->query("SELECT * FROM invoices WHERE case_id = $case_id");
if (!$invoices) {
    die("Failed to fetch invoices: " . $conn->error);
}
$invoices = $invoices->fetch_all(MYSQLI_ASSOC);

// Close the database connection
$conn->close();
?>