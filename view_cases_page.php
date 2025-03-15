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

// Fetch cases based on matter_id or client_id
if (isset($_GET['matter_id'])) {
    $matter_id = $_GET['matter_id'];
    $query = "SELECT * FROM cases WHERE matter_id = $matter_id";
} elseif (isset($_GET['client_id'])) {
    $client_id = $_GET['client_id'];
    $query = "SELECT * FROM cases WHERE client_id = $client_id";
} else {
    // Paralegals and Messengers start here
    $query = "SELECT * FROM cases";
}

$result = $conn->query($query);
$data = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cases</title>
</head>
<body>
    <h1>Cases</h1>
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
    <table border="1">
        <thead>
            <tr>
                <?php if (!empty($data)): ?>
                    <?php foreach ($data[0] as $key => $value): ?>
                        <th><?php echo htmlspecialchars($key); ?></th>
                    <?php endforeach; ?>
                <?php endif; ?>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <?php foreach ($row as $value): ?>
                        <td><?php echo htmlspecialchars($value); ?></td>
                    <?php endforeach; ?>
                    <td>
                        <a href="view_case_details.php?case_id=<?php echo $row['case_id']; ?>">View Case Details</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>