<?php

include 'config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $case_id = $_POST['case_id'];
    $evidence_type = $_POST['evidence_type'];
    $description = $_POST['description'];
    $file_path = "uploads/" . basename($_FILES["file"]["name"]);
    
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $file_path)) {
        $sql = "INSERT INTO evidence (case_id, evidence_type, file_path, description) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $case_id, $evidence_type, $file_path, $description);
        
        if ($stmt->execute()) {
            echo "Evidence uploaded successfully";
        } else {
            echo "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "File upload failed";
    }
}
$conn->close();
?>