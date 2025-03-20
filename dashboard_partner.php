<?php
session_start();

// usertype check
if (!isset($_SESSION['username']) || $_SESSION['usertype'] != 1) {
    header('Location: login_page.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex justify-between items-center">
            <span class="text-lg">Partner <span class="text-green-400">Dashboard</span></span>
            <div class="flex space-x-4">
                <a href="edit_profile_page.php">
                    <button class="px-4 py-1 bg-gray-200 text-gray-800 rounded text-sm">Edit Profile</button>
                </a>
                <a href="logout.php">
                    <button class="text-gray-300">Logout</button>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow flex items-center justify-center">
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[80%] max-w-2xl">
                <h1 class="text-3xl font-semibold">Welcome!</h1>
                <p class="text-lg text-gray-300 mt-1"><?php echo htmlspecialchars($_SESSION['uname']); ?></p>
                <p class="text-lg text-gray-300 mt-1"><?php echo htmlspecialchars($_SESSION['fname']); ?> <?php echo htmlspecialchars($_SESSION['lname']); ?></p>
                <hr class="my-6 border-gray-600">
                
                <!-- Buttons Section -->
                <div class="grid grid-cols-1 gap-4">
                    <a href="view_clients_page.php" class="w-full">
                        <button class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-16">View Clients</button>
                    </a>
                    <a href="view_matters_page.php" class="w-full">
                        <button class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-16">View Matters</button>
                    </a>
                    <a href="view_users_admin.php" class="w-full">
                        <button class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-16">View Users</button>
                    </a>
                    <a href="audit_log_page.php" class="w-full">
                        <button class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-16">View Audit Log</button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>