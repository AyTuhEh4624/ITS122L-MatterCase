<?php
include_once("encryption.php");
?>

<html>
<head>
	<title>Add Users</title>
</head>

<body>
	<a href="login.php">Home</a>
	<br/><br/>

	<form action="add.php" method="post" name="form1">
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
				<td>Email</td>
				<td><input type="text" name="email"></td>
			</tr>
			<tr> 
				<td>Username</td>
				<td><input type="text" name="mobile"></td>
			</tr>
			<tr> 
				<td>Mobile</td>
				<td><input type="text" name="mobile"></td>
			</tr>
			<tr> 
				<td>Password</td>
				<td><input type="text" name="mobile"></td>
			</tr>
			<tr> 
				<td></td>
				<td><input type="submit" name="Submit" value="Add"></td>
			</tr>
		</table>
	</form>
	
	<?php
	// Check if the form was submitted
	if (isset($_POST['Submit'])) {
		// Retrieve the form data
		$first_name = $_POST['first_name'];
		$last_name = $_POST['last_name'];
		$email = $_POST['email'];
		$mobile = $_POST['mobile'];

		// Encrypt the form data using the encryptData function
		$encrypted_first_name = encryptData($first_name, $key, $method);
		$encrypted_last_name = encryptData($last_name, $key, $method);
		$encrypted_email = encryptData($email, $key, $method);
		$encrypted_mobile = encryptData($mobile, $key, $method);

		// connect 2 database
		include_once("config.php");

		// Insert the encrypted user data into the database
		$result = mysqli_query($conn, "INSERT INTO users(first_name, last_name, email, mobile) VALUES('$encrypted_first_name', '$encrypted_last_name', '$encrypted_email', '$encrypted_mobile')");

		//debug
		if ($result) {
			echo "User added successfully. <a href='viewusers.php'>View Users</a>";
		} else {
			echo "Error: " . mysqli_error($conn);
		}
	}
	?>
</body>
</html>