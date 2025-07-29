<?php
// Include the database connection file. The auto-login logic is in this file.
require_once '../config/db_connect.php';

// Redirect to dashboard if already logged in (either by session or by a new 'remember me' cookie)
if(isset($_SESSION["admin_loggedin"]) && $_SESSION["admin_loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

$error_message = '';

// Process form data when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);

    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        $sql = "SELECT admin_id, username, password FROM admins WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $db_username, $hashed_password);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, start a new session
                            session_regenerate_id();
                            $_SESSION["admin_loggedin"] = true;
                            $_SESSION["admin_id"] = $id;
                            $_SESSION["admin_username"] = $db_username;                            
                            
                            // --- "REMEMBER ME" LOGIC FOR ADMIN ---
                            if ($remember_me) {
                                $token = bin2hex(random_bytes(16));
                                $token_hash = hash('sha256', $token);
                                $expires_at = date('Y-m-d H:i:s', time() + 86400 * 30); // 30 days

                                $update_sql = "UPDATE admins SET remember_token_hash = ?, remember_token_expires_at = ? WHERE admin_id = ?";
                                if ($update_stmt = $conn->prepare($update_sql)) {
                                    $update_stmt->bind_param("ssi", $token_hash, $expires_at, $id);
                                    $update_stmt->execute();
                                    $update_stmt->close();

                                    // Send a separate, uniquely named cookie for the admin
                                    setcookie('remember_me_admin_token', $token, time() + 86400 * 30, "/");
                                }
                            }
                            
                            header("location: dashboard.php");
                            exit;
                        } else {
                            $error_message = "The password you entered was not valid.";
                        }
                    }
                } else {
                    $error_message = "No account found with that username.";
                }
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">Admin Portal Login</h2>
        
        <?php if(!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username" id="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" id="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
            </div>
             <!-- New "Remember Me" Checkbox -->
            <div class="flex items-center justify-between mb-6">
                <label for="remember_me" class="flex items-center text-sm text-gray-600">
                    <input type="checkbox" name="remember_me" id="remember_me" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2">Remember me</span>
                </label>
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Sign In
            </button>
        </form>
    </div>
</body>
</html>