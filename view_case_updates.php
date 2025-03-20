<?php
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/view_case_details_f.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Case Updates</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Case <span class="text-green-400">Updates</span></span>
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
                    <a href="add_case_update_page.php?case_id=<?php echo $case_id; ?>">
                        <button class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12">Add New Update</button>
                    </a>
                </div>

                <div class="overflow-x-auto mt-4">
                    <table class="mx-auto w-full border-collapse border border-gray-500">
                        <thead>
                            <tr class="bg-gray-800 text-white">
                                <th class="border border-gray-500 px-4 py-2">Update ID</th>
                                <th class="border border-gray-500 px-4 py-2">Update Text</th>
                                <th class="border border-gray-500 px-4 py-2">Updated By</th>
                                <th class="border border-gray-500 px-4 py-2">Updated At</th>
                                <th class="border border-gray-500 px-4 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($case_updates as $update): ?>
                                <tr class="bg-gray-700 text-gray-300">
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($update['update_id']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($update['update_text']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($update['updated_by']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($update['updated_at']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2">
                                        <a href="edit_case_update_page.php?update_id=<?php echo $update['update_id']; ?>" class="text-green-400">Edit</a>
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