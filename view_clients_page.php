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

// Restrict access to Paralegals and Messengers
if ($usertype == 3 || $usertype == 4) {
    header('Location: view_cases_page.php');
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all clients
$clientQuery = "SELECT * FROM clients";
$clientResult = $conn->query($clientQuery);

if (!$clientResult) {
    die("Client query failed: " . $conn->error);
}

$clients = $clientResult->fetch_all(MYSQLI_ASSOC);

// Fetch all client-matter relationships
$matterQuery = "
    SELECT cm.client_id, m.title 
    FROM client_matters cm
    LEFT JOIN matters m ON cm.matter_id = m.matter_id
";
$matterResult = $conn->query($matterQuery);

if (!$matterResult) {
    die("Matter query failed: " . $conn->error);
}

$matters = $matterResult->fetch_all(MYSQLI_ASSOC);

// Organize matters by client_id
$mattersByClient = [];
foreach ($matters as $matter) {
    $clientId = $matter['client_id'];
    if (!isset($mattersByClient[$clientId])) {
        $mattersByClient[$clientId] = [];
    }
    $mattersByClient[$clientId][] = $matter['title'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Clients</title>
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
    <h1>View Clients</h1>

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

    <!-- Link to Add Clients Page (Only for Admins and Partners) -->
    <?php if ($usertype == 0 || $usertype == 1): ?>
        <p><a href="add_client_page.php">Add New Client</a></p>
    <?php endif; ?>

    <!-- Display Existing Clients -->
    <h2>Existing Clients</h2>
    <table>
        <thead>
            <tr>
                <th>Client ID</th>
                <th>Client Name</th>
                <th>Email</th>
                <th>Address</th>
                <th>Profile Picture</th>
                <th>Created At</th>
                <th>Matters</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?php echo htmlspecialchars($client['client_id']); ?></td>
                    <td><?php echo htmlspecialchars(decryptData($client['client_name'], $key, $method)); ?></td>
                    <td><?php echo htmlspecialchars(decryptData($client['email'], $key, $method)); ?></td>
                    <td><?php echo htmlspecialchars(decryptData($client['address'], $key, $method)); ?></td>
                    <td><?php echo htmlspecialchars(decryptData($client['profile_picture'], $key, $method)); ?></td>
                    <td><?php echo htmlspecialchars($client['created_at']); ?></td>
                    <td>
                        <?php
                        // Get matters for this client
                        $clientId = $client['client_id'];
                        $clientMatters = $mattersByClient[$clientId] ?? [];
                        $decryptedMatters = array_map(function ($matter) use ($key, $method) {
                            return decryptData($matter, $key, $method);
                        }, $clientMatters);
                        echo htmlspecialchars(implode(', ', $decryptedMatters) ?: 'No matters assigned');
                        ?>
                    </td>
                    <td>
                        <!-- View Client Details Link -->
                        <a href="view_cases_page.php?client_id=<?php echo $client['client_id']; ?>">View Details</a>
                        <!-- Edit Client Link (Only for Admins and Partners) -->
                        <?php if ($usertype == 0 || $usertype == 1): ?>
                            | <a href="edit_client_page.php?client_id=<?php echo $client['client_id']; ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>