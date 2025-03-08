<?php
session_start();

// Include the encryption file
include_once("encryption.php");
include_once("decrypt.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Database credentials
    $host = 'localhost';
    $db_username = 'root';
    $db_password = '';
    $database = 'mattercase';

    // Connect to the database
    $conn = mysqli_connect($host, $db_username, $db_password, $database);

    // Check for errors
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit();
    }

    // Fetch all users from the database
    $query = "SELECT * FROM users";
    $result = mysqli_query($conn, $query);

    $login_successful = false;

    // Loop through all users to find a match
    while ($row = mysqli_fetch_assoc($result)) {
        // Decrypt the stored username (email) and password
        $stored_encrypted_username = $row['email'];
        $stored_encrypted_password = $row['password'];

        $decrypted_username = decryptData($stored_encrypted_username, $key, $method);
        $decrypted_password = decryptData($stored_encrypted_password, $key, $method);

        // Check if the decrypted username and password match the input
        if ($username === $decrypted_username && $password === $decrypted_password) {
            $login_successful = true;

            // Check if the user is an admin
            if ($row["is_admin"] == 1) {
                $_SESSION['username'] = $username;
                header('Location: viewusers.php');
                exit();
            } else {
                $_SESSION['username'] = $username;
                header('Location: dashboard.php'); // Replace with your user dashboard page
                exit();
            }
        }
    }

    if (!$login_successful) {
        echo "Invalid username or password.";
    }

    // Close the database connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>  
    <title>Login</title>
</head>
<body>
    <h1>Login Now</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <label>Email:</label>
        <input type="text" name="username"><br><br>
        <label>Password:</label>
        <input type="password" name="password"><br><br>
        <input type="submit" value="Login"><br><br>
    </form>
</body>
</html>