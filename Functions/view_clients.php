<?php

include 'config.php';
$sql = "SELECT * FROM clients";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<p>" . $row["name"] . " - " . $row["email"] . "</p>";
    }
} else {
    echo "No clients found";
}
$conn->close();
?>