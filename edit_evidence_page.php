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

// Fetch the evidence to edit
$evidence_id = $_GET['evidence_id'];
$query = "SELECT * FROM evidence WHERE evidence_id = $evidence_id";
$result = $conn->query($query);
$evidence = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $evidence_type = $_POST['evidence_type'];
    $description = $_POST['description'];
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
                if (file_exists($upload_dir . basename($evidence['file_path']))) {
                    unlink($upload_dir . basename($evidence['file_path']));
                }

                // Update the evidence in the database with the new file
                $stmt = $conn->prepare("UPDATE evidence SET evidence_type = ?, file_path = ?, description = ?, submission_status = ? WHERE evidence_id = ?");
                $stmt->bind_param("ssssi", $evidence_type, $relative_path, $description, $submission_status, $evidence_id);
            } else {
                // Redirect back to the edit evidence page with an error message
                header("Location: edit_evidence_page.php?evidence_id=$evidence_id&error=1");
                exit();
            }
        } else {
            // Redirect back to the edit evidence page with an error message
            header("Location: edit_evidence_page.php?evidence_id=$evidence_id&error=1");
            exit();
        }
    } else {
        // Update the evidence in the database without changing the file
        $stmt = $conn->prepare("UPDATE evidence SET evidence_type = ?, description = ?, submission_status = ? WHERE evidence_id = ?");
        $stmt->bind_param("sssi", $evidence_type, $description, $submission_status, $evidence_id);
    }

    if ($stmt->execute()) {
        // Redirect back to the case details page with a success message
        header("Location: view_case_details.php?case_id={$evidence['case_id']}&success=1");
        exit();
    } else {
        // Redirect back to the edit evidence page with an error message
        header("Location: edit_evidence_page.php?evidence_id=$evidence_id&error=1");
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
    <title>Edit Evidence</title>
</head>
<body>
    <h1>Edit Evidence</h1>

    <!-- success or error msg -->
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Evidence updated successfully!</p>
    <?php elseif (isset($_GET['error'])): ?>
        <p style="color: red;">Failed to update evidence. Please try again.</p>
    <?php endif; ?>

    <!-- Edit evidence -->
    <form action="edit_evidence_page.php?evidence_id=<?php echo $evidence_id; ?>" method="POST" enctype="multipart/form-data">
        <label for="evidence_type">Evidence Type:</label>
        <input type="text" id="evidence_type" name="evidence_type" value="<?php echo htmlspecialchars($evidence['evidence_type']); ?>" required><br><br>

        <label for="file">File:</label>
        <input type="file" id="file" name="file"><br><br>
        <p>Current File: 
            <?php if (!empty($evidence['file_path'])): ?>
                <?php
                $file_path = $evidence['file_path'];
                $encoded_path = urlencode($file_path);
                $view_file_url = "/ITS122L-MatterCase/view_file.php?file=" . $encoded_path;
                ?>
                <a href="<?php echo htmlspecialchars($view_file_url); ?>" target="_blank"><?php echo basename($evidence['file_path']); ?></a>
            <?php else: ?>
                No file uploaded
            <?php endif; ?>
        </p>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($evidence['description']); ?></textarea><br><br>

        <label for="submission_status">Submission Status:</label>
        <select id="submission_status" name="submission_status" required>
            <option value="Submitted" <?php echo $evidence['submission_status'] == 'Submitted' ? 'selected' : ''; ?>>Submitted</option>
            <option value="Pending" <?php echo $evidence['submission_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="Rejected" <?php echo $evidence['submission_status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
        </select><br><br>

        <button type="submit">Update</button>
    </form>

    <p><a href="view_case_details.php?case_id=<?php echo $evidence['case_id']; ?>">Back to Case Details</a></p>
</body>
</html>