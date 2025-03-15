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

<html>
<head>
    <title>Add Users</title>
</head>
<body>
    <a href="login.php">Home</a>
    <br/><br/>

    <form action="add_user_page.php" method="post" name="form1">
        <table width="25%" border="0">
            <tr> 
                <td>FirstName</td>
                <td><input type="text" name="first_name"></td>
            </tr>
            <tr> 
                <td>LastName</td>
                <td><input type="text" name="last_name"></td>
            </tr>
            <tr> 
                <td>UserName</td>
                <td><input type="text" name="username"></td>
            </tr>
            <tr>
                <td>User Type</td>
                <td>
                    <select name="usertype" id="usertype">
                        <option value="0">Administrator</option>
                        <option value="1">Partner</option>
                        <option value="2">Lawyer</option>
                        <option value="3">Paralegal</option>
                        <option value="4">Messenger</option>
                    </select>
                </td>
            </tr>
            <tr> 
                <td>Email</td>
                <td><input type="text" name="email"></td>
            </tr>
            <tr> 
                <td>Password</td>
                <td><input type="text" name="pass"></td>
            </tr>
            <tr> 
                <td></td>
                <td><input type="submit" name="Submit" value="Add"></td>
            </tr>
        </table>
    </form>
</body>
</html>