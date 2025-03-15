<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php"); // Include decryption function
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php"); // Include encryption function

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

// Decrypt case title and court
$case['case_title'] = decryptData($case['case_title'], $key, $method);
$case['court'] = decryptData($case['court'], $key, $method);

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
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        h2, h3 {
            margin-top: 20px;
        }
    </style>
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
    <p><strong>Court:</strong> <?php echo htmlspecialchars($case['court']); ?></p>
    <p><strong>Case Type:</strong> <?php echo htmlspecialchars($case['case_type']); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($case['status']); ?></p>
    <p><strong>Created At:</strong> <?php echo htmlspecialchars($case['created_at']); ?></p>

<!-- Case Updates -->
<h3>Case Updates</h3>
<?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
    <p><a href="add_case_update_page.php?case_id=<?php echo $case_id; ?>">Add New Update</a></p>
<?php endif; ?>
<table>
    <thead>
        <tr>
            <th>Update ID</th>
            <th>Update Text</th>
            <th>Updated By</th>
            <th>Updated At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($case_updates as $update): ?>
            <tr>
                <td><?php echo htmlspecialchars($update['update_id']); ?></td>
                <td><?php echo htmlspecialchars($update['update_text']); ?></td>
                <td><?php echo htmlspecialchars($update['updated_by']); ?></td>
                <td><?php echo htmlspecialchars($update['updated_at']); ?></td>
                <td>
                    <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                        <a href="edit_case_update_page.php?update_id=<?php echo $update['update_id']; ?>">Edit</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    <!-- Evidence -->
    <h3>Evidence</h3>
    <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
        <p><a href="add_evidence_page.php?case_id=<?php echo $case_id; ?>">Add New Evidence</a></p>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>Evidence ID</th>
                <th>Evidence Type</th>
                <th>File</th>
                <th>Description</th>
                <th>Submission Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($evidence as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['evidence_id']); ?></td>
                    <td><?php echo htmlspecialchars($item['evidence_type']); ?></td>
                    <td>
                        <?php if (!empty($item['file_path'])): ?>
                            <a href="view_file.php?file=<?php echo urlencode($item['file_path']); ?>" target="_blank">View File</a>
                        <?php else: ?>
                            No file uploaded
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td><?php echo htmlspecialchars($item['submission_status']); ?></td>
                    <td>
                        <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                            <a href="edit_evidence_page.php?evidence_id=<?php echo $item['evidence_id']; ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Forms -->
    <h3>Forms</h3>
    <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
        <p><a href="add_form_page.php?case_id=<?php echo $case_id; ?>">Add New Form</a></p>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>Form ID</th>
                <th>Form Title</th>
                <th>Submission Status</th>
                <th>Uploaded At</th>
                <th>File</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($forms as $form): ?>
                <tr>
                    <td><?php echo htmlspecialchars($form['form_id']); ?></td>
                    <td><?php echo htmlspecialchars($form['form_title']); ?></td>
                    <td><?php echo htmlspecialchars($form['submission_status']); ?></td>
                    <td><?php echo htmlspecialchars($form['uploaded_at']); ?></td>
                    <td>
                        <?php if (!empty($form['file_path'])): ?>
                            <a href="view_file.php?file=<?php echo urlencode($form['file_path']); ?>" target="_blank">View File</a>
                        <?php else: ?>
                            No file uploaded
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                            <a href="edit_form_page.php?form_id=<?php echo $form['form_id']; ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Case Fees -->
    <h3>Case Fees</h3>
    <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
        <p><a href="add_case_fee_page.php?case_id=<?php echo $case_id; ?>">Add New Fee</a></p>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>Fee ID</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Payment Status</th>
                <th>Due Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($case_fees as $fee): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fee['fee_id']); ?></td>
                    <td><?php echo htmlspecialchars($fee['amount']); ?></td>
                    <td><?php echo htmlspecialchars($fee['fee_description']); ?></td>
                    <td><?php echo htmlspecialchars($fee['payment_status']); ?></td>
                    <td><?php echo htmlspecialchars($fee['due_date']); ?></td>
                    <td>
                        <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                            <a href="edit_case_fee_page.php?fee_id=<?php echo $fee['fee_id']; ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Invoices -->
    <h3>Invoices</h3>
    <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
        <p><a href="add_invoice_page.php?case_id=<?php echo $case_id; ?>">Add New Invoice</a></p>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>Invoice ID</th>
                <th>Amount</th>
                <th>Payment Status</th>
                <th>Due Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $invoice): ?>
                <tr>
                    <td><?php echo htmlspecialchars($invoice['invoice_id']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['amount']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['payment_status']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['due_date']); ?></td>
                    <td>
                        <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                            <a href="edit_invoice_page.php?invoice_id=<?php echo $invoice['invoice_id']; ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>