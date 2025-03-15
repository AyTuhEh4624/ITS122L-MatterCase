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
        header("Location: view_case_details.php?case_id={$fee['case_id']}&success=1");
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
    <title>Edit Case Fee</title>
</head>
<body>
    <h1>Edit Case Fee</h1>

    <!-- Display success or error messages -->
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Case fee updated successfully!</p>
    <?php elseif (isset($_GET['error'])): ?>
        <p style="color: red;">Failed to update case fee. Please try again.</p>
    <?php endif; ?>

    <!-- Form to Edit a Case Fee -->
    <form action="edit_case_fee_page.php?fee_id=<?php echo $fee_id; ?>" method="POST">
        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" value="<?php echo htmlspecialchars($fee['amount']); ?>" required><br><br>

        <label for="fee_description">Description:</label>
        <textarea id="fee_description" name="fee_description" required><?php echo htmlspecialchars($fee['fee_description']); ?></textarea><br><br>

        <label for="payment_status">Payment Status:</label>
        <select id="payment_status" name="payment_status" required>
            <option value="Unpaid" <?php echo $fee['payment_status'] == 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
            <option value="Paid" <?php echo $fee['payment_status'] == 'Paid' ? 'selected' : ''; ?>>Paid</option>
            <option value="Overdue" <?php echo $fee['payment_status'] == 'Overdue' ? 'selected' : ''; ?>>Overdue</option>
        </select><br><br>

        <label for="due_date">Due Date:</label>
        <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($fee['due_date']); ?>" required><br><br>

        <button type="submit">Update</button>
    </form>

    <p><a href="view_case_details.php?case_id=<?php echo $fee['case_id']; ?>">Back to Case Details</a></p>
</body>
</html>