<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php"); 
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/audit_log.php"); 

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['id'];
$usertype = $_SESSION['usertype'];

// Restrict access to Admins and Partners only
if ($usertype != 0 && $usertype != 1) {
    header('Location: view_clients_page.php');
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all matters for the multi-select dropdown
$matters_query = "SELECT matter_id, title FROM matters";
$matters_result = $conn->query($matters_query);
$matters = $matters_result->fetch_all(MYSQLI_ASSOC);

// Decrypt matter titles for display
foreach ($matters as &$matter) {
    $matter['title'] = decryptData($matter['title'], $key, $method);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $client_name = $_POST['client_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $profile_picture = $_POST['profile_picture'];
    $matter_ids = $_POST['matter_ids']; // Array of selected matter IDs

    // Encrypt sensitive data
    $encrypted_client_name = encryptData($client_name, $key, $method);
    $encrypted_email = encryptData($email, $key, $method);
    $encrypted_address = encryptData($address, $key, $method);
    $encrypted_profile_picture = encryptData($profile_picture, $key, $method);

    // Insert the new client into the database
    $stmt = $conn->prepare("INSERT INTO clients (client_name, email, address, profile_picture) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $encrypted_client_name, $encrypted_email, $encrypted_address, $encrypted_profile_picture);

    if ($stmt->execute()) {
        $client_id = $stmt->insert_id; // Get the ID of the newly inserted client

        // Log the action in the audit log
        $action = "Added new client, Client ID: $client_id, Client name: $client_name, Related matters IDs: $matter_ids";
        logAction($conn, $user_id, $action, $key, $method);
        // Insert selected matters into the client_matters table
        if (!empty($matter_ids)) {
            $insert_matters_stmt = $conn->prepare("INSERT INTO client_matters (client_id, matter_id) VALUES (?, ?)");
            foreach ($matter_ids as $matter_id) {
                $insert_matters_stmt->bind_param("ii", $client_id, $matter_id);
                $insert_matters_stmt->execute();
            }
            $insert_matters_stmt->close();
        }

        // Redirect back to the view clients page with a success message
        header('Location: view_clients_page.php?success=1');
        exit();
    } else {
        // Redirect back to the add client page with an error message
        header('Location: add_client_page.php?error=1');
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Client</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Add <span class="text-green-400">Client</span></span>
    
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
        <p style="color: green;">Client added successfully!</p>
    <?php elseif (isset($_GET['error'])): ?>
        <p style="color: red;">Failed to add client. Please try again.</p>
    <?php endif; ?>
        
    <!-- Form to Add a New Case Update -->
    <form action="add_client_page.php" method="POST">
        <table border="0" class="mx-auto text-left">
            <tr> 
                <td>Client Name</td>
                <td><input type="text" id="client_name" name="client_name" class="w-full text-black" required></td>
            </tr>
            <tr> 
                <td>Email</td>
                <td><input type="email" id="email" name="email" class="w-full text-black" required></td>
            </tr>
            <tr> 
                <td>Address</td>
                <td><textarea id="address" name="address" class="w-full text-black" required></textarea></td>
            </tr>
            <tr> 
                <td>Profile Picture URL</td>
                <td><input type="text" id="profile_picture" name="profile_picture" class="w-full text-black"></td>
            </tr>
            <tr> 
                <td>Select Matters</td>
                <td><select id="matter_ids" name="matter_ids[]"  class="w-full text-black" multiple>
            <?php foreach ($matters as $matter): ?>
                <option value="<?php echo $matter['matter_id']; ?>">
                    <?php echo htmlspecialchars($matter['title']); ?>
                </option>
            <?php endforeach; ?>
        </select></td>
            </tr>
            <tr> 
                <td colspan="2"><button type="submit" class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12">Add Client</button></td>
            </tr>
            <tr> 
                <td colspan="2"><a href="view_case_details.php?case_id=<?php echo $case_id; ?>"><button class="bg-gray-700 text-white-900 font-semibold py-3 rounded-lg shadow-md w-full h-12">Back to View Clients</button></a></td>
            </tr>
        </table>
    </form>
        </div>
    </div>
</div>
</body>
</html>