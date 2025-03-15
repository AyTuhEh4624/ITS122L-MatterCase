
<?php
// Include the login logic
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/login.php");
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	
    <title>Login</title>
    <link rel="stylesheet" href="FrontEndTrial/css/login_page.css">
</head>
<body>
	
	
	<img src="FrontEndTrial\img\logo1.png" alt="MatterCase Logo">
	
    <!-- Content -->
    <div class="login">
        <!-- Title -->
        <h1>Login</h1>

        <!-- Form -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">

            <!-- Email Input -->
            <div class="input-container">
                <input type="text" id="email" name="username" placeholder="Email">
            </div>

            <!-- Password Input -->
            <div class="input-container">
                <input type="password" id="password" name="pass" placeholder="Password">
            </div>

            <!-- Submit Button -->
            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>