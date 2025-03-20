<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php"); // Include decryption function
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php"); // Include encryption function

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

    // Check if the lawyer is already assigned to the case
    $checkQuery = "SELECT * FROM case_lawyers WHERE case_id = $case_id AND lawyer_id = $lawyer_id";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows > 0) {
        echo "<p>This lawyer is already assigned to the selected case.</p>";
    } else {
        // Assign the lawyer to the case
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

    // Remove the lawyer from the case
    $removeQuery = "DELETE FROM case_lawyers WHERE case_id = $case_id AND lawyer_id = $lawyer_id";
    if ($conn->query($removeQuery) === TRUE) {
        echo "<p>Lawyer removed from the case successfully!</p>";
    } else {
        echo "<p>Error removing lawyer: " . $conn->error . "</p>";
    }
}

// Fetch assigned cases for the selected lawyer
$assignedCases = [];
if (isset($_GET['lawyer_id'])) {
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

    // Decrypt case titles
    foreach ($assignedCases as &$case) {
        $case['case_title'] = decryptData($case['case_title'], $key, $method);
    }
}

// Decrypt lawyer names
$lawyers = [];
while ($lawyer = $lawyersResult->fetch_assoc()) {
    $lawyer['first_name'] = decryptData($lawyer['first_name'], $key, $method);
    $lawyer['last_name'] = decryptData($lawyer['last_name'], $key, $method);
    $lawyers[] = $lawyer;
}

// Decrypt case titles for the assign case dropdown
$cases = [];
while ($case = $casesResult->fetch_assoc()) {
    $case['case_title'] = decryptData($case['case_title'], $key, $method);
    $cases[] = $case;
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Lawyer</title>
    <style>
        form {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        select, input[type="submit"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .assigned-cases {
            margin-top: 20px;
        }
        .assigned-cases table {
            width: 100%;
            border-collapse: collapse;
        }
        .assigned-cases th, .assigned-cases td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        .assigned-cases th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Assign Lawyer to Case</h1>

    <!-- Back to Dashboard Button -->
    <p>
        <a href="<?php
            // Redirect to the appropriate dashboard based on usertype
            switch ($usertype) {
                case 0: echo 'dashboard_admin.php'; break;
                case 1: echo 'dashboard_partner.php'; break;
                case 2: echo 'dashboard_lawyer.php'; break;
                case 3: echo 'dashboard_paralegal.php'; break;
                case 4: echo 'dashboard_messenger.php'; break;
                default: echo 'login_page.php'; break;
            }
        ?>">Back to Dashboard</a>
    </p>

    <!-- Assign Lawyer Form -->
    <form method="POST" action="">
        <label for="lawyer_id">Select Lawyer:</label>
        <select id="lawyer_id" name="lawyer_id" onchange="this.form.submit()" required>
            <option value="">-- Select a Lawyer --</option>
            <?php foreach ($lawyers as $lawyer): ?>
                <option value="<?php echo $lawyer['id']; ?>" <?php echo (isset($_GET['lawyer_id']) && $_GET['lawyer_id'] == $lawyer['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($lawyer['first_name'] . ' ' . $lawyer['last_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Display Assigned Cases -->
    <?php if (!empty($assignedCases)): ?>
        <div class="assigned-cases">
            <h2>Assigned Cases</h2>
            <table>
                <thead>
                    <tr>
                        <th>Case ID</th>
                        <th>Case Title</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignedCases as $case): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($case['case_id']); ?></td>
                            <td><?php echo htmlspecialchars($case['case_title']); ?></td>
                            <td>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="case_id" value="<?php echo $case['case_id']; ?>">
                                    <input type="hidden" name="lawyer_id" value="<?php echo $_GET['lawyer_id']; ?>">
                                    <input type="submit" name="remove_lawyer" value="Remove" style="background-color: #ff4d4d;">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Assign Lawyer to Case Form -->
    <?php if (isset($_GET['lawyer_id'])): ?>
        <form method="POST" action="">
            <label for="case_id">Select Case:</label>
            <select id="case_id" name="case_id" required>
                <option value="">-- Select a Case --</option>
                <?php foreach ($cases as $case): ?>
                    <option value="<?php echo $case['case_id']; ?>"><?php echo htmlspecialchars($case['case_title']); ?></option>
                <?php endforeach; ?>
            </select>

            <input type="hidden" name="lawyer_id" value="<?php echo $_GET['lawyer_id']; ?>">
            <input type="submit" name="assign_lawyer" value="Assign Lawyer">
        </form>
    <?php endif; ?>
</body>
</html>