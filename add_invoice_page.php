<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php"); // Include encryption function
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php");
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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $client_id = $_POST['client_id'];
    $case_id = $_POST['case_id'];
    $amount = $_POST['amount'];
    $payment_status = $_POST['payment_status'];
    $due_date = $_POST['due_date'];

    // Insert the new invoice into the database
    $stmt = $conn->prepare("INSERT INTO invoices (client_id, case_id, amount, payment_status, due_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iidss", $client_id, $case_id, $amount, $payment_status, $due_date);

    if ($stmt->execute()) {

        // Log the action in the audit log
        $action = "Added new invoice to client ID: $client_id, case ID: $case_id, amount: $amount, due date: $due_date";
        logAction($conn, $user_id, $action, $key, $method);
        // Redirect back to the case details page with a success message
        header("Location: view_case_invoices.php?case_id=$case_id&success=1");
        exit();
    } else {
        // Redirect back to the add invoice page with an error message
        header("Location: add_invoice_page.php?case_id=$case_id&error=1");
        exit();
    }

    $stmt->close();
    $conn->close();
}

$case_id = $_GET['case_id']; // Get the case ID from the URL

// Fetch clients for the dropdown
$clients = $conn->query("SELECT client_id, client_name FROM clients");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Invoice</title>
</head>
<body>
    <h1>Add Invoice</h1>

    <!-- Display success or error messages -->
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Invoice added successfully!</p>
    <?php elseif (isset($_GET['error'])): ?>
        <p style="color: red;">Failed to add invoice. Please try again.</p>
    <?php endif; ?>

    <!-- Form to Add a New Invoice -->
    <form action="add_invoice_page.php" method="POST">
        <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">

        <label for="client_id">Client:</label>
        <select id="client_id" name="client_id" required>
            <?php while ($client = $clients->fetch_assoc()): ?>
                <option value="<?php echo $client['client_id']; ?>">
                    <?php echo htmlspecialchars(decryptData($client['client_name'], $key, $method)); ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" required><br><br>

        <label for="payment_status">Payment Status:</label>
        <select id="payment_status" name="payment_status" required>
            <option value="Pending">Pending</option>
            <option value="Paid">Paid</option>
        </select><br><br>

        <label for="due_date">Due Date:</label>
        <input type="date" id="due_date" name="due_date" required><br><br>

        <button type="submit">Add Invoice</button>
    </form>

    <p><a href="view_case_invoices.php?case_id=<?php echo $case_id; ?>">Back to Case Details</a></p>
</body>
</html>