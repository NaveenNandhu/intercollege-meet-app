<?php
require_once '../config/db_connect.php';

// Security Check
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: index.php");
    exit;
}

// --- Logic for creating a new meet (remains the same) ---
$feedback_message = "";
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_meet'])){
    $meet_name = trim($_POST['meet_name']);
    $department = trim($_POST['department']);
    if(!empty($meet_name) && !empty($department)){
        $sql = "INSERT INTO meets (meet_name, department) VALUES (?, ?)";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("ss", $meet_name, $department);
            if($stmt->execute()){
                $feedback_message = "<div class='bg-green-100 text-green-800 p-3 rounded mb-4'>Meet created successfully!</div>";
            } else {
                $feedback_message = "<div class='bg-red-100 text-red-800 p-3 rounded mb-4'>Error: Could not create meet.</div>";
            }
            $stmt->close();
        }
    } else {
        $feedback_message = "<div class='bg-yellow-100 text-yellow-800 p-3 rounded mb-4'>Please fill in all fields.</div>";
    }
}

// Fetch all meets to display in the table
$meets_result = $conn->query("SELECT meet_id, meet_name, department, created_at FROM meets ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Meets</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <?php include '_sidebar.php'; ?>
        
        <div class="flex-1 p-10 overflow-y-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Meets</h1>

            <?php echo $feedback_message; ?>

            <!-- Create New Meet Form -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h2 class="text-xl font-bold mb-4">Create a New Meet</h2>
                <form action="manage_meets.php" method="post">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="meet_name" placeholder="Meet Name (e.g., Tech Fest 2025)" class="mt-1 block w-full p-2 border rounded" required>
                        <input type="text" name="department" placeholder="Department" class="mt-1 block w-full p-2 border rounded" required>
                    </div>
                    <button type="submit" name="create_meet" class="mt-4 bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700">Create Meet</button>
                </form>
            </div>

            <!-- List of Created Meets -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Existing Meets</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Meet Name</th>
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                <!-- New Column for Actions -->
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($meets_result && $meets_result->num_rows > 0): ?>
                                <?php while($row = $meets_result->fetch_assoc()): ?>
                                <tr>
                                    <td class="p-3 font-medium text-gray-900"><?php echo htmlspecialchars($row['meet_name']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo date("M j, Y", strtotime($row['created_at'])); ?></td>
                                    <td class="p-3 text-sm font-medium">
                                        <!-- New Edit and Delete Links -->
                                        <a href="edit_meet.php?id=<?php echo $row['meet_id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <a href="delete_meet.php?id=<?php echo $row['meet_id']; ?>" class="text-red-600 hover:text-red-900 ml-4" onclick="return confirm('Are you sure you want to delete this meet? This will also delete all events associated with it.');">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center p-4 text-gray-500">No meets created yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>