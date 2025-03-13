<?php

include 'config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $case_id = $_POST['case_id'];
    $amount = $_POST['amount'];
    $fee_description = $_POST['fee_description'];
    $status = $_POST['status'];
    $due_date = $_POST['due_date'];
    
    $sql = "INSERT INTO case_fees (case_id, amount, fee_description, status, due_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idsss", $case_id, $amount, $fee_description, $status, $due_date);
    
    if ($stmt->execute()) {
        echo "Case fee added successfully";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}
$conn->close();
?>