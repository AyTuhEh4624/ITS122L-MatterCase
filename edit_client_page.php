<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/decrypt.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/audit_log.php");

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['id'];
$usertype = $_SESSION['usertype'];

// Restrict access to Admins and Partners only
if ($usertype != 0 && $usertype != 1) {
    header('Location: view_clients_page.php');
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch client data to edit
$client_id = $_GET['client_id'] ?? null;
if (!$client_id) {
    echo "Client ID not provided.";
    exit();
}

// Fetch client data
$query = "SELECT * FROM clients WHERE client_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client_data = $result->fetch_assoc();

if (!$client_data) {
    echo "Client not found.";
    exit();
}

// Decrypt client data
$client_name = decryptData($client_data['client_name'], $key, $method);
$email = decryptData($client_data['email'], $key, $method);
$address = decryptData($client_data['address'], $key, $method);
$profile_picture = decryptData($client_data['profile_picture'], $key, $method);

// Fetch related matters
$matters_query = "SELECT cm.matter_id, m.title FROM client_matters cm JOIN matters m ON cm.matter_id = m.matter_id WHERE cm.client_id = ?";
$stmt = $conn->prepare($matters_query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$matters = $result->fetch_all(MYSQLI_ASSOC);

foreach ($matters as &$matter) {
    $matter['title'] = decryptData($matter['title'], $key, $method);
}

// Handle removal of related matters
if (isset($_POST['remove_matter'])) {
    $matter_id_to_remove = $_POST['remove_matter_id'];
    $delete_stmt = $conn->prepare("DELETE FROM client_matters WHERE client_id = ? AND matter_id = ?");
    $delete_stmt->bind_param("ii", $client_id, $matter_id_to_remove);
    if ($delete_stmt->execute()) {
        echo "Matter removed successfully.";
        header("Refresh:0"); // Refresh the page to reflect changes
    } else {
        echo "Error removing matter.";
    }
}

// Handle adding of related matters
if (isset($_POST['add_matter'])) {
    $new_matter_id = $_POST['new_matter_id'];
    $insert_stmt = $conn->prepare("INSERT INTO client_matters (client_id, matter_id) VALUES (?, ?)");
    $insert_stmt->bind_param("ii", $client_id, $new_matter_id);
    if ($insert_stmt->execute()) {
        echo "Matter added successfully.";
        header("Refresh:0"); // Refresh the page to reflect changes
    } else {
        echo "Error adding matter.";
    }
}

// Fetch available matters for selection
$available_matters_query = "SELECT matter_id, title FROM matters";
$available_matters_result = $conn->query($available_matters_query);
$available_matters = $available_matters_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Client</title>
</head>
<body>
    <h1>Edit Client</h1>

    <form name="update_client" method="post" action="edit_client_page.php?client_id=<?php echo $client_id; ?>">
        <table border="0">
            <tr> 
                <td>Client Name</td>
                <td><input type="text" name="client_name" value="<?php echo htmlspecialchars($client_name); ?>"></td>
            </tr>
            <tr> 
                <td>Email</td>
                <td><input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>"></td>
            </tr>
            <tr> 
                <td>Address</td>
                <td><input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>"></td>
            </tr>
        </table>
    </form>

    <h2>Related Matters</h2>
    <table border="1">
        <tr>
            <th>Matter Title</th>
            <th>Action</th>
        </tr>
        <?php foreach ($matters as $matter): ?>
        <tr>
            <td><?php echo htmlspecialchars($matter['title']); ?></td>
            <td>
                <form method="post" action="">
                    <input type="hidden" name="remove_matter_id" value="<?php echo $matter['matter_id']; ?>">
                    <input type="submit" name="remove_matter" value="Remove">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Add Matter</h2>
    <form method="post" action="">
        <select name="new_matter_id">
            <?php foreach ($available_matters as $available_matter): ?>
                <option value="<?php echo $available_matter['matter_id']; ?>">
                    <?php echo htmlspecialchars(decryptData($available_matter['title'], $key, $method)); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" name="add_matter" value="Add Matter">
    </form>

    <div class="mb-4">
        <button type="submit" class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12">Update Client</button>
    </div>
    <div>
        <a href="view_clients_page.php"><button type="button" class="bg-gray-700 text-white font-semibold py-3 rounded-lg shadow-md w-full h-12">Back to View Clients</button></a>
    </div>
</body>
</html>
