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
    $amount = $_POST['amount'];
    $fee_description = $_POST['fee_description'];
    $payment_status = $_POST['payment_status'];
    $due_date = $_POST['due_date'];

    // Insert the new case fee into the database
    $stmt = $conn->prepare("INSERT INTO case_fees (case_id, amount, fee_description, payment_status, due_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $case_id, $amount, $fee_description, $payment_status, $due_date);

    if ($stmt->execute()) {
        // Redirect back to the case details page with a success message
        header("Location: view_case_fees.php?case_id=$case_id&success=1");
        exit();
    } else {
        // Redirect back to the add case fee page with an error message
        header("Location: add_case_fee_page.php?case_id=$case_id&error=1");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Case Fee</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Add <span class="text-green-400">Case Fee</span></span>
            <div class="ml-auto">
                <a href="view_case_details.php?case_id=<?php echo $case_id; ?>">
                    <button class="text-gray-300">Back to Case Details</button>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow flex justify-center mt-4">
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[90%] max-w-lg">
                <h2 class="text-xl font-semibold text-white mb-4">Add Case Fee</h2>
                
                <!-- Form to Add a New Case Fee -->
                <form action="add_case_fee_page.php" method="POST" class="mt-4">
                    <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">
                    
                    <label for="amount" class="block mb-2 font-semibold">Amount:</label>
                    <input type="number" id="amount" name="amount" step="0.01" required class="w-full p-2 border border-gray-500 rounded-lg bg-gray-800 text-white">
                    
                    <label for="fee_description" class="block mt-4 mb-2 font-semibold">Description:</label>
                    <textarea id="fee_description" name="fee_description" required class="w-full p-2 border border-gray-500 rounded-lg bg-gray-800 text-white"></textarea>
                    
                    <label for="payment_status" class="block mt-4 mb-2 font-semibold">Payment Status:</label>
                    <select id="payment_status" name="payment_status" required class="w-full p-2 border border-gray-500 rounded-lg bg-gray-800 text-white">
                        <option value="Unpaid">Unpaid</option>
                        <option value="Paid">Paid</option>
                        <option value="Overdue">Overdue</option>
                    </select>
                    
                    <label for="due_date" class="block mt-4 mb-2 font-semibold">Due Date:</label>
                    <input type="date" id="due_date" name="due_date" required class="w-full p-2 border border-gray-500 rounded-lg bg-gray-800 text-white">
                    
                    <button type="submit" class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12 mt-4">Add Fee</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
