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

// Fetch the case fee to edit
$fee_id = $_GET['fee_id'];
$query = "SELECT * FROM case_fees WHERE fee_id = $fee_id";
$result = $conn->query($query);
$fee = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $amount = $_POST['amount'];
    $fee_description = $_POST['fee_description'];
    $payment_status = $_POST['payment_status'];
    $due_date = $_POST['due_date'];

    // Update the case fee in the database
    $stmt = $conn->prepare("UPDATE case_fees SET amount = ?, fee_description = ?, payment_status = ?, due_date = ? WHERE fee_id = ?");
    $stmt->bind_param("dsssi", $amount, $fee_description, $payment_status, $due_date, $fee_id);

    if ($stmt->execute()) {
        // Redirect back to the case details page with a success message
        header("Location: view_case_fees.php?case_id={$fee['case_id']}&success=1");
        exit();
    } else {
        // Redirect back to the edit case fee page with an error message
        header("Location: edit_case_fee_page.php?fee_id=$fee_id&error=1");
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
    <title>Edit Case Fee</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Edit <span class="text-green-400">Case Fee</span></span>
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
                <h1 class="text-xl font-semibold mb-4">Edit Case Fee</h1>
                
                <!-- Display success or error messages -->
                <?php if (isset($_GET['success'])): ?>
                    <p class="text-green-400">Case fee updated successfully!</p>
                <?php elseif (isset($_GET['error'])): ?>
                    <p class="text-red-400">Failed to update case fee. Please try again.</p>
                <?php endif; ?>
                
                <!-- Form to Edit a Case Fee -->
                <form action="edit_case_fee_page.php?fee_id=<?php echo $fee_id; ?>" method="POST" class="text-left mt-4">
                    <label for="amount" class="block text-sm mb-1">Amount:</label>
                    <input type="number" id="amount" name="amount" step="0.01" value="<?php echo htmlspecialchars($fee['amount']); ?>" required 
                           class="w-full p-2 rounded bg-gray-800 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-400">
                    
                    <label for="fee_description" class="block text-sm mt-3 mb-1">Description:</label>
                    <textarea id="fee_description" name="fee_description" required 
                              class="w-full p-2 rounded bg-gray-800 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-400"><?php echo htmlspecialchars($fee['fee_description']); ?></textarea>
                    
                    <label for="payment_status" class="block text-sm mt-3 mb-1">Payment Status:</label>
                    <select id="payment_status" name="payment_status" required 
                            class="w-full p-2 rounded bg-gray-800 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-400">
                        <option value="Unpaid" <?php echo $fee['payment_status'] == 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                        <option value="Paid" <?php echo $fee['payment_status'] == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="Overdue" <?php echo $fee['payment_status'] == 'Overdue' ? 'selected' : ''; ?>>Overdue</option>
                    </select>
                    
                    <label for="due_date" class="block text-sm mt-3 mb-1">Due Date:</label>
                    <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($fee['due_date']); ?>" required 
                           class="w-full p-2 rounded bg-gray-800 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-green-400">
                    
                    <button type="submit" class="mt-4 bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12">Update</button>
                </form>
                
                <p class="mt-4"><a href="view_case_fees.php?case_id=<?php echo $fee['case_id']; ?>" class="text-blue-400">Back to Case Details</a></p>
            </div>
        </div>
    </div>
</body>
</html>