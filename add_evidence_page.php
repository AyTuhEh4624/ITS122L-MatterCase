<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php"); // Include encryption function
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/audit_log.php"); 
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
    $evidence_type = $_POST['evidence_type'];
    $description = $_POST['description'];
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
                // Insert the new evidence into the database
                $stmt = $conn->prepare("INSERT INTO evidence (case_id, evidence_type, file_path, description, submission_status) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $case_id, $evidence_type, $relative_path, $description, $submission_status);

                if ($stmt->execute()) {

                    // Log the action in the audit log
                    $action = "Added new $evidence_type evidence to case ID: $case_id, submission status: $submission_status";
                    logAction($conn, $user_id, $action, $key, $method);

                    // Redirect back to the case details page with a success message
                    header("Location: view_case_evidence.php?case_id=$case_id&success=1");
                    exit();
                } else {
                    // Redirect back to the add evidence page with an error message
                    header("Location: add_evidence_page.php?case_id=$case_id&error=1");
                    exit();
                }

                $stmt->close();
            } else {
                // Redirect back to the add evidence page with an error message
                header("Location: add_evidence_page.php?case_id=$case_id&error=1");
                exit();
            }
        } else {
            // Redirect back to the add evidence page with an error message
            header("Location: add_evidence_page.php?case_id=$case_id&error=1");
            exit();
        }
    } else {
        // Redirect back to the add evidence page with an error message
        header("Location: add_evidence_page.php?case_id=$case_id&error=1");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Evidence</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Add <span class="text-green-400">Evidence</span></span>
            <div class="ml-auto">
                <a href="view_case_details.php?case_id=<?php echo $case_id; ?>">
                    <button class="text-gray-300">Back to Case Details</button>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow flex justify-center mt-4">
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[90%] max-w-lg">
                <h2 class="text-xl font-semibold text-white mb-4">Add Evidence</h2>
                
                <!-- Display success or error messages -->
                <?php if (isset($_GET['success'])): ?>
                    <p class="text-green-400">Evidence added successfully!</p>
                <?php elseif (isset($_GET['error'])): ?>
                    <p class="text-red-400">Failed to add evidence. Please try again.</p>
                <?php endif; ?>
                
                <!-- Form to Add a New Evidence -->
                <form action="add_evidence_page.php" method="POST" enctype="multipart/form-data" class="mt-4">
                    <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">
                    
                    <label for="evidence_type" class="block mb-2 font-semibold">Evidence Type:</label>
                    <input type="text" id="evidence_type" name="evidence_type" required class="w-full p-2 border border-gray-500 rounded-lg bg-gray-800 text-white">
                    
                    <label for="file" class="block mt-4 mb-2 font-semibold">File:</label>
                    <input type="file" id="file" name="file" required class="w-full p-2 border border-gray-500 rounded-lg bg-gray-800 text-white">
                    
                    <label for="description" class="block mt-4 mb-2 font-semibold">Description:</label>
                    <textarea id="description" name="description" required class="w-full p-2 border border-gray-500 rounded-lg bg-gray-800 text-white"></textarea>
                    
                    <label for="submission_status" class="block mt-4 mb-2 font-semibold">Submission Status:</label>
                    <select id="submission_status" name="submission_status" required class="w-full p-2 border border-gray-500 rounded-lg bg-gray-800 text-white">
                <?php if ($usertype == 0 || $usertype == 1): ?>
                            <option value="Submitted">Submitted</option>
                <option value="Rejected">Rejected</option>
                <?php endif; ?>
                            <option value="Pending">Pending</option>
                            
                    </select>
                    
                    <button type="submit" class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12 mt-4">Add Evidence</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>