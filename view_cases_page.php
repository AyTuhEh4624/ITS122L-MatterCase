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
    
    // Fetch assigned lawyers
    $case_id = $row['case_id'];
    $lawyers_query = "
        SELECT u.username 
        FROM users u 
        JOIN case_lawyers cl ON u.id = cl.lawyer_id 
        WHERE cl.case_id = $case_id
    ";
    
    $lawyers_result = $conn->query($lawyers_query);
    $lawyers = [];
    while ($lawyer = $lawyers_result->fetch_assoc()) {
        $lawyers[] = $lawyer['username'];
    }
    
    $row['lawyers'] = implode(", ", $lawyers);
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
    <title>View Cases</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">View <span class="text-green-400">Cases</span></span>
    
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
                    <a href="add_case_page.php">
                        <button class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12">Add New Case</button>
                    </a>
                <?php endif; ?>

                <div class="overflow-x-auto mt-4">
                    <table class="mx-auto w-[100%] border-collapse border border-gray-500">
                        <thead>
                            <tr class="bg-gray-800 text-white">
                                <th class="border border-gray-500 px-4 py-2">Case ID</th>
                                <th class="border border-gray-500 px-4 py-2">Case Title</th>
                                <th class="border border-gray-500 px-4 py-2">Court</th>
                                <th class="border border-gray-500 px-4 py-2">Case Type</th>
                                <th class="border border-gray-500 px-4 py-2">Status</th>
                                <th class="border border-gray-500 px-4 py-2">Created At</th>
                <th>Assigned Lawyers</th>
                                <th class="border border-gray-500 px-4 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                                <tr class="bg-gray-700 text-gray-300">
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($row['case_id']); ?></td>
                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($row['case_title']); ?></td>
                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($row['court']); ?></td>
                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($row['case_type']); ?></td>
                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($row['status']); ?></td>
                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($row['lawyers']); ?></td>
                    <td class="border border-gray-500 px-4 py-2">
                        <!-- View Case Details Link -->
                        <a href="view_case_details.php?case_id=<?php echo $row['case_id']; ?>" class="text-blue-400">View Details</a>
                        <!-- Edit Case Link (Only for Admins and Partners) -->
                        <?php if ($usertype == 0 || $usertype == 1): ?>
                            | <a href="edit_case_page.php?case_id=<?php echo $row['case_id']; ?>" class="text-green-400">Edit</a>
                        <?php endif; ?>
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
