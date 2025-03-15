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
        header("Location: view_case_details.php?case_id=$case_id&success=1");
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
    <title>Add Case Fee</title>
</head>
<body>
    <h1>Add Case Fee</h1>

    <!-- Display success or error messages -->
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Case fee added successfully!</p>
    <?php elseif (isset($_GET['error'])): ?>
        <p style="color: red;">Failed to add case fee. Please try again.</p>
    <?php endif; ?>

    <!-- Form to Add a New Case Fee -->
    <form action="add_case_fee_page.php" method="POST">
        <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">

        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" required><br><br>

        <label for="fee_description">Description:</label>
        <textarea id="fee_description" name="fee_description" required></textarea><br><br>

        <label for="payment_status">Payment Status:</label>
        <select id="payment_status" name="payment_status" required>
            <option value="Unpaid">Unpaid</option>
            <option value="Paid">Paid</option>
            <option value="Overdue">Overdue</option>
        </select><br><br>

        <label for="due_date">Due Date:</label>
        <input type="date" id="due_date" name="due_date" required><br><br>

        <button type="submit">Add Fee</button>
    </form>

    <p><a href="view_case_details.php?case_id=<?php echo $case_id; ?>">Back to Case Details</a></p>
</body>
</html>