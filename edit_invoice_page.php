<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php"); // Include encryption function
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php");
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

// Fetch the invoice to edit
$invoice_id = $_GET['invoice_id'];
$query = "SELECT * FROM invoices WHERE invoice_id = $invoice_id";
$result = $conn->query($query);
$invoice = $result->fetch_assoc();

// Fetch clients for the dropdown
$clients = $conn->query("SELECT client_id, client_name FROM clients");

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $client_id = $_POST['client_id'];
    $amount = $_POST['amount'];
    $payment_status = $_POST['payment_status'];
    $due_date = $_POST['due_date'];

    // Update the invoice in the database
    $stmt = $conn->prepare("UPDATE invoices SET client_id = ?, amount = ?, payment_status = ?, due_date = ? WHERE invoice_id = ?");
    $stmt->bind_param("idssi", $client_id, $amount, $payment_status, $due_date, $invoice_id);

    if ($stmt->execute()) {
        // Redirect back to the case details page with a success message
        header("Location: view_case_invoices.php?case_id={$invoice['case_id']}&success=1");
        exit();
    } else {
        // Redirect back to the edit invoice page with an error message
        header("Location: edit_invoice_page.php?invoice_id=$invoice_id&error=1");
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
    <title>Edit Invoice</title>
</head>
<body>
    <h1>Edit Invoice</h1>

    <!-- Display success or error messages -->
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Invoice updated successfully!</p>
    <?php elseif (isset($_GET['error'])): ?>
        <p style="color: red;">Failed to update invoice. Please try again.</p>
    <?php endif; ?>

    <!-- Form to Edit an Invoice -->
    <form action="edit_invoice_page.php?invoice_id=<?php echo $invoice_id; ?>" method="POST">
        <label for="client_id">Client:</label>
        <select id="client_id" name="client_id" required>
            <?php while ($client = $clients->fetch_assoc()): ?>
                <option value="<?php echo $client['client_id']; ?>" <?php echo $client['client_id'] == $invoice['client_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars(decryptData($client['client_name'], $key, $method)); ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" value="<?php echo htmlspecialchars($invoice['amount']); ?>" required><br><br>

        <label for="payment_status">Payment Status:</label>
        <select id="payment_status" name="payment_status" required>
            <option value="Pending" <?php echo $invoice['payment_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="Paid" <?php echo $invoice['payment_status'] == 'Paid' ? 'selected' : ''; ?>>Paid</option>
        </select><br><br>

        <label for="due_date">Due Date:</label>
        <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($invoice['due_date']); ?>" required><br><br>

        <button type="submit">Update</button>
    </form>

    <p><a href="view_case_invoices.php?case_id=<?php echo $invoice['case_id']; ?>">Back to Case Details</a></p>
</body>
</html>