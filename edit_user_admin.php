<?php
session_start();
include_once("config.php");
include_once("encryption.php");
include_once("decrypt.php");
include_once("audit_log.php"); 
include_once("email_unique.php"); 

// Check if form is submitted for user update
if (isset($_POST['update'])) {
    $id = $_POST['id'];

    // Fetch the old user data
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
    if ($result && mysqli_num_rows($result) > 0) {
        $old_data = mysqli_fetch_assoc($result);

        // Decrypt the old data
        $old_first_name = decryptData($old_data['first_name'], $key, $method);
        $old_last_name = decryptData($old_data['last_name'], $key, $method);
        $old_email = decryptData($old_data['email'], $key, $method);
        $old_pass = decryptData($old_data['pass'], $key, $method);
        $old_username = $old_data['username']; // Username is not encrypted
    } else {
        echo "User not found.";
        exit();
    }

    // Retrieve and sanitize new form data
    $new_first_name = $_POST['first_name'];
    $new_last_name = $_POST['last_name'];
    $new_email = $_POST['email'];
    $new_username = $_POST['username'];
    $new_pass = $_POST['pass'];
    if (!isEmailUnique($conn, $email, $key, $method)) {
        echo "Error: Email already exists. Please use a different email address.";
    } else {
        // Encrypt the new data
        $encrypted_first_name = encryptData($new_first_name, $key, $method);
        $encrypted_last_name = encryptData($new_last_name, $key, $method);
        $encrypted_email = encryptData($new_email, $key, $method);
        $encrypted_pass = encryptData($new_pass, $key, $method);

        // Compare old and new data to log changes
        $changes = [];
        if ($new_first_name !== $old_first_name) {
            $changes[] = "First Name: Updated";
        }
        if ($new_last_name !== $old_last_name) {
            $changes[] = "Last Name: Updated";
        }
        if ($new_email !== $old_email) {
            $changes[] = "Email: Updated";
        }
        if ($new_username !== $old_username) {
            $changes[] = "Username: '$old_username' -> '$new_username'";
        }
        if ($new_pass !== $old_pass) {
            $changes[] = "Password: Updated"; 
        }

        // Log changes if any
        if (!empty($changes)) {
            $action = "Updated user ID $id: " . implode(", ", $changes);
            logAction($conn, $_SESSION['id'], $action); 
        }

        // Update the user data in the database
        $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, username=?, pass=? WHERE id=?");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sssssi", $encrypted_first_name, $encrypted_last_name, $encrypted_email, $new_username, $encrypted_pass, $id);

        if ($stmt->execute()) {
            header("Location: viewusers.php");
            exit();
        } else {
            echo "Error updating record: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Display selected user data based on id
$id = $_GET['id'];

// Fetch user data based on id
$result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");

if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_array($result);

    // Decrypt the data
    $first_name = decryptData($user_data['first_name'], $key, $method);
    $last_name = decryptData($user_data['last_name'], $key, $method);
    $email = decryptData($user_data['email'], $key, $method);
    $username = $user_data['username'];
    $pass = decryptData($user_data['pass'], $key, $method);
} else {
    echo "User not found.";
    exit();
}
?>

<html>
<head>	
    <title>Edit User Data</title>
</head>
<body>
    <a href="viewusers.php">Home</a>
    <br/><br/>
    
    <form name="update_user" method="post" action="edit.php">
        <table border="0">
            <tr> 
                <td>First Name</td>
                <td><input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>"></td>
            </tr>
            <tr> 
                <td>Last Name</td>
                <td><input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>"></td>
            </tr>
            <tr> 
                <td>Email</td>
                <td><input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>"></td>
            </tr>
            <tr> 
                <td>Username</td>
                <td><input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>"></td>
            </tr>
            <tr> 
                <td>Password</td>
                <td><input type="text" name="pass" value="<?php echo htmlspecialchars($pass); ?>"></td>
            </tr>
            <tr>
                <td><input type="hidden" name="id" value="<?php echo $_GET['id']; ?>"></td>
                <td><input type="submit" name="update" value="Update"></td>
            </tr>
        </table>
    </form>
</body>
</html>