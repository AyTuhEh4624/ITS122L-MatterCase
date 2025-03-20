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

// Fetch the case update to edit
$update_id = $_GET['update_id'];
$query = "SELECT * FROM case_updates WHERE update_id = $update_id";
$result = $conn->query($query);
$update = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $update_text = $_POST['update_text'];

    // Update the case update in the database
    $stmt = $conn->prepare("UPDATE case_updates SET update_text = ? WHERE update_id = ?");
    $stmt->bind_param("si", $update_text, $update_id);

    if ($stmt->execute()) {
        // Redirect back to the case details page with a success message
        header("Location: view_case_updates.php?case_id={$update['case_id']}&success=1");
        exit();
    } else {
        // Redirect back to the edit case update page with an error message
        header("Location: edit_case_update_page.php?update_id=$update_id&error=1");
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
    <title>Edit Case Update</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Edit <span class="text-green-400">Case Update</span></span>
    
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
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[90%] max-w-md">
                <!-- Display success or error messages -->
                <?php if (isset($_GET['success'])): ?>
                    <p class="text-green-400">Case update updated successfully!</p>
                <?php elseif (isset($_GET['error'])): ?>
                    <p class="text-red-400">Failed to update case update. Please try again.</p>
                <?php endif; ?>
    
                <!-- Form to Edit a Case Update -->
                <form action="edit_case_update_page.php?update_id=<?php echo $update_id; ?>" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-left">Update Text</label>
                        <textarea id="update_text" name="update_text" required class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600 h-32"><?php echo htmlspecialchars($update['update_text']); ?></textarea>
                    </div>
                    <div>
                        <button type="submit" class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full">Update</button>
                    </div>
                </form>
    
                <p class="mt-4">
                    <a href="view_case_updates.php?case_id=<?php echo $update['case_id']; ?>" class="text-blue-400">Back to Case Details</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>