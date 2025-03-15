<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/config.php");
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
    header('Location: view_cases_page.php');
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch client data to edit
$client_id = $_GET['client_id'] ?? null;
if (!$client_id) {
    echo "Client ID not provided.";
    exit();
}

// Fetch client data
$query = "SELECT * FROM clients WHERE client_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client_data = $result->fetch_assoc();

if (!$client_data) {
    echo "Client not found.";
    exit();
}

// Decrypt client data
$client_name = decryptData($client_data['client_name'], $key, $method);
$email = decryptData($client_data['email'], $key, $method);
$address = decryptData($client_data['address'], $key, $method);
$profile_picture = decryptData($client_data['profile_picture'], $key, $method);

// Handle form submission
if (isset($_POST['update'])) {
    $new_client_name = $_POST['client_name'];
    $new_email = $_POST['email'];
    $new_address = $_POST['address'];
    $new_profile_picture = $_POST['profile_picture'];

    // Check if the new email is unique (if it has changed)
    if ($new_email !== $email && !isEmailUnique($conn, $new_email, $key, $method)) {
        echo "Error: Email already exists. Please use a different email address.";
    } else {
        // Compare old and new data to log changes
        $changes = [];
        if ($new_client_name !== $client_name) {
            $changes[] = "Client Name: '$client_name' to '$new_client_name'";
        }
        if ($new_email !== $email) {
            $changes[] = "Email: '$email' to '$new_email'";
        }
        if ($new_address !== $address) {
            $changes[] = "Address: '$address' to '$new_address'";
        }
        if ($new_profile_picture !== $profile_picture) {
            $changes[] = "Profile Picture: Updated";
        }

        // Log changes if any
        if (!empty($changes)) {
            $action = "Updated client ID $client_id: " . implode(", ", $changes);
            logAction($conn, $user_id, $action, $key, $method);
        }

        // Encrypt new data
        $encrypted_client_name = encryptData($new_client_name, $key, $method);
        $encrypted_email = encryptData($new_email, $key, $method);
        $encrypted_address = encryptData($new_address, $key, $method);
        $encrypted_profile_picture = encryptData($new_profile_picture, $key, $method);

        // Update the client data in the database
        $update_query = "
            UPDATE clients 
            SET client_name = ?, 
                email = ?, 
                address = ?, 
                profile_picture = ? 
            WHERE client_id = ?
        ";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param(
            "ssssi",
            $encrypted_client_name,
            $encrypted_email,
            $encrypted_address,
            $encrypted_profile_picture,
            $client_id
        );

        if ($stmt->execute()) {
            echo "Client updated successfully.";
        } else {
            echo "Error updating client.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Client</title>
</head>
<body>
    <h1>Edit Client</h1>

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
                default:
                    echo 'login_page.php'; // Fallback to login page
                    break;
            }
        ?>">Back to Dashboard</a>
    </p>

    <!-- Edit Client Form -->
    <form name="update_client" method="post" action="edit_client_page.php?client_id=<?php echo $client_id; ?>">
        <table border="0">
            <tr> 
                <td>Client Name</td>
                <td><input type="text" name="client_name" value="<?php echo htmlspecialchars($client_name); ?>"></td>
            </tr>
            <tr> 
                <td>Email</td>
                <td><input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>"></td>
            </tr>
            <tr> 
                <td>Address</td>
                <td><input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>"></td>
            </tr>
            <tr> 
                <td>Profile Picture</td>
                <td><input type="text" name="profile_picture" value="<?php echo htmlspecialchars($profile_picture); ?>"></td>
            </tr>
            <tr>
                <td><input type="hidden" name="client_id" value="<?php echo $client_id; ?>"></td>
                <td><input type="submit" name="update" value="Update"></td>
            </tr>
        </table>
    </form>
</body>
</html>