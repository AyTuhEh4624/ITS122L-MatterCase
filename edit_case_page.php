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
    <title>Edit Client</title>
    <style>
        form {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Edit Client</h1>

    <!-- Back to Dashboard Button -->
    <p>
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
        ?>">Back to Dashboard</a>
    </p>

    <!-- Edit Client Form -->
    <form method="POST" action="">
        <label for="client_name">Client Name:</label>
        <input type="text" id="client_name" name="client_name" value="<?php echo htmlspecialchars($client['client_name']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" required>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($client['address']); ?>" required>

        <input type="submit" value="Update Client">
    </form>
</body>
</html>