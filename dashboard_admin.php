<?php
session_start();

// usertype check
if (!isset($_SESSION['username']) || $_SESSION['usertype'] != 0) {
    header('Location: login_page.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>  
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <a href="view_users_admin.php" class="button">View Users</a>
    <a href="audit_log_page.php" class="button">View Audit Log</a>
    <a href="edit_profile_page.php">Edit My Profile</a>
    <a href="view_matters_page.php">View Matters</a>
    <a href="view_clients_page.php">View Clients</a>
    
    <a href="logout.php">Log out </a>
</body>
</html>