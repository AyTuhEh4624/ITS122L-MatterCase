<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php"); // Include decryption function

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['id'];
$usertype = $_SESSION['usertype'];

// Restrict access to Admins and Partners only
if ($usertype != 0 && $usertype != 1) {
    header('Location: view_cases_page.php');
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $case_title = $_POST['case_title'];
    $court = $_POST['court'];
    $case_type = $_POST['case_type'];
    $status = $_POST['status'];
    $client_id = $_POST['client_id'];
    $matter_id = $_POST['matter_id'];

    // Encrypt the case title and court
    $encryptedCaseTitle = encryptData($case_title, $key, $method);
    $encryptedCourt = encryptData($court, $key, $method);

    // Insert the new case into the database
    $stmt = $conn->prepare("INSERT INTO cases (case_title, court, case_type, status, client_id, matter_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $encryptedCaseTitle, $encryptedCourt, $case_type, $status, $client_id, $matter_id);

    if ($stmt->execute()) {
        // Redirect back to the view cases page with a success message
        header('Location: view_cases_page.php?success=1');
        exit();
    } else {
        // Redirect back to the add case page with an error message
        header('Location: add_case_page.php?error=1');
        exit();
    }

    $stmt->close();
    $conn->close();
}

// Fetch clients and matters for dropdowns
$clients = $conn->query("SELECT client_id, client_name FROM clients");
$matters = $conn->query("SELECT matter_id, title FROM matters");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Case</title>
    <link rel="stylesheet" href="css/add_case_page.css">
</head>
<body>
    <div class="top-container">
        <div class="top-nav">
            <div class="top-left">
                <img src="FrontEndTrial\img\logo1.png" alt="MatterCase Logo">
                <h1>MatterCase</h1>
            </div> 
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <!-- Display success or error messages -->
    <div class="main-container">
        <h2>Add Case</h2>
        <div class="status-message">
            <?php if (isset($_GET['success'])): ?>
               <p style="color: green;">Case added successfully!</p>
            <?php elseif (isset($_GET['error'])): ?>
                <p style="color: red;">Failed to add case. Please try again.</p>
            <?php endif; ?>
        </div>

        <!-- Form to Add a New Case -->
        <div class="form">
            <form action="add_case_page.php" method="POST">
                <input type="text" id="case_title" name="case_title" placeholder="Case Title" required><br><br>
                <input type="text" id="court" name="court" placeholder="Court" required><br><br>
                <input type="text" id="case_type" name="case_type" placeholder="Case Type" required><br><br>

                <label for="client_id">Client:</label>
                <select id="client_id" name="client_id" class="client" required>
                    <?php while ($client = $clients->fetch_assoc()): ?>
                        <option value="<?php echo $client['client_id']; ?>">
                            <?php echo htmlspecialchars(decryptData($client['client_name'], $key, $method)); ?>
                        </option>
                    <?php endwhile; ?>
                </select><br><br>

                <label for="matter_id">Matter:</label>
                <select id="matter_id" name="matter_id" class="matter" required>
                    <?php while ($matter = $matters->fetch_assoc()): ?>
                        <option value="<?php echo $matter['matter_id']; ?>">
                            <?php echo htmlspecialchars(decryptData($matter['title'], $key, $method)); ?>
                        </option>
                    <?php endwhile; ?>
                </select><br><br>

                <label for="status">Status:</label>
                <select id="status" name="status" class="status" required>
                    <option value="Active">Active</option>
                    <option value="Dismissed">Dismissed</option>
                    <option value="Closed">Closed</option>
                </select><br><br>

                <button type="submit">Add Case</button>
            </form>
            <p><a href="view_cases_page.php">Back to View Cases</a></p>
        </div>
    </div>
</body>
</html>