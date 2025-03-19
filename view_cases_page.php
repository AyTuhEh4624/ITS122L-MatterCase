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

// Fetch cases based on matter_id or client_id
if (isset($_GET['matter_id'])) {
    $matter_id = $_GET['matter_id'];
    $query = "
        SELECT c.case_id, c.case_title, c.court, c.case_type, c.status, c.created_at
        FROM cases c
        WHERE c.matter_id = $matter_id
    ";
} elseif (isset($_GET['client_id'])) {
    $client_id = $_GET['client_id'];
    $query = "
        SELECT c.case_id, c.case_title, c.court, c.case_type, c.status, c.created_at
        FROM cases c
        WHERE c.client_id = $client_id
    ";
} else {
    // Fetch all cases for Admins, Partners, and Lawyers
    $query = "
        SELECT c.case_id, c.case_title, c.court, c.case_type, c.status, c.created_at
        FROM cases c
    ";
}

$result = $conn->query($query);
if (!$result) {
    die("Database query failed: " . $conn->error);
}

// Fetch and display cases individually
$data = [];
while ($row = $result->fetch_assoc()) {
    // Decrypt case data if necessary
    $row['case_title'] = decryptData($row['case_title'], $key, $method);
    $row['court'] = decryptData($row['court'], $key, $method);
    $data[] = $row;
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Cases</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>View Cases</h1>

    <!-- Add New Case Button (Only for Admins and Partners) -->
    <?php if ($usertype == 0 || $usertype == 1): ?>
        <p><a href="add_case_page.php">Add New Case</a></p>
    <?php endif; ?>

    <!-- Back to Dashboard Button -->
    <p>
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
    </p>

    <!-- Display Existing Cases -->
    <h2>Existing Cases</h2>
    <table>
        <thead>
            <tr>
                <th>Case ID</th>
                <th>Case Title</th>
                <th>Court</th>
                <th>Case Type</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['case_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['case_title']); ?></td>
                    <td><?php echo htmlspecialchars($row['court']); ?></td>
                    <td><?php echo htmlspecialchars($row['case_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td>
                        <!-- View Case Details Link -->
                        <a href="view_case_details.php?case_id=<?php echo $row['case_id']; ?>">View Details</a>
                        <!-- Edit Case Link (Only for Admins and Partners) -->
                        <?php if ($usertype == 0 || $usertype == 1): ?>
                            | <a href="edit_case_page.php?case_id=<?php echo $row['case_id']; ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>