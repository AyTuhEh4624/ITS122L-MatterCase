<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/lawyer_assignment.php");

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

// Handle lawyer assignment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_lawyers'])) {
    $case_id = $_POST['case_id'];
    $lawyer_ids = $_POST['lawyer_ids']; // Array of lawyer IDs

    // Assign lawyers to the case
    assignLawyersToCase($conn, $case_id, $lawyer_ids);

    echo "<p>Lawyers assigned successfully!</p>";
}

// Fetch cases based on matter_id or client_id
if (isset($_GET['matter_id'])) {
    $matter_id = $_GET['matter_id'];
    $query = "
        SELECT c.case_id, c.case_title, c.court, c.case_type, c.status, c.created_at,
               cl.client_name, m.title AS matter_title
        FROM cases c
        JOIN clients cl ON c.client_id = cl.client_id
        JOIN matters m ON c.matter_id = m.matter_id
        WHERE c.matter_id = $matter_id
    ";
} elseif (isset($_GET['client_id'])) {
    $client_id = $_GET['client_id'];
    $query = "
        SELECT c.case_id, c.case_title, c.court, c.case_type, c.status, c.created_at,
               cl.client_name, m.title AS matter_title
        FROM cases c
        JOIN clients cl ON c.client_id = cl.client_id
        JOIN matters m ON c.matter_id = m.matter_id
        WHERE c.client_id = $client_id
    ";
} else {
    // Fetch all cases for Admins, Partners, and Lawyers
    $query = "
        SELECT c.case_id, c.case_title, c.court, c.case_type, c.status, c.created_at,
               cl.client_name, m.title AS matter_title
        FROM cases c
        JOIN clients cl ON c.client_id = cl.client_id
        JOIN matters m ON c.matter_id = m.matter_id
    ";
}

$result = $conn->query($query);
$data = $result->fetch_all(MYSQLI_ASSOC);

// Decrypt case data if necessary
foreach ($data as &$row) {
    $row['case_title'] = decryptData($row['case_title'], $key, $method);
    $row['court'] = decryptData($row['court'], $key, $method);
    $row['client_name'] = decryptData($row['client_name'], $key, $method);
    $row['matter_title'] = decryptData($row['matter_title'], $key, $method);
}
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
                <?php if (!empty($data)): ?>
                    <th>Case ID</th>
                    <th>Case Title</th>
                    <th>Court</th>
                    <th>Case Type</th>
                    <th>Status</th>
                    <th>Client Name</th>
                    <th>Matter Title</th>
                    <th>Assigned Lawyers</th>
                    <th>Created At</th>
                    <th>Action</th>
                    <th>Assign Lawyers</th>
                <?php endif; ?>
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
                    <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['matter_title']); ?></td>
                    <td>
                        <?php
                        // Fetch assigned lawyers for this case
                        $assigned_lawyers = fetchAssignedLawyersForCase($conn, $row['case_id'], $key, $method);
                        if (!empty($assigned_lawyers)) {
                            foreach ($assigned_lawyers as $lawyer) {
                                echo htmlspecialchars($lawyer['first_name'] . ' ' . $lawyer['last_name']) . "<br>";
                            }
                        } else {
                            echo "No lawyers assigned.";
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td>
                        <!-- View Case Details Link -->
                        <a href="view_case_details.php?case_id=<?php echo $row['case_id']; ?>">View Details</a>
                        <!-- Edit Case Link (Only for Admins and Partners) -->
                        <?php if ($usertype == 0 || $usertype == 1): ?>
                            | <a href="edit_case_page.php?case_id=<?php echo $row['case_id']; ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" action="view_cases_page.php" style="display: inline;">
                            <input type="hidden" name="case_id" value="<?php echo $row['case_id']; ?>">
                            <select name="lawyer_ids[]" multiple required>
                                <?php
                                $lawyers = fetchAndDecryptLawyers($conn, $key, $method);
                                foreach ($lawyers as $lawyer): ?>
                                    <option value="<?php echo $lawyer['id']; ?>">
                                        <?php echo htmlspecialchars($lawyer['first_name'] . ' ' . $lawyer['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="assign_lawyers">Assign</button>
                        </form>
                    </td>
                    
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>