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

// Fetch all clients with their associated matters
$query = "
    SELECT c.client_id, 
           c.client_name, 
           c.email, 
           c.address, 
           c.profile_picture, 
           c.created_at,
           GROUP_CONCAT(m.title SEPARATOR ', ') AS matter_names
    FROM clients c
    LEFT JOIN client_matters cm ON c.client_id = cm.client_id
    LEFT JOIN matters m ON cm.matter_id = m.matter_id
    GROUP BY c.client_id
";
$result = $conn->query($query);
$data = $result->fetch_all(MYSQLI_ASSOC);

// Decrypt client data and matter names
foreach ($data as &$row) {
    // Decrypt client data
    $row['client_name'] = decryptData($row['client_name'], $key, $method);
    $row['email'] = decryptData($row['email'], $key, $method);
    $row['address'] = decryptData($row['address'], $key, $method);
    $row['profile_picture'] = decryptData($row['profile_picture'], $key, $method);

    // Decrypt matter names if they exist
    if (!empty($row['matter_names'])) {
        $matter_names = explode(', ', $row['matter_names']);
        $decrypted_matter_names = array_map(function ($name) use ($key, $method) {
            return decryptData($name, $key, $method);
        }, $matter_names);
        $row['matter_names'] = implode(', ', $decrypted_matter_names);
    }
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
                    <?php foreach ($row as $key => $value): ?>
                        <td>
                            <?php 
                            // Display matter names as a comma-separated list
                            if ($key === 'matter_names') {
                                echo htmlspecialchars($value ? $value : 'No matters assigned');
                            } else {
                                echo htmlspecialchars($value);
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                    <td>
                        <!-- View Client Details Link -->
                        <a href="view_cases_page.php?client_id=<?php echo $row['client_id']; ?>">View Details</a>
                        <!-- Edit Client Link (Only for Admins and Partners) -->
                        <?php if ($usertype == 0 || $usertype == 1): ?>
                            | <a href="edit_client_page.php?client_id=<?php echo $row['client_id']; ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>