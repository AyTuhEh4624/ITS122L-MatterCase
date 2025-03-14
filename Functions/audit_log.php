<?php
// Include the encryption file
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php");

function logAction($conn, $user_id, $action, $key, $method) {
    // Encrypt the action before storing it
    $encrypted_action = encryptData($action, $key, $method);

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, action) VALUES (?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters and execute the statement
    $stmt->bind_param("is", $user_id, $encrypted_action);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }

    $stmt->close();
}
?>