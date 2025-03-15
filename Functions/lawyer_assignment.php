<?php
// Function to assign multiple lawyers to a specific case
function assignLawyersToCase($conn, $case_id, $lawyer_ids) {
    foreach ($lawyer_ids as $lawyer_id) {
        // Check if the assignment already exists
        $check_query = "SELECT * FROM case_lawyers WHERE case_id = ? AND lawyer_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ii", $case_id, $lawyer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Insert the assignment
            $insert_query = "INSERT INTO case_lawyers (case_id, lawyer_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ii", $case_id, $lawyer_id);
            $stmt->execute();
        }

        $stmt->close();
    }
}


// Function to fetch and decrypt lawyer names
function fetchAndDecryptLawyers($conn, $key, $method) {
    $lawyers = [];
    $query = "SELECT id, first_name, last_name FROM users WHERE usertype = 2"; // usertype = 2 for lawyers
    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Decrypt first_name and last_name
            $row['first_name'] = decryptData($row['first_name'], $key, $method);
            $row['last_name'] = decryptData($row['last_name'], $key, $method);
            $lawyers[] = $row;
        }
    }

    return $lawyers;
}

// Function to fetch assigned lawyers for a case
function fetchAssignedLawyersForCase($conn, $case_id, $key, $method) {
    $assigned_lawyers = [];
    $query = "
        SELECT u.id, u.first_name, u.last_name
        FROM case_lawyers cl
        JOIN users u ON cl.lawyer_id = u.id
        WHERE cl.case_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $case_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Decrypt lawyer names
        $row['first_name'] = decryptData($row['first_name'], $key, $method);
        $row['last_name'] = decryptData($row['last_name'], $key, $method);
        $assigned_lawyers[] = $row;
    }

    $stmt->close();
    return $assigned_lawyers;
}

?>

