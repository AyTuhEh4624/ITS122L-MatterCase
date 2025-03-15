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

// Ensure the uploads directory exists
$upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true); // Create the directory with proper permissions
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $case_id = $_POST['case_id'];
    $form_title = $_POST['form_title'];
    $submission_status = $_POST['submission_status'];

    // Handle file upload
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
                // Insert the new form into the database
                $stmt = $conn->prepare("INSERT INTO forms (case_id, form_title, file_path, submission_status) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $case_id, $form_title, $relative_path, $submission_status);

                if ($stmt->execute()) {
                    // Redirect back to the case details page with a success message
                    header("Location: view_case_details.php?case_id=$case_id&success=1");
                    exit();
                } else {
                    // Redirect back to the add form page with an error message
                    header("Location: add_form_page.php?case_id=$case_id&error=1");
                    exit();
                }

                $stmt->close();
            } else {
                // Redirect back to the add form page with an error message
                header("Location: add_form_page.php?case_id=$case_id&error=1");
                exit();
            }
        } else {
            // Redirect back to the add form page with an error message
            header("Location: add_form_page.php?case_id=$case_id&error=1");
            exit();
        }
    } else {
        // Redirect back to the add form page with an error message
        header("Location: add_form_page.php?case_id=$case_id&error=1");
        exit();
    }

    $conn->close();
}

$case_id = $_GET['case_id']; // Get the case ID from the URL
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Form</title>
</head>
<body>
    <h1>Add Form</h1>

    <!-- Display success or error messages -->
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Form added successfully!</p>
    <?php elseif (isset($_GET['error'])): ?>
        <p style="color: red;">Failed to add form. Please try again.</p>
    <?php endif; ?>

    <!-- Form to Add a New Form -->
    <form action="add_form_page.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">

        <label for="form_title">Form Title:</label>
        <input type="text" id="form_title" name="form_title" required><br><br>

        <label for="file">File:</label>
        <input type="file" id="file" name="file" required><br><br>

        <label for="submission_status">Submission Status:</label>
        <select id="submission_status" name="submission_status" required>
            <option value="Submitted">Submitted</option>
            <option value="Pending">Pending</option>
            <option value="Rejected">Rejected</option>
        </select><br><br>

        <button type="submit">Add Form</button>
    </form>

    <p><a href="view_case_details.php?case_id=<?php echo $case_id; ?>">Back to Case Details</a></p>
</body>
</html>