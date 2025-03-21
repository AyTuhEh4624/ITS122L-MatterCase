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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Lawyer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Assign <span class="text-green-400">Lawyer</span></span>
            <div class="ml-auto flex space-x-4">
                <a href="logout.php"><button class="text-gray-300">Logout</button></a>
                <a href="<?php
                    switch ($usertype) {
                        case 0: echo 'dashboard_admin.php'; break;
                        case 1: echo 'dashboard_partner.php'; break;
                        case 2: echo 'dashboard_lawyer.php'; break;
                        case 3: echo 'dashboard_paralegal.php'; break;
                        case 4: echo 'dashboard_messenger.php'; break;
                        default: echo 'login_page.php'; break;
                    }
                ?>">
                <button class="text-gray-300">Dashboard</button>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow flex justify-center mt-4">
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[70%]">
                <h2 class="text-xl font-semibold text-white mb-4">Assign Lawyer to Case</h2>
                
                <!-- Assign Lawyer Form -->
                <form method="POST" action="" class="mb-4">
                    <label for="lawyer_id" class="block mb-2 font-semibold">Select Lawyer:</label>
                    <select id="lawyer_id" name="lawyer_id" onchange="this.form.submit()" required class="w-full p-2 border border-gray-500 rounded-lg bg-gray-800 text-white">
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
                    <div class="mt-4">
                        <h2 class="text-lg font-semibold text-white">Assigned Cases</h2>
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
                                                <form method="POST" action="" style="display:inline;">
                                                    <input type="hidden" name="case_id" value="<?php echo $case['case_id']; ?>">
                                                    <input type="hidden" name="lawyer_id" value="<?php echo $_GET['lawyer_id']; ?>">
                                                    <input type="submit" name="remove_lawyer" value="Remove" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
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
                    <form method="POST" action="" class="mt-4">
                        <label for="case_id" class="block mb-2 font-semibold">Select Case:</label>
                        <select id="case_id" name="case_id" required class="w-full p-2 border border-gray-500 rounded-lg bg-gray-800 text-white">
                            <option value="">-- Select a Case --</option>
                            <?php while ($case = $casesResult->fetch_assoc()): ?>
                                <option value="<?php echo $case['case_id']; ?>"><?php echo htmlspecialchars(decryptData($case['case_title'], $key, $method)); ?></option>
                            <?php endwhile; ?>
                        </select>
                        <input type="hidden" name="lawyer_id" value="<?php echo $_GET['lawyer_id']; ?>">
                        <input type="submit" name="assign_lawyer" value="Assign Lawyer" class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12 mt-2">
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
