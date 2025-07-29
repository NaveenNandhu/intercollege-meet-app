<?php
require_once '../config/db_connect.php';

// Security Check
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: index.php");
    exit;
}

$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$user_id) {
    header("location: manage_users.php");
    exit;
}

$feedback_message = '';

// --- Handle form submission for UPDATING the user ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $roll_number = trim($_POST['roll_number']);
    $user_type = $_POST['user_type'];
    $new_password = $_POST['new_password'];

    // If a new password is provided, hash it. Otherwise, keep the old one.
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET full_name = ?, email = ?, roll_number = ?, user_type = ?, password = ? WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $full_name, $email, $roll_number, $user_type, $hashed_password, $user_id);
    } else {
        $sql = "UPDATE users SET full_name = ?, email = ?, roll_number = ?, user_type = ? WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $full_name, $email, $roll_number, $user_type, $user_id);
    }

    if ($stmt->execute()) {
        header("location: manage_users.php");
        exit;
    } else {
        $feedback_message = "<div class='bg-red-100 text-red-800 p-3 rounded'>Error updating user.</div>";
    }
    $stmt->close();
}

// --- Fetch existing user data to pre-fill the form ---
$sql_fetch = "SELECT full_name, email, roll_number, user_type FROM users WHERE student_id = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $user_id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    header("location: manage_users.php");
    exit;
}
$stmt_fetch->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <?php include '_sidebar.php'; ?>
        
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit User Account</h1>
            <?php echo $feedback_message; ?>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <form action="edit_user.php?id=<?php echo $user_id; ?>" method="post" class="space-y-4">
                    <div>
                        <label for="full_name" class="block font-bold">Full Name</label>
                        <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" class="w-full p-2 border rounded mt-1" required>
                    </div>
                     <div>
                        <label for="email" class="block font-bold">Email</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full p-2 border rounded mt-1" required>
                    </div>
                    <div>
                        <label for="roll_number" class="block font-bold">Roll Number / ID</label>
                        <input type="text" name="roll_number" id="roll_number" value="<?php echo htmlspecialchars($user['roll_number']); ?>" class="w-full p-2 border rounded mt-1">
                    </div>
                     <div>
                        <label for="user_type" class="block font-bold">User Type</label>
                        <select name="user_type" id="user_type" class="w-full p-2 border rounded mt-1" required>
                            <option value="student" <?php echo ($user['user_type'] == 'student') ? 'selected' : ''; ?>>Student</option>
                            <option value="coordinator" <?php echo ($user['user_type'] == 'coordinator') ? 'selected' : ''; ?>>Coordinator</option>
                        </select>
                    </div>
                    <div>
                        <label for="new_password" class="block font-bold">New Password</label>
                        <input type="password" name="new_password" id="new_password" placeholder="Leave blank to keep current password" class="w-full p-2 border rounded mt-1">
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700">Save Changes</button>
                        <a href="manage_users.php" class="text-gray-600 ml-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>