<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php"); // Include decryption function

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['id'];
$usertype = $_SESSION['usertype'];

// Restrict access to Paralegals and Messengers
if ($usertype == 3 || $usertype == 4) {
    header('Location: view_cases_page.php');
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if matter_id is provided in the URL
if (!isset($_GET['matter_id'])) {
    die("Matter ID is missing.");
}

$matter_id = $_GET['matter_id'];

// Delete all cases under the matter
$deleteCasesQuery = "DELETE FROM cases WHERE matter_id = $matter_id";
if ($conn->query($deleteCasesQuery) === TRUE) {
    echo "All cases under the matter have been deleted.<br>";
} else {
    die("Error deleting cases: " . $conn->error);
}

// Delete the matter
$deleteMatterQuery = "DELETE FROM matters WHERE matter_id = $matter_id";
if ($conn->query($deleteMatterQuery) === TRUE) {
    echo "Matter deleted successfully!";
} else {
    die("Error deleting matter: " . $conn->error);
}

// Close the database connection
$conn->close();

// Redirect back to the matters page
header("Location: view_matters_page.php");
exit();
?>