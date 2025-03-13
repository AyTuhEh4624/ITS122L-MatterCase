<?php
session_start();

// Include the encryption file
include_once("encryption.php");
include_once("decrypt.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $pass = $_POST['pass'];

    // Database credentials
    $host = 'localhost';
    $db_username = 'root';
    $db_pass = '';
    $database = 'mattercase';

    // Connect to the database
    $conn = mysqli_connect($host, $db_username, $db_pass, $database);

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
        // Decrypt the stored username (email) and pass
        $stored_encrypted_username = $row['email'];
        $stored_encrypted_pass = $row['pass'];

        $decrypted_username = decryptData($stored_encrypted_username, $key, $method);
        $decrypted_pass = decryptData($stored_encrypted_pass, $key, $method);

        // Check if the decrypted username and pass match the input
        if ($username === $decrypted_username && $pass === $decrypted_pass) {
            $login_successful = true;
            $_SESSION['username'] = $username;
            $_SESSION['id'] = $row['id'];

            // Check usertype
            //0 - admin
            //1 - partner
            //2 - lawyer
            //3 - paralegal
            //4 - messenger
            switch($row["usertype"]){
                case 0:
                    header('Location: dashboard_admin.php');
                    exit();
                    break;
                case 1:
                    header('Location: dashboard_partner.php');
                    exit();
                    break;
                case 2:
                    header('Location: dashboard_lawyer.php');
                    exit();
                    break;
                case 3:
                    header('Location: dashboard_paralegal.php');
                    exit();
                    break;
                case 4:
                    header('Location: dashboard_messenger.php');
                    exit();
                    break;
                default:
                    echo "Invalid user type.";
                    break;
            }
        }
    }

    if (!$login_successful) {
        echo "Invalid username or pass.";
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
        <input type="password" name="pass"><br><br>
        <input type="submit" value="Login"><br><br>
    </form>
</body>
</html>