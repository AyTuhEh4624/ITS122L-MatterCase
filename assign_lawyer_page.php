<?php
session_start();
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

// Fetch all cases
$casesQuery = "SELECT case_id, case_title FROM cases";
$casesResult = $conn->query($casesQuery);
if (!$casesResult) {
    die("Failed to fetch cases: " . $conn->error);
}

// Fetch all lawyers (usertype = 2)
$lawyersQuery = "SELECT id, first_name, last_name FROM users WHERE usertype = 2";
$lawyersResult = $conn->query($lawyersQuery);
if (!$lawyersResult) {
    die("Failed to fetch lawyers: " . $conn->error);
}

// Handle form submission for assigning a lawyer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_lawyer'])) {
    $case_id = $_POST['case_id'];
    $lawyer_id = $_POST['lawyer_id'];

    $checkQuery = "SELECT * FROM case_lawyers WHERE case_id = $case_id AND lawyer_id = $lawyer_id";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows > 0) {
        echo "<p>This lawyer is already assigned to the selected case.</p>";
    } else {
        $assignQuery = "INSERT INTO case_lawyers (case_id, lawyer_id) VALUES ($case_id, $lawyer_id)";
        if ($conn->query($assignQuery) === TRUE) {
            echo "<p>Lawyer assigned successfully!</p>";
        } else {
            echo "<p>Error assigning lawyer: " . $conn->error . "</p>";
        }
    }
}

// Handle form submission for removing a lawyer from a case
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_lawyer'])) {
    $case_id = $_POST['case_id'];
    $lawyer_id = $_POST['lawyer_id'];

    $removeQuery = "DELETE FROM case_lawyers WHERE case_id = $case_id AND lawyer_id = $lawyer_id";
    if ($conn->query($removeQuery) === TRUE) {
        echo "<p>Lawyer removed from the case successfully!</p>";
    } else {
        echo "<p>Error removing lawyer: " . $conn->error . "</p>";
    }
}

// Fetch assigned cases for the selected lawyer
$assignedCases = [];
if (isset($_GET['lawyer_id']) && $_GET['lawyer_id'] !== '') {
    $selectedLawyerId = $_GET['lawyer_id'];

    $assignedCasesQuery = "
        SELECT c.case_id, c.case_title
        FROM cases c
        JOIN case_lawyers cl ON c.case_id = cl.case_id
        WHERE cl.lawyer_id = $selectedLawyerId
    ";
    $assignedCasesResult = $conn->query($assignedCasesQuery);

    if (!$assignedCasesResult) {
        die("Failed to fetch assigned cases: " . $conn->error);
    }

    $assignedCases = $assignedCasesResult->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Lawyer</title>
</head>
<body>
    <h1>Assign Lawyer to Case</h1>
    <p><a href="<?php echo ($usertype == 0) ? 'dashboard_admin.php' : 'dashboard_partner.php'; ?>">Back to Dashboard</a></p>

    <form method="GET" action="">
        <label for="lawyer_id">Select Lawyer:</label>
        <select id="lawyer_id" name="lawyer_id" onchange="this.form.submit()" required>
            <option value="">-- Select a Lawyer --</option>
            <?php 
            $lawyersResult->data_seek(0);
            while ($lawyer = $lawyersResult->fetch_assoc()): ?>
                <option value="<?php echo $lawyer['id']; ?>" <?php echo (isset($_GET['lawyer_id']) && $_GET['lawyer_id'] == $lawyer['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars(decryptData($lawyer['first_name'], $key, $method) . ' ' . decryptData($lawyer['last_name'], $key, $method)); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if (!empty($assignedCases)): ?>
        <h2>Assigned Cases</h2>
        <table>
            <tr><th>Case ID</th><th>Case Title</th><th>Action</th></tr>
            <?php foreach ($assignedCases as $case): ?>
                <tr>
                    <td><?php echo htmlspecialchars($case['case_id']); ?></td>
                    <td><?php echo htmlspecialchars(decryptData($case['case_title'], $key, $method)); ?></td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="case_id" value="<?php echo $case['case_id']; ?>">
                            <input type="hidden" name="lawyer_id" value="<?php echo $_GET['lawyer_id']; ?>">
                            <input type="submit" name="remove_lawyer" value="Remove">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php if (isset($_GET['lawyer_id'])): ?>
        <form method="POST" action="">
            <label for="case_id">Select Case:</label>
            <select id="case_id" name="case_id" required>
                <option value="">-- Select a Case --</option>
                <?php while ($case = $casesResult->fetch_assoc()): ?>
                    <option value="<?php echo $case['case_id']; ?>"><?php echo htmlspecialchars(decryptData($case['case_title'], $key, $method)); ?></option>
                <?php endwhile; ?>
            </select>
            <input type="hidden" name="lawyer_id" value="<?php echo $_GET['lawyer_id']; ?>">
            <input type="submit" name="assign_lawyer" value="Assign Lawyer">
        </form>
    <?php endif; ?>
</body>
</html>
