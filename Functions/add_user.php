<?php

include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/config.php"); 
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/audit_log.php"); 
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/email_unique.php"); 

function addUser($conn, $first_name, $last_name, $email, $pass, $usertype, $username, $key, $method, $actor_id) {
    // Check if the email is unique
    if (!isEmailUnique($conn, $email, $key, $method)) {
        return "Error: Email already exists. Please use a different email address.";
    }

    // Encrypt the data
    $encrypted_first_name = encryptData($first_name, $key, $method);
    $encrypted_last_name = encryptData($last_name, $key, $method);
    $encrypted_email = encryptData($email, $key, $method);
    $encrypted_pass = encryptData($pass, $key, $method);

    // SQL injection prevention
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, pass, usertype, username) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        return "Prepare failed: " . $conn->error;
    }

    $stmt->bind_param("ssssis", $encrypted_first_name, $encrypted_last_name, $encrypted_email, $encrypted_pass, $usertype, $username);

    if ($stmt->execute()) {
        $new_user_id = $stmt->insert_id;

        // Log the action in the audit log
        $action = "Added new user with ID: $new_user_id, Username: $username, Usertype: $usertype";
        logAction($conn, $actor_id, $action);

        $stmt->close();
        return "User added successfully. <a href='view_users_admin.php'>View Users</a>";
    } else {
        $error = $stmt->error;
        $stmt->close();
        return "Error: " . $error;
    }
}

?>