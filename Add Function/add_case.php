<?php
include 'config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matter_id = $_POST['matter_id'];
    $case_title = $_POST['case_title'];
    $court = $_POST['court'];
    $case_type = $_POST['case_type'];
    $status = $_POST['status'];
    
    $sql = "INSERT INTO cases (matter_id, case_title, court, case_type, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $matter_id, $case_title, $court, $case_type, $status);
    
    if ($stmt->execute()) {
        echo "Case added successfully";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}
$conn->close();
?>