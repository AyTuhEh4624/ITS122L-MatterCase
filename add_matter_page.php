<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/audit_log.php"); 

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
        
        // Log the action in the audit log
        $action = "Added new matter with title: $title, Status: $status";
        logAction($conn, $user_id, $action, $key, $method);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Matter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Add <span class="text-green-400">Matter</span></span>
            <div class="ml-auto">
                <a href="view_matters_page.php">
                    <button class="text-gray-300">Back to View Matters</button>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow flex justify-center mt-4">
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[90%] max-w-lg">
                <h2 class="text-xl font-semibold text-white mb-4">Add New Matter</h2>
                
                <!-- Display success or error messages -->
                <?php if (isset($_GET['success'])): ?>
                    <p class="text-green-400">Matter added successfully!</p>
                <?php elseif (isset($_GET['error'])): ?>
                    <p class="text-red-400">Failed to add matter. Please try again.</p>
                <?php endif; ?>
                
                <!-- Form to Add a New Matter -->
                <form action="add_matter_page.php" method="POST" class="mt-4">
                    <label for="title" class="block mb-2 font-semibold">Title:</label>
                    <input type="text" id="title" name="title" required class="w-full p-2 border border-gray-500 rounded-lg bg-gray-800 text-white">
                    
                    <label for="description" class="block mt-4 mb-2 font-semibold">Description:</label>
                    <textarea id="description" name="description" required class="w-full p-2 border border-gray-500 rounded-lg bg-gray-800 text-white"></textarea>
                    
                    <label for="status" class="block mt-4 mb-2 font-semibold">Status:</label>
                    <select id="status" name="status" required class="w-full p-2 border border-gray-500 rounded-lg bg-gray-800 text-white">
                        <option value="Open">Open</option>
                        <option value="Closed">Closed</option>
                        <option value="Pending">Pending</option>
                    </select>
                    
                    <button type="submit" class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12 mt-4">Add Matter</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
