<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php"); // Include encryption function
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/audit_log.php"); // Include encryption function
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

                    // Log the action in the audit log
                    $action = "Added new form $form_title to case ID: $case_id, submission status: $submission_status";
                    logAction($conn, $user_id, $action, $key, $method);

                    // Redirect back to the case details page with a success message
                    header("Location: view_case_forms.php?case_id=$case_id&success=1");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Add <span class="text-green-400">Form</span></span>
            <div class="ml-auto flex space-x-4">
                <a href="logout.php"><button class="text-gray-300">Logout</button></a>
                <a href="<?php
                    switch ($usertype) {
                        case 0: echo 'dashboard_admin.php'; break;
                        case 1: echo 'dashboard_partner.php'; break;
                        case 2: echo 'dashboard_lawyer.php'; break;
                        case 3: echo 'dashboard_paralegal.php'; break;
                        case 4: echo 'dashboard_messenger.php'; break;
                        default: echo 'login_page.php'; break;
                    }
                ?>">
                <button class="text-gray-300">Dashboard</button>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow flex justify-center mt-4">
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[90%] max-w-lg">
                <h1 class="text-xl font-semibold mb-4">Add Form</h1>
                
                <!-- Display success or error messages -->
                <?php if (isset($_GET['success'])): ?>
                    <p class="text-green-400">Form added successfully!</p>
                <?php elseif (isset($_GET['error'])): ?>
                    <p class="text-red-400">Failed to add form. Please try again.</p>
                <?php endif; ?>
                
                <!-- Form to Add a New Form -->
                <form action="add_form_page.php" method="POST" enctype="multipart/form-data" class="text-left mt-4">
                    <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">
                    
                    <label for="form_title" class="block text-sm mb-1">Form Title:</label>
                    <input type="text" id="form_title" name="form_title" required 
                           class="w-full p-2 rounded bg-gray-800 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-400">
                    
                    <label for="file" class="block text-sm mt-3 mb-1">File:</label>
                    <input type="file" id="file" name="file" required 
                           class="w-full p-2 rounded bg-gray-800 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-400">
                    
                    <label for="submission_status" class="block text-sm mt-3 mb-1">Submission Status:</label>
                    <select id="submission_status" name="submission_status" required 
                            class="w-full p-2 rounded bg-gray-800 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-400">
                        <option value="Pending">Pending</option>
                        <?php if ($usertype == 0 || $usertype == 1): ?>
                            <option value="Submitted">Submitted</option>
                            <option value="Rejected">Rejected</option>
                        <?php endif; ?>
                    </select>
                    
                    <button type="submit" class="mt-4 bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12">Add Form</button>
                </form>
                
                <p class="mt-4"><a href="view_case_forms.php?case_id=<?php echo $case_id; ?>" class="text-blue-400">Back to Case Details</a></p>
            </div>
        </div>
    </div>
</body>
</html>
