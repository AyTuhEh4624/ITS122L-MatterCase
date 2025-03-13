<?php
include_once("decrypt.php");
//for some reason just encrypting input email then comparing it to database natively doesn't work
//this code gets EVERY EMAIL from the database, decrypts them, then comapres them to the input email
function isEmailUnique($conn, $email, $key, $method) {
    $stmt = $conn->prepare("SELECT email FROM users");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    while ($row = $result->fetch_assoc()) {
        $encrypted_email = $row['email'];
        $decrypted_email = decryptData($encrypted_email, $key, $method);

        // Compare the decrypted email with the input email
        if ($decrypted_email === $email) {
            return false; // Email is not unique
        }
    }

    return true; // Email is unique
}
?>

