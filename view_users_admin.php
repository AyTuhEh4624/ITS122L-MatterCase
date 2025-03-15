<?php
// Create database connection using config file
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/config.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php");

// Fetch all users data from database
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>

<html>
<head>    
    <title>View Users</title>
</head>
<body>
    <form method="POST" action="add_user_page.php">
        <input type="submit" name="submit" value="Add New User"> <br /><br />
    </form>

    <table>
        <tr>
            <th>Firstname</th> 
            <th>Lastname</th> 
            <th>Username</th> 
            <th>Email</th> 
            <th>Operations</th>
        </tr>
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
                    <a href='edit_profile_page.php?id=$user_data[id]'>Edit</a> | 
                    <a href='delete_user.php?id=$user_data[id]'>Delete</a>
                  </td></tr>";        
        }
        ?>
    </table>
    <a href="dashboard_admin.php">Back to Dashboard</a>
    <a href="logout.php">Log out</a>
</body>
</html>