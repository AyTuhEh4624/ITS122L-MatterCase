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
if (!$result) {
    die("Database query failed: " . $conn->error);
}

// Fetch and display matters individually
$data = [];
while ($row = $result->fetch_assoc()) {
    // Decrypt the title and description fields
    $row['title'] = decryptData($row['title'], $key, $method);
    $row['description'] = decryptData($row['description'], $key, $method);
    $data[] = $row;
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
            <span class="text-lg">Client <span class="text-green-400">Matters</span></span>
    
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
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[90%]">
                <?php if ($usertype == 0 || $usertype == 1): ?>
                    <a href="add_matter_page.php">
                        <button class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12">Add New Matter</button>
                    </a>
                <?php endif; ?>

                <div class="overflow-x-auto mt-4">
                    <table class="mx-auto w-[100%] border-collapse border border-gray-500">
                        <thead>
                            <tr class="bg-gray-800 text-white">
                                <th class="border border-gray-500 px-4 py-2">Matter ID</th>
                                <th class="border border-gray-500 px-4 py-2">Title</th>
                                <th class="border border-gray-500 px-4 py-2">Description</th>
                                <th class="border border-gray-500 px-4 py-2">Created At</th>
                                <th class="border border-gray-500 px-4 py-2">Edit Matter</th>
                                <th class="border border-gray-500 px-4 py-2">View Cases</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                                <tr class="bg-gray-700 text-gray-300">
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($row['matter_id']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2">
                                        <a href="edit_matter_page.php?matter_id=<?php echo $row['matter_id']; ?>" class="text-blue-400">Edit</a>
                                    </td>
                                    <td class="border border-gray-500 px-4 py-2">
                                        <a href="view_cases_page.php?matter_id=<?php echo $row['matter_id']; ?>" class="text-green-400">View Cases</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>