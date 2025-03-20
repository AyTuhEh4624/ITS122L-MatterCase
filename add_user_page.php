<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: login.php'); 
    exit();
}

include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/add_user.php");

// Check if the form was submitted
if (isset($_POST['Submit'])) {
    // Retrieve the form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $usertype = $_POST['usertype'];
    $username = $_POST['username'];
    $actor_id = $_SESSION['id'];

    // Call the addUser function
    $result = addUser($conn, $first_name, $last_name, $email, $pass, $usertype, $username, $key, $method, $actor_id);

    // Display the result
    echo $result;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Add <span class="text-green-400">Users</span></span>
    
            <div class="ml-auto flex space-x-4">
                <a href="logout.php"><button class="text-gray-300">Logout</button></a>
                <a href="<?php
                    switch ($usertype) {
                        case 0: echo 'dashboard_admin.php'; break;
                        case 1: echo 'dashboard_partner.php'; break;
                        case 2: echo 'dashboard_lawyer.php'; break;
                        case 3: echo 'dashboard_paralegal.php'; break;
                        case 4: echo 'dashboard_messenger.php'; break;
                        default: echo 'login_page.php'; break;
                    }
                ?>">
                <button class="text-gray-300">Dashboard</button>
                </a>
            </div>
        </div>
    
        <!-- Main Content -->
        <div class="flex-grow flex justify-center mt-4">
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[90%] max-w-md">
                <form action="add_user_page.php" method="post" name="form1" class="space-y-4">
                    <div>
                        <label class="block text-left">First Name</label>
                        <input type="text" name="first_name" class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600">
                    </div>
                    <div>
                        <label class="block text-left">Last Name</label>
                        <input type="text" name="last_name" class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600">
                    </div>
                    <div>
                        <label class="block text-left">Username</label>
                        <input type="text" name="username" class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600">
                    </div>
                    <div>
                        <label class="block text-left">User Type</label>
                        <select name="usertype" id="usertype" class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600">
                            <option value="0">Administrator</option>
                            <option value="1">Partner</option>
                            <option value="2">Lawyer</option>
                            <option value="3">Paralegal</option>
                            <option value="4">Messenger</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-left">Email</label>
                        <input type="text" name="email" class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600">
                    </div>
                    <div>
                        <label class="block text-left">Password</label>
                        <input type="password" name="pass" class="w-full px-3 py-2 rounded bg-gray-800 text-white border border-gray-600">
                    </div>
                    <div>
                        <input type="submit" name="Submit" value="Add" class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full">
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>