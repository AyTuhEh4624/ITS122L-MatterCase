<?php

function logAction($conn, $user_id, $action) {
    // prep statement
    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, action) VALUES (?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("is", $user_id, $action);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }

    $stmt->close();
}
?>

