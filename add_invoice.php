<?php

include 'config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = $_POST['client_id'];
    $case_id = $_POST['case_id'];
    $amount = $_POST['amount'];
    $status = $_POST['status'];
    $due_date = $_POST['due_date'];
    
    $sql = "INSERT INTO invoices (client_id, case_id, amount, status, due_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $client_id, $case_id, $amount, $status, $due_date);
    
    if ($stmt->execute()) {
        echo "Invoice added successfully";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}
$conn->close();
?>