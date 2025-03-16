<?php
session_start();

// Include the encryption file
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/audit_log.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $pass = $_POST['pass'];

    // Database credentials
    $host = 'localhost';
    $db_username = 'root';
    $db_pass = '';
    $database = 'mattercase';

    // Connect to the database
    $conn = mysqli_connect($host, $db_username, $db_pass, $database);

    // Check for errors
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit();
    }

    // Fetch all users from the database
    $query = "SELECT * FROM users";
    $result = mysqli_query($conn, $query);

    $login_successful = false;

    // Loop through all users to find a match
    while ($row = mysqli_fetch_assoc($result)) {
        // Decrypt the stored username (email) and pass
        $stored_encrypted_username = $row['email'];
        $stored_encrypted_pass = $row['pass'];

        $decrypted_username = decryptData($stored_encrypted_username, $key, $method);
        $decrypted_pass = decryptData($stored_encrypted_pass, $key, $method);

        // Check if the decrypted username and pass match the input
        if ($username === $decrypted_username && $pass === $decrypted_pass) {
            $login_successful = true;

            // Store user information in the session
            $_SESSION['username'] = $username;
            $_SESSION['id'] = $row['id'];
            $_SESSION['usertype'] = $row['usertype'];
            $_SESSION['uname'] = $row['username']; // Store the username
            $_SESSION['fname'] = decryptData($row['first_name'],$key, $method); // Store the first name
            $_SESSION['lname'] = decryptData($row['last_name'],$key, $method); // Store the last name

            // Log the login action
            $user_id = $row['id'];
            $action = "User logged in";
            logAction($conn, $user_id, $action, $key, $method);

            switch($row["usertype"]){
                case 0:
                    header('Location: dashboard_admin.php');
                    exit();
                    break;
                case 1:
                    header('Location: dashboard_partner.php');
                    exit();
                    break;
                case 2:
                    header('Location: dashboard_lawyer.php');
                    exit();
                    break;
                case 3:
                    header('Location: dashboard_paralegal.php');
                    exit();
                    break;
                case 4:
                    header('Location: dashboard_messenger.php');
                    exit();
                    break;
                default:
                    echo "Invalid user type.";
                    break;
            }
        }
    }

    if (!$login_successful) {
        echo "Invalid username or pass.";
    }

    // Close the database connection
    mysqli_close($conn);
}
?>