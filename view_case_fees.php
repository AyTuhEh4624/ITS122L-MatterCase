<?php
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/view_case_details_f.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Case Fees</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Case <span class="text-green-400">Fees</span></span>
            <div class="ml-auto">
                <a href="view_case_details.php?case_id=<?php echo $case_id; ?>">
                    <button class="text-gray-300">Back to Case Details</button>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow flex justify-center mt-4">
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[90%]">
                <h2 class="text-xl font-semibold">Case: <?php echo htmlspecialchars($case['case_title']); ?></h2>
                
                <div class="mt-4">
                    <a href="add_case_fee_page.php?case_id=<?php echo $case_id; ?>">
                        <button class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12">Add New Fee</button>
                    </a>
                </div>

                <div class="overflow-x-auto mt-4">
                    <table class="mx-auto w-full border-collapse border border-gray-500">
                        <thead>
                            <tr class="bg-gray-800 text-white">
                                <th class="border border-gray-500 px-4 py-2">Fee ID</th>
                                <th class="border border-gray-500 px-4 py-2">Amount</th>
                                <th class="border border-gray-500 px-4 py-2">Description</th>
                                <th class="border border-gray-500 px-4 py-2">Payment Status</th>
                                <th class="border border-gray-500 px-4 py-2">Due Date</th>
                                <th class="border border-gray-500 px-4 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($case_fees as $fee): ?>
                                <tr class="bg-gray-700 text-gray-300">
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($fee['fee_id']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($fee['amount']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($fee['fee_description']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($fee['payment_status']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($fee['due_date']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2">
                                        <a href="edit_case_fee_page.php?fee_id=<?php echo $fee['fee_id']; ?>" class="text-green-400">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>