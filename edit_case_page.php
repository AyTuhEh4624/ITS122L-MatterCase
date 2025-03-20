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

// Check if case_id is provided in the URL
if (!isset($_GET['case_id'])) {
    die("Case ID is missing.");
}

$case_id = $_GET['case_id'];

// Fetch client_id and matter_id from the cases table
$caseQuery = "SELECT client_id, matter_id FROM cases WHERE case_id = $case_id";
$caseResult = $conn->query($caseQuery);

if (!$caseResult) {
    die("Database query failed: " . $conn->error);
}

if ($caseResult->num_rows === 0) {
    die("No case found with the provided ID.");
}

$case = $caseResult->fetch_assoc();
$client_id = $case['client_id'];
$matter_id = $case['matter_id'];

// Fetch client details
$clientQuery = "SELECT * FROM clients WHERE client_id = $client_id";
$clientResult = $conn->query($clientQuery);

if (!$clientResult) {
    die("Database query failed: " . $conn->error);
}

$client = $clientResult->fetch_assoc();

if (!$client) {
    die("No client found with the provided ID.");
}

// Decrypt client data
$client['client_name'] = decryptData($client['client_name'], $key, $method);
$client['email'] = decryptData($client['email'], $key, $method);
$client['address'] = decryptData($client['address'], $key, $method);
$client['profile_picture'] = decryptData($client['profile_picture'], $key, $method);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $client_name = htmlspecialchars($_POST['client_name']);
    $email = htmlspecialchars($_POST['email']);
    $address = htmlspecialchars($_POST['address']);
    $profile_picture = htmlspecialchars($_POST['profile_picture']);

    // Encrypt data before saving to the database
    $client_name_encrypted = encryptData($client_name, $key, $method);
    $email_encrypted = encryptData($email, $key, $method);
    $address_encrypted = encryptData($address, $key, $method);
    $profile_picture_encrypted = encryptData($profile_picture, $key, $method);

    // Update client details in the database
    $updateQuery = "
        UPDATE clients
        SET client_name = '$client_name_encrypted',
            email = '$email_encrypted',
            address = '$address_encrypted',
            profile_picture = '$profile_picture_encrypted'
        WHERE client_id = $client_id
    ";

    if ($conn->query($updateQuery) === TRUE) {
        echo "<p>Client details updated successfully!</p>";
    } else {
        echo "<p>Error updating client details: " . $conn->error . "</p>";
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Client</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Edit <span class="text-green-400">Client</span></span>
    
            <div class="ml-auto flex space-x-4">
                <a href="logout.php"><button class="text-gray-300">Logout</button></a>
                <a href="<?php
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
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[90%] max-w-md">
                <form method="POST" action="" class="space-y-4">
                    <div>
                        <label class="block text-left">Client Name</label>
                        <input type="text" id="client_name" name="client_name" value="<?php echo htmlspecialchars($client['client_name']); ?>" required class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600">
                    </div>
                    <div>
                        <label class="block text-left">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" required class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600">
                    </div>
                    <div>
                        <label class="block text-left">Address</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($client['address']); ?>" required class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600">
                    </div>
                    <div>
                        <input type="submit" value="Update Client" class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full">
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>