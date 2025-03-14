<?php
// Include necessary files
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/audit_log.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/email_unique.php");

function connectToDatabase() {
    // Database credentials
    $host = 'localhost';
    $db_username = 'root';
    $db_pass = '';
    $database = 'mattercase';

    // Connect to the database
    $conn = mysqli_connect($host, $db_username, $db_pass, $database);

    // Check for errors
    if (mysqli_connect_errno()) {
        die("Failed to connect to MySQL: " . mysqli_connect_error());
    }

    return $conn;
}

function fetchUserData($conn, $user_id) {
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

function updateUserProfile($conn, $user_id, $first_name, $last_name, $email, $username, $pass, $key, $method) {
    // Encrypt the new data
    $encrypted_first_name = encryptData($first_name, $key, $method);
    $encrypted_last_name = encryptData($last_name, $key, $method);
    $encrypted_email = encryptData($email, $key, $method);
    $encrypted_pass = encryptData($pass, $key, $method);

    // Update the user data in the database
    $update_query = "UPDATE users SET first_name=?, last_name=?, email=?, username=?, pass=? WHERE id=?";
    $stmt = $conn->prepare($update_query);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssssi", $encrypted_first_name, $encrypted_last_name, $encrypted_email, $username, $encrypted_pass, $user_id);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function isAdmin($usertype) {
    return $usertype == 0 || $usertype == 1;
}

function canEditProfile($logged_in_user_id, $logged_in_usertype, $target_user_id) {
    // Admins can edit any profile, regular users can only edit their own
    return isAdmin($logged_in_usertype) || $logged_in_user_id == $target_user_id;
}
?>