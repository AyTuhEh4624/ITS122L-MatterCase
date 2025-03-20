<?php
// Include the logic file
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/view_case_details_f.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Case Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Case <span class="text-green-400">Details</span></span>
    
            <div class="ml-auto flex space-x-4">
                <a href="logout.php"><button class="text-gray-300">Logout</button></a>
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
                ?>">
                <button class="text-gray-300">Dashboard</button>
            </a>
        </div>
    </div>

        <!-- Main Content -->
        <div class="flex-grow flex justify-center mt-4">
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[60%]">
                <div class="overflow-x-auto mt-4">
                <h2>Case: <?php echo htmlspecialchars($case['case_title']); ?></h2>
                <p><strong>Court:</strong> <?php echo htmlspecialchars($case['court']); ?></p>
    <p><strong>Case Type:</strong> <?php echo htmlspecialchars($case['case_type']); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($case['status']); ?></p>
    <p><strong>Created At:</strong> <?php echo htmlspecialchars($case['created_at']); ?></p>
                    <br><br>
    <!-- Navigation Buttons -->
    <h3>Case Sections:</h3>
    <ul>
        <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
        <li><a href="view_case_updates.php?case_id=<?php echo $case_id; ?>" class="text-blue-400">View Case Updates</a></li>
        <?php endif; ?>
        <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2 || $usertype == 4): ?>
        <li><a href="view_case_evidence.php?case_id=<?php echo $case_id; ?>" class="text-blue-400">View Evidence</a></li>
        <?php endif; ?>
        <li><a href="view_case_forms.php?case_id=<?php echo $case_id; ?>" class="text-blue-400">View Forms</a></li>
        <?php if ($usertype == 0 || $usertype == 1 || $usertype == 3 || $usertype == 4): ?>
        <li><a href="view_case_fees.php?case_id=<?php echo $case_id; ?>" class="text-blue-400">View Case Fees</a></li>
        <?php endif; ?>
        <?php if ($usertype == 0 || $usertype == 1 || $usertype == 3): ?>
        <li><a href="view_case_invoices.php?case_id=<?php echo $case_id; ?>" class="text-blue-400">View Invoices</a></li>
        <?php endif; ?>
    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>