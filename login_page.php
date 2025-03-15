
<?php
// Include the login logic
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/login.php");
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