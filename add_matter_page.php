<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php"); // Include encryption function

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['id'];
$usertype = $_SESSION['usertype'];

// Restrict access to Admins and Partners only
if ($usertype != 0 && $usertype != 1) {
    header('Location: view_matters_page.php');
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    // Encrypt the title and description
    $encryptedTitle = encryptData($title, $key, $method);
    $encryptedDescription = encryptData($description, $key, $method);

    // Insert the new matter into the database
    $stmt = $conn->prepare("INSERT INTO matters (title, description, status) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $encryptedTitle, $encryptedDescription, $status);

    if ($stmt->execute()) {
        // Redirect back to the view matters page with a success message
        header('Location: view_matters_page.php?success=1');
        exit();
    } else {
        // Redirect back to the add matter page with an error message
        header('Location: add_matter_page.php?error=1');
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Matter</title>
</head>
<body>
    <h1>Add New Matter</h1>

    <!-- Display success or error messages -->
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Matter added successfully!</p>
    <?php elseif (isset($_GET['error'])): ?>
        <p style="color: red;">Failed to add matter. Please try again.</p>
    <?php endif; ?>

    <!-- Form to Add a New Matter -->
    <form action="add_matter_page.php" method="POST">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required><br><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea><br><br>

        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="Open">Open</option>
            <option value="Closed">Closed</option>
            <option value="Pending">Pending</option>
        </select><br><br>

        <button type="submit">Add Matter</button>
    </form>

    <p><a href="view_matters_page.php">Back to View Matters</a></p>
</body>
</html>