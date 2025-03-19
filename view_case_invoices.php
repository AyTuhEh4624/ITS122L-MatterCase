<?php
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/view_case_details_f.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Invoices</title>
</head>
<body>
    <h1>Case Invoices</h1>
    <a href="view_case_details.php?case_id=<?php echo $case_id; ?>">Back to Case Details</a>

    <h2>Case: <?php echo htmlspecialchars($case['case_title']); ?></h2>

    <p><a href="add_invoice_page.php?case_id=<?php echo $case_id; ?>">Add New Invoice</a></p>
    <table>
        <thead>
            <tr>
                <th>Invoice ID</th>
                <th>Amount</th>
                <th>Payment Status</th>
                <th>Due Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $invoice): ?>
                <tr>
                    <td><?php echo htmlspecialchars($invoice['invoice_id']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['amount']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['payment_status']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['due_date']); ?></td>
                    <td>
                        <a href="edit_invoice_page.php?invoice_id=<?php echo $invoice['invoice_id']; ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>