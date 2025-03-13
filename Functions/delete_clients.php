<?php

include 'config.php';
if (isset($_GET['client_id'])) {
    $client_id = $_GET['client_id'];
    $sql = "DELETE FROM clients WHERE client_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $client_id);
    
    if ($stmt->execute()) {
        echo "Client deleted successfully";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}
$conn->close();
?>