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

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the uploads directory exists
$upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true); // Create the directory with proper permissions
}

// Fetch the form to edit
$form_id = $_GET['form_id'];
$query = "SELECT * FROM forms WHERE form_id = $form_id";
$result = $conn->query($query);
$form = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $form_title = $_POST['form_title'];
    $submission_status = $_POST['submission_status'];

    // Handle file upload (if a new file is provided)
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_name = basename($_FILES['file']['name']);
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_size = $_FILES['file']['size'];
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file type and size
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
        $max_size = 5 * 1024 * 1024; // 5 MB

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $file_name = uniqid() . "_" . $file_name; // Unique file name to prevent conflicts
            $file_path = $upload_dir . $file_name; // Full server path
            $relative_path = "uploads/" . $file_name; // Relative path for database

            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Delete the old file if it exists
                if (file_exists($upload_dir . basename($form['file_path']))) {
                    unlink($upload_dir . basename($form['file_path']));
                }

                // Update the form in the database with the new file
                $stmt = $conn->prepare("UPDATE forms SET form_title = ?, file_path = ?, submission_status = ? WHERE form_id = ?");
                $stmt->bind_param("sssi", $form_title, $relative_path, $submission_status, $form_id);
            } else {
                // Redirect back to the edit form page with an error message
                header("Location: edit_form_page.php?form_id=$form_id&error=1");
                exit();
            }
        } else {
            // Redirect back to the edit form page with an error message
            header("Location: edit_form_page.php?form_id=$form_id&error=1");
            exit();
        }
    } else {
        // Update the form in the database without changing the file
        $stmt = $conn->prepare("UPDATE forms SET form_title = ?, submission_status = ? WHERE form_id = ?");
        $stmt->bind_param("ssi", $form_title, $submission_status, $form_id);
    }

    if ($stmt->execute()) {
        // Redirect back to the case details page with a success message
        header("Location: view_case_forms.php?case_id={$form['case_id']}&success=1");
        exit();
    } else {
        // Redirect back to the edit form page with an error message
        header("Location: edit_form_page.php?form_id=$form_id&error=1");
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
    <title>Edit Form</title>
</head>
<body>
    <h1>Edit Form</h1>

    <!-- Display success or error messages -->
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Form updated successfully!</p>
    <?php elseif (isset($_GET['error'])): ?>
        <p style="color: red;">Failed to update form. Please try again.</p>
    <?php endif; ?>

    <!-- Form to Edit a Form -->
    <form action="edit_form_page.php?form_id=<?php echo $form_id; ?>" method="POST" enctype="multipart/form-data">
    <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
        <label for="form_title">Form Title:</label>
        <input type="text" id="form_title" name="form_title" value="<?php echo htmlspecialchars($form['form_title']); ?>" required><br><br>

        <label for="file">File:</label>
        <input type="file" id="file" name="file"><br><br>
        <p>Current File: 
            <?php if (!empty($form['file_path'])): ?>
                <a href="/ITS122L-MatterCase/<?php echo htmlspecialchars($form['file_path']); ?>" target="_blank"><?php echo basename($form['file_path']); ?></a>
            <?php else: ?>
                No file uploaded
            <?php endif; ?>
        </p>
        <?php endif; ?>
        <?php if ($usertype == 0 || $usertype == 1 || $usertype == 4): ?>
        <label for="submission_status">Submission Status:</label>
        <select id="submission_status" name="submission_status" required>
            <option value="Submitted" <?php echo $form['submission_status'] == 'Submitted' ? 'selected' : ''; ?>>Submitted</option>
            <option value="Pending" <?php echo $form['submission_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="Rejected" <?php echo $form['submission_status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
        </select><br><br>
        <?php endif; ?>
        <button type="submit">Update</button>
    </form>

    <p><a href="view_case_forms.php?case_id=<?php echo $form['case_id']; ?>">Back to Case Details</a></p>
</body>
</html>