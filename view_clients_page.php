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

// Close the database connection
$conn->close();
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
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">View <span class="text-green-400">Client</span></span>
    
            <div class="ml-auto flex space-x-4">
                <a href="logout.php"><button class="text-gray-300">Logout</button></a>
                <a href="<?php
                // Redirect to the appropriate dashboard based on usertype
                    switch ($usertype) {
                        case 0: echo 'dashboard_admin.php'; break;
                        case 1: echo 'dashboard_partner.php'; break;
                        case 2: echo 'dashboard_lawyer.php'; break;
                        case 3: echo 'dashboard_paralegal.php'; break;
                        case 4: echo 'dashboard_messenger.php'; break;
                        default: echo 'login_page.php'; break;
                    }
                ?>">
                <button class="text-gray-300">Dashboard</button>
            </a>
        </div>
    </div>

        <!-- Main Content -->
    <div class="flex-grow flex justify-center mt-4">
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[90%] items-center">
            <?php if ($usertype == 0 || $usertype == 1): ?>
        <a href="add_client_page.php"><button class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12 mb-6">Add New Client</button></a>
    <?php endif; ?>

        <div class="overflow-x-auto mt-4">
            <table class="mx-auto w-[100%] border-collapse border border-gray-500">
            <thead>
            <tr class="bg-gray-800 text-white">
                <th class="border border-gray-500 px-4 py-2">Client ID</th>
                <th class="border border-gray-500 px-4 py-2">Client Name</th>
                <th class="border border-gray-500 px-4 py-2">Email</th>
                <th class="border border-gray-500 px-4 py-2">Address</th>
                <th class="border border-gray-500 px-4 py-2">Created At</th>
                <th class="border border-gray-500 px-4 py-2">Matters</th>
                <th class="border border-gray-500 px-4 py-2">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?php echo htmlspecialchars($client['client_id']); ?></td>
                    <td><?php echo htmlspecialchars(decryptData($client['client_name'], $key, $method)); ?></td>
                    <td><?php echo htmlspecialchars(decryptData($client['email'], $key, $method)); ?></td>
                    <td><?php echo htmlspecialchars(decryptData($client['address'], $key, $method)); ?></td>
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
                        <a href="view_cases_page.php?client_id=<?php echo $client['client_id']; ?>" class="text-blue-400">View Details</a>
                        <!-- Edit Client Link (Only for Admins and Partners) -->
                        <?php if ($usertype == 0 || $usertype == 1): ?>
                            | <a href="edit_client_page.php?client_id=<?php echo $client['client_id']; ?>" class="text-green-400">Edit</a>
                        <?php endif; ?>
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