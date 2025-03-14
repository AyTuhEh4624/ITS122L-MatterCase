<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/config.php"); 
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php"); 

global $key, $method;


if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}


if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $delete_query = "DELETE FROM audit_log WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $delete_query)) {
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Log entry deleted successfully.'); window.location.href = 'audit_log_page.php';</script>";
        } else {
            echo "<script>alert('Error deleting log entry.');</script>";
        }
        mysqli_stmt_close($stmt);
    }
}


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
    <script>
        function Delete(id) {
                document.getElementById('deleteForm' + id).submit();
        }
    </script>
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
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars(decryptData($row['action'], $key, $method)); ?></td>
                    <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                    <td>
                        <form id="deleteForm<?php echo $row['id']; ?>" method="post">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="button" onclick="Delete(<?php echo $row['id']; ?>)">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>

<?php
mysqli_close($conn);
?>
