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
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com">
        function Delete(id) {
                document.getElementById('deleteForm' + id).submit();
        }
    </script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Audit <span class="text-green-400">Log</span></span>
    
            <div class="ml-auto flex space-x-4">
                <a href="logout.php"><button class="text-gray-300">Logout</button></a>
                <a href="dashboard_admin.php">
                <button class="text-gray-300">Dashboard</button>
            </a>
        </div>
    </div>

<!-- Main Content -->
<div class="flex-grow flex justify-center mt-4">
    <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[90%]">
        <div class="overflow-x-auto">
            <table class="mx-auto w-[100%] border-collapse border border-gray-500">
                <thead>
                    <tr class="bg-gray-800 text-white">
                    <th class="border border-gray-500 px-4 py-2">ID</th>
                <th class="border border-gray-500 px-4 py-2">User ID</th>
                <th class="border border-gray-500 px-4 py-2">Username</th>
                <th class="border border-gray-500 px-4 py-2">Action</th>
                <th class="border border-gray-500 px-4 py-2">Timestamp</th>
                <th class="border border-gray-500 px-4 py-2">Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($row['id']); ?></td>
                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($row['user_id']); ?></td>
                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($row['username'] ?? 'N/A'); ?></td>
                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars(decryptData($row['action'], $key, $method)); ?></td>
                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($row['timestamp']); ?></td>
                    <td  class="border border-gray-500 px-4 py-2">
                        <form id="deleteForm<?php echo $row['id']; ?>" method="post">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="button" onclick="Delete(<?php echo $row['id']; ?>)" class="text-red-400">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
        </div>
    </div>
</div>


</body>
</html>

<?php
mysqli_close($conn);
?>
