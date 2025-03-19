<?php
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/view_case_details_f.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Fees</title>
</head>
<body>
    <h1>Case Fees</h1>
    <a href="view_case_details.php?case_id=<?php echo $case_id; ?>">Back to Case Details</a>

    <h2>Case: <?php echo htmlspecialchars($case['case_title']); ?></h2>

    <p><a href="add_case_fee_page.php?case_id=<?php echo $case_id; ?>">Add New Fee</a></p>
    <table>
        <thead>
            <tr>
                <th>Fee ID</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Payment Status</th>
                <th>Due Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($case_fees as $fee): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fee['fee_id']); ?></td>
                    <td><?php echo htmlspecialchars($fee['amount']); ?></td>
                    <td><?php echo htmlspecialchars($fee['fee_description']); ?></td>
                    <td><?php echo htmlspecialchars($fee['payment_status']); ?></td>
                    <td><?php echo htmlspecialchars($fee['due_date']); ?></td>
                    <td>
                        <a href="edit_case_fee_page.php?fee_id=<?php echo $fee['fee_id']; ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>