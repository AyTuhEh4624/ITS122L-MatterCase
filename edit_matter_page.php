<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php"); // Include decryption function
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php"); // Include encryption function
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/audit_log.php"); // Include encryption function
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

// Fetch matter data to edit
if (isset($_GET['matter_id'])) {
    $matter_id = $_GET['matter_id'];
    $query = "SELECT * FROM matters WHERE matter_id = $matter_id";
    $result = $conn->query($query);
    $matter = $result->fetch_assoc();

    if (!$matter) {
        echo "Matter not found.";
        exit();
    }

    // Decrypt the title and description
    $title = decryptData($matter['title'], $key, $method);
    $description = decryptData($matter['description'], $key, $method);
    $status = $matter['status'];
} else {
    echo "Matter ID not provided.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_title = $_POST['title'];
    $new_description = $_POST['description'];
    $new_status = $_POST['status'];

    // Compare old and new values to log changes
    $changes = [];
    if ($new_title !== $title) {
        $changes[] = "Title: '$title' to '$new_title'";
    }
    if ($new_description !== $description) {
        $changes[] = "Description: '$description' to '$new_description'";
    }
    if ($new_status !== $status) {
        $changes[] = "Status: '$status' to '$new_status'";
    }

    // Log changes if any
    if (!empty($changes)) {
        $action = "Updated matter ID $matter_id: " . implode(", ", $changes);
        logAction($conn, $user_id, $action, $key, $method);
    }

    // Encrypt the title and description
    $encrypted_title = encryptData($new_title, $key, $method);
    $encrypted_description = encryptData($new_description, $key, $method);

    // Update the matter in the database
    $stmt = $conn->prepare("UPDATE matters SET title = ?, description = ?, status = ? WHERE matter_id = ?");
    $stmt->bind_param("sssi", $encrypted_title, $encrypted_description, $new_status, $matter_id);

    if ($stmt->execute()) {
        // Redirect back to the view matters page with a success message
        header('Location: view_matters_page.php?success=1');
        exit();
    } else {
        // Redirect back to the edit matter page with an error message
        header('Location: edit_matter_page.php?matter_id=' . $matter_id . '&error=1');
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
    <title>Edit Matter</title>
</head>
<body>
    <h1>Edit Matter</h1>

    <!-- Display success or error messages -->
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Matter updated successfully!</p>
    <?php elseif (isset($_GET['error'])): ?>
        <p style="color: red;">Failed to update matter. Please try again.</p>
    <?php endif; ?>

    <!-- Form to Edit Matter -->
    <form action="edit_matter_page.php?matter_id=<?php echo $matter_id; ?>" method="POST">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required><br><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($description); ?></textarea><br><br>

        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="Open" <?php echo ($status === 'Open') ? 'selected' : ''; ?>>Open</option>
            <option value="Closed" <?php echo ($status === 'Closed') ? 'selected' : ''; ?>>Closed</option>
            <option value="Pending" <?php echo ($status === 'Pending') ? 'selected' : ''; ?>>Pending</option>
        </select><br><br>

        <button type="submit">Update Matter</button>
    </form>
        
    <p><a href="view_matters_page.php">Back to View Matters</a></p>
</body>
</html>