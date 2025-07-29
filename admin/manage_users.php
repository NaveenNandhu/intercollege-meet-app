<?php
require_once '../config/db_connect.php';

// Security Check
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Fetch all users from the database
$users_result = $conn->query("SELECT student_id, full_name, email, roll_number, user_type, registered_at FROM users ORDER BY registered_at DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <?php include '_sidebar.php'; ?>
        
        <div class="flex-1 p-10 overflow-y-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage All Users</h1>

            <!-- List of All Users -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Full Name</th>
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Roll Number / ID</th>
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">User Type</th>
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($users_result && $users_result->num_rows > 0): ?>
                                <?php while($user = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td class="p-3 font-medium text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo htmlspecialchars($user['roll_number'] ?: 'N/A'); ?></td>
                                    <td class="p-3 text-gray-500">
                                        <span class="capitalize px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $user['user_type'] === 'coordinator' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                            <?php echo htmlspecialchars($user['user_type']); ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm font-medium">
                                        <a href="edit_user.php?id=<?php echo $user['student_id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <a href="delete_user.php?id=<?php echo $user['student_id']; ?>" class="text-red-600 hover:text-red-900 ml-4" onclick="return confirm('Are you sure you want to delete this user? This will remove all their registrations, assignments, and result records.');">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center p-4 text-gray-500">No users have registered yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>