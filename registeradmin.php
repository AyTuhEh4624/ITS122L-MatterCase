<?php
// Include the encryption file
include_once("encryption.php");
include_once("config.php");

// Check if the form was submitted
if (isset($_POST["submit"])) {
    // Retrieve the form data
    $is_admin = 1;
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];

    // Encrypt form data using the encryptData function
    $encryptedusername = encryptData($username, $key, $method);
    $encryptedfirstname = encryptData($firstname, $key, $method);
    $encryptedlastname = encryptData($lastname, $key, $method);
    $encryptedpassword = encryptData($password, $key, $method);
    $encryptedemail = encryptData($email, $key, $method);

    // Insert the user into the database
    $sql = "INSERT INTO users (username, is_admin, first_name, last_name, password, email) VALUES ('$encryptedusername', '$is_admin','$encryptedfirstname','$encryptedlastname','$encryptedpassword', '$encryptedemail')";
    if (mysqli_query($conn, $sql)) {
        echo "<b>Registration successful!<b>";
        header('Refresh: 1; URL = login.php');
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

// Close the database connection
mysqli_close($conn);
?>

<html lang="en">  
<head>  
  <meta charset="utf-8">  
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">  
  <title> Admin Registration Form  </title>  
  <style>  
.error {   
color: white;  
    font-family: lato;  
    background: gainsboro;  
    display: inline-block;  
    padding: 2px 10px;  
}  
* {  
    padding: 0;  
    margin: 0;  
    box-sizing: border-box;  
}  
body {  
    margin: 50px auto;  
    text-align: left;  
    width: 800px;  
}  
h1 {  
    font-family: Arial;  
  display: block;  
  font-size: 2rem;  
  font-weight: bold;  
  text-align: center;  
  letter-spacing: 3px;  
  color: darkblue;  
    text-transform: uppercase;  
}  
label {  
    width: 150px;  
    display: inline-block;  
    text-align: left;  
    font-size: 1.5rem;  
    font-family: 'Lato';  
}  
input {  
    border: 1px solid #ccc;  
    font-size: 1.5rem;  
    font-weight: 100;  
    font-family: 'Lato';  
    padding: 10px;  
}  
form {  
    margin: 25px auto;  
    padding: 20px;  
    border: 5px solid #ccc;  
    width: 500px;  
    background: #f3e7e9;  
}  
div.form-element {  
    margin: 20px 0;  
}  
input[type=submit]::after {    
  background: #fff;    
  content: '';    
  position: absolute;    
  z-index: -1;    
}    
input[type=submit] {    
  border: 2px solid;    
  border-radius: 2px;    
  color: ;    
  display: block;    
  font-size: 1em;    
  font-weight: bold;    
  margin: 1em auto;    
  padding: 1em 4em;    
 position: relative;    
  text-transform: uppercase;    
}    
input[type=submit]::before   
{    
  background: #fff;    
  content: '';    
  position: absolute;    
  z-index: -1;    
}    
input[type=submit]:hover {    
  color: #1A33FF;    
}    
</style>  
</head>  
<body>    


<form method="post" action="">
<h1>Admin Registration Form </h1><br><br>

    <label>Username:</label>
    <input type="text" name="username"><br>
    <label>Firstname:</label>
    <input type="text" name="firstname"><br>
    <label>Lastname:</label>
    <input type="text" name="lastname"><br>
    <label>Password:</label>
    <input type="password" name="password"><br>
    <label>Email:</label>
    <input type="email" name="email"><br>
    <input type="submit" name="submit" value="Submit">



</form>

</body>
</head>


