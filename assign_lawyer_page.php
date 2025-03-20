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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Lawyer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col items-center p-6">
        <h1 class="text-2xl font-bold">Assign Lawyer to Case</h1>
        
        <div class="mt-4 w-full max-w-lg">
            <a href="<?php
                switch ($usertype) {
                    case 0: echo 'dashboard_admin.php'; break;
                    case 1: echo 'dashboard_partner.php'; break;
                    case 2: echo 'dashboard_lawyer.php'; break;
                    case 3: echo 'dashboard_paralegal.php'; break;
                    case 4: echo 'dashboard_messenger.php'; break;
                    default: echo 'login_page.php'; break;
                }
            ?>" class="text-yellow-300 underline">Back to Dashboard</a>
        </div>
        
        <form method="POST" action="" class="bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg mt-4">
            <label for="lawyer_id" class="block mb-2 font-semibold">Select Lawyer:</label>
            <select id="lawyer_id" name="lawyer_id" onchange="this.form.submit()" required class="w-full p-2 rounded bg-gray-700 text-white">
                <option value="">-- Select a Lawyer --</option>
                <?php foreach ($lawyers as $lawyer): ?>
                    <option value="<?php echo $lawyer['id']; ?>" <?php echo (isset($_GET['lawyer_id']) && $_GET['lawyer_id'] == $lawyer['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($lawyer['first_name'] . ' ' . $lawyer['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        
        <?php if (!empty($assignedCases)): ?>
            <div class="w-full max-w-lg mt-6">
                <h2 class="text-lg font-semibold">Assigned Cases</h2>
                <div class="overflow-x-auto mt-2">
                    <table class="w-full border-collapse border border-gray-500">
                        <thead>
                            <tr class="bg-gray-800 text-white">
                                <th class="border border-gray-500 px-4 py-2">Case ID</th>
                                <th class="border border-gray-500 px-4 py-2">Case Title</th>
                                <th class="border border-gray-500 px-4 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignedCases as $case): ?>
                                <tr class="bg-gray-700 text-gray-300">
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($case['case_id']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($case['case_title']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2">
                                        <form method="POST" action="" class="inline">
                                            <input type="hidden" name="case_id" value="<?php echo $case['case_id']; ?>">
                                            <input type="hidden" name="lawyer_id" value="<?php echo $_GET['lawyer_id']; ?>">
                                            <input type="submit" name="remove_lawyer" value="Remove" class="bg-red-500 px-2 py-1 rounded cursor-pointer">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['lawyer_id'])): ?>
            <form method="POST" action="" class="bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-lg mt-6">
                <label for="case_id" class="block mb-2 font-semibold">Select Case:</label>
                <select id="case_id" name="case_id" required class="w-full p-2 rounded bg-gray-700 text-white">
                    <option value="">-- Select a Case --</option>
                    <?php foreach ($cases as $case): ?>
                        <option value="<?php echo $case['case_id']; ?>"><?php echo htmlspecialchars($case['case_title']); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="hidden" name="lawyer_id" value="<?php echo $_GET['lawyer_id']; ?>">
                <input type="submit" name="assign_lawyer" value="Assign Lawyer" class="mt-4 w-full bg-green-500 text-white font-semibold py-2 rounded cursor-pointer">
            </form>
        <?php endif; ?>
    </div>
</body>
</html>