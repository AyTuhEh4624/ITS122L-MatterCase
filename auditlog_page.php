<?php
session_start();
include_once("config.php"); // Include the database connection file

// Check if the user is logged in (optional, for security)
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Fetch audit log data from the database
$query = "SELECT audit_log.*, users.username 
          FROM audit_log 
          LEFT JOIN users ON audit_log.user_id = users.id 
          ORDER BY audit_log.timestamp DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching audit log: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log</title>
</head>
<body>
    <h1>Audit Log</h1>
    <a href="dashboard_admin.php">Back to Dashboard</a>
    <br><br>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Username</th>
                <th>Action</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['action']); ?></td>
                    <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>