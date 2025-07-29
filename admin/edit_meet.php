<?php
require_once '../config/db_connect.php';

// Security Check
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Get the meet ID from the URL
$meet_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$meet_id) {
    header("location: manage_meets.php");
    exit;
}

$meet_name = $department = "";
$feedback_message = '';

// --- Handle form submission for UPDATING the meet ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $meet_name = trim($_POST['meet_name']);
    $department = trim($_POST['department']);

    if (empty($meet_name) || empty($department)) {
        $feedback_message = "<div class='bg-red-100 text-red-800 p-3 rounded mb-4'>Please fill in all fields.</div>";
    } else {
        $sql = "UPDATE meets SET meet_name = ?, department = ? WHERE meet_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $meet_name, $department, $meet_id);
            if ($stmt->execute()) {
                // Redirect back to the main list after successful update
                header("location: manage_meets.php");
                exit;
            } else {
                $feedback_message = "<div class='bg-red-100 text-red-800 p-3 rounded mb-4'>Error updating meet.</div>";
            }
            $stmt->close();
        }
    }
} else {
    // --- Fetch existing meet data to pre-fill the form ---
    $sql = "SELECT meet_name, department FROM meets WHERE meet_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $meet_id);
        $stmt->execute();
        $stmt->bind_result($meet_name, $department);
        if (!$stmt->fetch()) {
            // If no meet is found with that ID, redirect
            header("location: manage_meets.php");
            exit;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Meet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <?php include '_sidebar.php'; ?>
        
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Meet</h1>
            
            <?php echo $feedback_message; ?>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <form action="edit_meet.php?id=<?php echo $meet_id; ?>" method="post">
                    <div class="mb-4">
                        <label for="meet_name" class="block text-gray-700 font-bold mb-2">Meet Name</label>
                        <input type="text" name="meet_name" id="meet_name" value="<?php echo htmlspecialchars($meet_name); ?>" class="w-full p-2 border rounded" required>
                    </div>
                    <div class="mb-4">
                        <label for="department" class="block text-gray-700 font-bold mb-2">Department</label>
                        <input type="text" name="department" id="department" value="<?php echo htmlspecialchars($department); ?>" class="w-full p-2 border rounded" required>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700">Save Changes</button>
                        <a href="manage_meets.php" class="text-gray-600 ml-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>