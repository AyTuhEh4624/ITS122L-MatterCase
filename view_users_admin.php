<?php
// Create database connection using config file
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/config.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php");

// Fetch all users data from database
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">View <span class="text-green-400">Users</span></span>
    
            <div class="ml-auto flex space-x-4">
                <a href="logout.php"><button class="text-gray-300">Logout</button></a>
                <a href="dashboard_admin.php">
                <button class="text-gray-300">Dashboard</button>
            </a>
        </div>
    </div>


        <!-- Main Content -->
    <div class="flex-grow flex justify-center mt-4">
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[90%] items-center">
        <a href="add_user_page.php"><button class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12 mb-6">Add New User</button></a>


        <div class="overflow-x-auto mt-4">
            <table class="mx-auto w-[100%] border-collapse border border-gray-500">
            <thead>
            <tr class="bg-gray-800 text-white">
            <th>Firstname</th> 
            <th>Lastname</th> 
            <th>Username</th> 
            <th>Email</th> 
            <th>Operations</th>
        </tr>
            </thead>
        <?php  
        while($user_data = mysqli_fetch_array($result)) {         
            // Decrypt the data
            $firstname = decryptData($user_data['first_name'], $key, $method);
            $lastname = decryptData($user_data['last_name'], $key, $method);
            $username = $user_data['username'];
            $email = decryptData($user_data['email'], $key, $method);

            echo "<tr>";
            echo "<td>".$firstname."</td>";
            echo "<td>".$lastname."</td>";
            echo "<td>".$username."</td>";
            echo "<td>".$email."</td>";    
            echo "<td>
                    <a href='edit_profile_page.php?id=$user_data[id]' class='text-green-400'>Edit</a> | 
                    <a href='delete_user.php?id=$user_data[id]' class='text-red-400'>Delete</a>
                  </td></tr>";        
        }
        ?>
    </table>
        </div>
    </div>
</div>
</body>