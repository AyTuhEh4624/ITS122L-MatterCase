<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php"); // Include decryption function

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['id'];
$usertype = $_SESSION['usertype'];

// Restrict access to Admins and Partners only
if ($usertype != 0 && $usertype != 1) {
    header('Location: view_cases_page.php');
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $case_title = $_POST['case_title'];
    $court = $_POST['court'];
    $case_type = $_POST['case_type'];
    $status = $_POST['status'];
    $client_id = $_POST['client_id'];
    $matter_id = $_POST['matter_id'];

    // Encrypt the case title and court
    $encryptedCaseTitle = encryptData($case_title, $key, $method);
    $encryptedCourt = encryptData($court, $key, $method);

    // Insert the new case into the database
    $stmt = $conn->prepare("INSERT INTO cases (case_title, court, case_type, status, client_id, matter_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $encryptedCaseTitle, $encryptedCourt, $case_type, $status, $client_id, $matter_id);

    if ($stmt->execute()) {
        // Redirect back to the view cases page with a success message
        header('Location: view_cases_page.php?success=1');
        exit();
    } else {
        // Redirect back to the add case page with an error message
        header('Location: add_case_page.php?error=1');
        exit();
    }

    $stmt->close();
    $conn->close();
}

// Fetch clients and matters for dropdowns
$clients = $conn->query("SELECT client_id, client_name FROM clients");
$matters = $conn->query("SELECT matter_id, title FROM matters");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Case</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Add <span class="text-green-400">Case</span></span>
    
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

    <div class="flex justify-center items-center mt-4">

    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Case added successfully!</p>
    <?php elseif (isset($_GET['error'])): ?>
        <p style="color: red;">Failed to add case. Please try again.</p>
    <?php endif; ?>

    <form action="add_case_page.php" method="POST">
        <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">
        <table border="0" class="mx-auto text-left">
            <tr> 
                <td>Case Title</td>
                <td><input type="text" id="case_title" name="case_title" class="w-full text-black" required></td>
            </tr>
            <tr> 
                <td>Court</td>
                <td><input type="text" id="court" name="court" class="w-full text-black" required></td>
            </tr>
            <tr> 
                <td>Case Type</td>
                <td><input type="text" id="case_type" name="case_type" class="w-full text-black" required></td>
            </tr>
            <tr> 
                <td>Status</td>
                <td><select id="status" name="status" class="w-full text-black" required>
            <option value="Active">Active</option>
            <option value="Dismissed">Dismissed</option>
            <option value="Closed">Closed</option>
        </select></td>
            </tr>
            <tr> 
                <td>Client</td>
                <td><select id="client_id" name="client_id" class="w-full text-black" required>
            <?php while ($client = $clients->fetch_assoc()): ?>
                <option value="<?php echo $client['client_id']; ?>">
                    <?php echo htmlspecialchars(decryptData($client['client_name'], $key, $method)); ?>
                </option>
            <?php endwhile; ?>
        </select></td>
            </tr>
            <tr> 
                <td>Matter</td>
                <td><select id="matter_id" name="matter_id" class="w-full text-black" required>
            <?php while ($matter = $matters->fetch_assoc()): ?>
                <option value="<?php echo $matter['matter_id']; ?>">
                    <?php echo htmlspecialchars(decryptData($matter['title'], $key, $method)); ?>
                </option>
            <?php endwhile; ?>
        </select></td>
            </tr>
            <tr> 
                <td colspan="2"><button type="submit" class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12">Add Fee</button></td>
            </tr>
            <tr> 
                <td colspan="2"><a href="view_cases_page.php"><button class="bg-gray-700 text-white-900 font-semibold py-3 rounded-lg shadow-md w-full h-12">Back to View Cases</button></a></td>
            </tr>
        </table>
    </form>
        </div>
    </div>
</div>
</body>
</html>