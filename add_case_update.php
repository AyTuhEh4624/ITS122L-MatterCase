<?php

include 'config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $case_id = $_POST['case_id'];
    $update_text = $_POST['update_text'];
    $updated_by = $_POST['updated_by'];
    
    $sql = "INSERT INTO case_updates (case_id, update_text, updated_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $case_id, $update_text, $updated_by);
    
    if ($stmt->execute()) {
        echo "Case update added successfully";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}
$conn->close();
?>