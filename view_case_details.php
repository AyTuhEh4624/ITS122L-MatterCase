<?php
// Include the logic file
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/view_case_details_f.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Details</title>
</head>
<body>
    <h1>Case Details</h1>
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

    <h2>Case: <?php echo htmlspecialchars($case['case_title']); ?></h2>
    <p><strong>Court:</strong> <?php echo htmlspecialchars($case['court']); ?></p>
    <p><strong>Case Type:</strong> <?php echo htmlspecialchars($case['case_type']); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($case['status']); ?></p>
    <p><strong>Created At:</strong> <?php echo htmlspecialchars($case['created_at']); ?></p>

    <!-- Navigation Buttons -->
    <h3>Case Sections</h3>
    <ul>
        <li><a href="view_case_updates.php?case_id=<?php echo $case_id; ?>">View Case Updates</a></li>
        <li><a href="view_case_evidence.php?case_id=<?php echo $case_id; ?>">View Evidence</a></li>
        <li><a href="view_case_forms.php?case_id=<?php echo $case_id; ?>">View Forms</a></li>
        <li><a href="view_case_fees.php?case_id=<?php echo $case_id; ?>">View Case Fees</a></li>
        <li><a href="view_case_invoices.php?case_id=<?php echo $case_id; ?>">View Invoices</a></li>
    </ul>
</body>
</html>