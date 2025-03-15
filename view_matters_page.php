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

// Fetch matters based on user role
if ($usertype == 0 || $usertype == 1) {
    // Admin and Partner can see all matters
    $query = "SELECT * FROM matters";
} elseif ($usertype == 2) {
    // Lawyers can only see matters assigned to them
    $query = "
        SELECT DISTINCT m.* 
        FROM matters m
        JOIN cases c ON m.matter_id = c.matter_id
        JOIN case_lawyers cl ON c.case_id = cl.case_id
        WHERE cl.lawyer_id = $user_id
    ";
}

$result = $conn->query($query);
$data = $result->fetch_all(MYSQLI_ASSOC);

// Decrypt the title and description fields
foreach ($data as &$row) {
    $row['title'] = decryptData($row['title'], $key, $method);
    $row['description'] = decryptData($row['description'], $key, $method);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex justify-between items-center">
            <span class="text-lg">Client <span class="text-green-400">Matters</span></span>
            <a href="logout.php"><button class="text-gray-300">Logout</button></a>
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
        ?>"><button class="text-gray-300">Dashboard</button></a>
        </div>

        <!-- Main Content -->
        <div class="flex-grow flex justify-center mt-2">
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[90%]">
            <?php if ($usertype == 0 || $usertype == 1): ?>
        <a href="add_matter_page.php"><button class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12">Add New Matter</button></a>
    <?php endif; ?>
            <table width="90%">
        <thead>
            <tr>
                <?php if (!empty($data)): ?>
                    <?php foreach ($data[0] as $key => $value): ?>
                        <th><?php echo htmlspecialchars($key); ?></th>
                    <?php endforeach; ?>
                <?php endif; ?>
                <th>Edit Matter</th>
                <th>View Cases</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <?php foreach ($row as $key => $value): ?>
                        <td>
                            <?php 
                            // Display decrypted title and description
                            echo htmlspecialchars($value);
                            ?>
                        </td>
                    <?php endforeach; ?>
                    <td>
                        <a href="edit_matter_page.php?matter_id=<?php echo $row['matter_id']; ?>">Edit</a>
                    </td>
                    <td>
                        <a href="view_cases_page.php?matter_id=<?php echo $row['matter_id']; ?>">View Cases</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
            </div>
        </div>
    </div>
</body>
</html>