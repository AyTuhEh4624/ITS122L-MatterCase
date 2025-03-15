<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php"); // Include decryption function

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

// Fetch case details
$case_id = $_GET['case_id'];
$query = "SELECT * FROM cases WHERE case_id = $case_id";
$result = $conn->query($query);
$case = $result->fetch_assoc();

// Fetch related data
$case_updates = $conn->query("SELECT * FROM case_updates WHERE case_id = $case_id")->fetch_all(MYSQLI_ASSOC);
$case_fees = $conn->query("SELECT * FROM case_fees WHERE case_id = $case_id")->fetch_all(MYSQLI_ASSOC);
$evidence = $conn->query("SELECT * FROM evidence WHERE case_id = $case_id")->fetch_all(MYSQLI_ASSOC);
$forms = $conn->query("SELECT * FROM forms WHERE case_id = $case_id")->fetch_all(MYSQLI_ASSOC);
$invoices = $conn->query("SELECT * FROM invoices WHERE case_id = $case_id")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Details</title>
</head>
<body>
    <h1>Case Details</h1>
    <a href="<?php
        // Redirect to the appropriate dashboard based on usertype
        switch ($usertype) {
            case 0: // Admin
                echo 'dashboard_admin.php';
                break;
            case 1: // Partner
                echo 'dashboard_partner.php';
                break;
            case 2: // Lawyer
                echo 'dashboard_lawyer.php';
                break;
            case 3: // Paralegal
                echo 'dashboard_paralegal.php';
                break;
            case 4: // Messenger
                echo 'dashboard_messenger.php';
                break;
            default:
                echo 'login_page.php'; // Fallback to login page
                break;
        }
    ?>">Back to Dashboard</a>
    <h2>Case: <?php echo htmlspecialchars($case['case_title']); ?></h2>

    <h3>Case Updates</h3>
    <table border="1">
        <thead>
            <tr>
                <th>Update ID</th>
                <th>Update Text</th>
                <th>Updated By</th>
                <th>Updated At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($case_updates as $update): ?>
                <tr>
                    <td><?php echo htmlspecialchars($update['update_id']); ?></td>
                    <td><?php echo htmlspecialchars($update['update_text']); ?></td>
                    <td><?php echo htmlspecialchars($update['updated_by']); ?></td>
                    <td><?php echo htmlspecialchars($update['updated_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Repeat similar tables for case_fees, evidence, forms, and invoices -->
</body>
</html>