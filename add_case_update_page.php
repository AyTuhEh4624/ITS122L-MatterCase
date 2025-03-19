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

// Restrict access to Admins, Partners, and Lawyers only
if ($usertype != 0 && $usertype != 1 && $usertype != 2) {
    header('Location: view_cases_page.php');
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
    $case_id = $_POST['case_id'];
    $update_text = $_POST['update_text'];
    $updated_by = $user_id; // Use the logged-in user's ID

    // Validate that the user ID exists in the `users` table
    $user_check_query = "SELECT id FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_check_query);
    $stmt->bind_param("i", $updated_by);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        // If the user ID is invalid, show an error
        die("Error: Invalid user ID. Please log in again.");
    }

    // Insert the new case update into the database
    $stmt = $conn->prepare("INSERT INTO case_updates (case_id, update_text, updated_by) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $case_id, $update_text, $updated_by);

    if ($stmt->execute()) {
        // Redirect back to the case details page with a success message
        header("Location: view_case_details.php?case_id=$case_id&success=1");
        exit();
    } else {
        // Redirect back to the add case update page with an error message
        header("Location: add_case_update_page.php?case_id=$case_id&error=1");
        exit();
    }

    $stmt->close();
    $conn->close();
}

$case_id = $_GET['case_id']; // Get the case ID from the URL
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Case Update</title>
    <link rel="stylesheet" href="css/add_case_update_page.css">
</head>
<body>

    <div class="top-container">
        <div class="top-nav">
            <div class="top-left">
                <img src="FrontEndTrial\img\logo1.png" alt="MatterCase Logo">
                <h1>MatterCase</h1>
            </div> 
            <a href="logout.php" >Logout</a>
        </div>
    </div>

    <!-- Display success or error messages -->
    <div class="main-container">
        <h2>Add Case Update</h2>
        <div class="status-message">
            <?php if (isset($_GET['success'])): ?>
                <p style="color: green;">Case update added successfully!</p>
            <?php elseif (isset($_GET['error'])): ?>
                <p style="color: red;">Failed to add case update. Please try again.</p>
            <?php endif; ?>
        </div>

        <!-- Form to Add a New Case Update -->
        <div class="form">
        <form action="add_case_update_page.php" method="POST">
            <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">

            <textarea id="update_text" name="update_text" placeholder="Update Description" required></textarea><br><br>

            <button type="submit">Add Update</button>
        </form>
        <div class="links">
            <p><a href="view_case_details.php?case_id=<?php echo $case_id; ?>">Back to Case Details</a></p>
        </div>

    </div>
    </div>
</body>
</html>