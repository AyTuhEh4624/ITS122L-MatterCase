<?php
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/view_case_details_f.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Case Evidence</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <h1>Case Evidence</h1>
    <a href="view_case_details.php?case_id=<?php echo $case_id; ?>">Back to Case Details</a>

    <h2>Case: <?php echo htmlspecialchars($case['case_title']); ?></h2>
    <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
    <p><a href="add_evidence_page.php?case_id=<?php echo $case_id; ?>">Add New Evidence</a></p>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>Evidence ID</th>
                <th>Evidence Type</th>
                <th>File</th>
                <th>Description</th>
                <th>Submission Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($evidence as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['evidence_id']); ?></td>
                    <td><?php echo htmlspecialchars($item['evidence_type']); ?></td>
                    <td>
                        <?php if (!empty($item['file_path'])): ?>
                            <a href="view_file.php?file=<?php echo urlencode($item['file_path']); ?>" target="_blank">View File</a>
                        <?php else: ?>
                            No file uploaded
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td><?php echo htmlspecialchars($item['submission_status']); ?></td>
                    <td>
                        <a href="edit_evidence_page.php?evidence_id=<?php echo $item['evidence_id']; ?>">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <div class="bg-gray-700 text-gray-300 px-6 py-3 flex items-center">
            <span class="text-lg">Case <span class="text-green-400">Evidence</span></span>
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
                    <a href="add_evidence_page.php?case_id=<?php echo $case_id; ?>">
                        <button class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12">Add New Evidence</button>
                    </a>
                </div>

                <div class="overflow-x-auto mt-4">
                    <table class="mx-auto w-full border-collapse border border-gray-500">
                        <thead>
                            <tr class="bg-gray-800 text-white">
                                <th class="border border-gray-500 px-4 py-2">Evidence ID</th>
                                <th class="border border-gray-500 px-4 py-2">Evidence Type</th>
                                <th class="border border-gray-500 px-4 py-2">File</th>
                                <th class="border border-gray-500 px-4 py-2">Description</th>
                                <th class="border border-gray-500 px-4 py-2">Submission Status</th>
                                <th class="border border-gray-500 px-4 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($evidence as $item): ?>
                                <tr class="bg-gray-700 text-gray-300">
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($item['evidence_id']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($item['evidence_type']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2">
                                        <?php if (!empty($item['file_path'])): ?>
                                            <a href="view_file.php?file=<?php echo urlencode($item['file_path']); ?>" target="_blank" class="text-blue-400">View File</a>
                                        <?php else: ?>
                                            No file uploaded
                                        <?php endif; ?>
                                    </td>
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($item['description']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2"><?php echo htmlspecialchars($item['submission_status']); ?></td>
                                    <td class="border border-gray-500 px-4 py-2">
                                        <a href="edit_evidence_page.php?evidence_id=<?php echo $item['evidence_id']; ?>" class="text-green-400">Edit</a>
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
