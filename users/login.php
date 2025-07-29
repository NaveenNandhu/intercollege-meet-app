<?php
require_once '../config/db_connect.php';

// If a user is already logged in, redirect them to their dashboard
if(isset($_SESSION["user_loggedin"]) && $_SESSION["user_loggedin"] === true){
    if ($_SESSION["user_type"] === 'coordinator') {
        header("location: ../coordinator/dashboard.php");
    } else {
        header("location: dashboard.php");
    }
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);

    if(!empty($email) && !empty($password)){
        $sql = "SELECT student_id, full_name, email, password, user_type FROM users WHERE email = ?";
        
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $email);
            
            if($stmt->execute()){
                $stmt->store_result();
                
                if($stmt->num_rows == 1){
                    $stmt->bind_result($id, $name, $email_from_db, $hashed_password, $user_type);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, start a session
                            session_regenerate_id();
                            $_SESSION["user_loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["user_name"] = $name;
                            $_SESSION["user_type"] = $user_type;

                            // --- "REMEMBER ME" LOGIC ---
                            if ($remember_me) {
                                // 1. Generate secure random tokens
                                $token = bin2hex(random_bytes(16));
                                $token_hash = hash('sha256', $token);
                                
                                // 2. Set an expiration date (e.g., 30 days from now)
                                $expires_at = date('Y-m-d H:i:s', time() + 86400 * 30); // 86400 = 1 day

                                // 3. Store the HASHED token in the database
                                $update_sql = "UPDATE users SET remember_token_hash = ?, remember_token_expires_at = ? WHERE student_id = ?";
                                if ($update_stmt = $conn->prepare($update_sql)) {
                                    $update_stmt->bind_param("ssi", $token_hash, $expires_at, $id);
                                    $update_stmt->execute();
                                    $update_stmt->close();

                                    // 4. Send the UN-HASHED token to the user's browser in a cookie
                                    setcookie('remember_me_token', $token, time() + 86400 * 30, "/"); // The '/' makes it available on the whole site
                                }
                            }
                            
                            // Redirect based on role
                            if ($user_type === 'coordinator') {
                                header("location: ../coordinator/dashboard.php");
                            } else {
                                header("location: dashboard.php");
                            }
                            exit;
                        } else {
                            $error = "Invalid password. Please try again.";
                        }
                    }
                } else {
                    $error = "No account found with that email address.";
                }
            } else {
                $error = "Oops! Something went wrong.";
            }
            $stmt->close();
        }
    } else {
        $error = "Please fill in both email and password.";
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">User Portal Login</h2>
        
        <?php if(!empty($error)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="post" class="space-y-4">
            <input type="email" name="email" placeholder="Email Address" class="w-full p-3 border border-gray-300 rounded-md" required>
            <input type="password" name="password" placeholder="Password" class="w-full p-3 border border-gray-300 rounded-md" required>
            
            <!-- New "Remember Me" Checkbox -->
            <div class="flex items-center justify-between">
                <label for="remember_me" class="flex items-center text-sm text-gray-600">
                    <input type="checkbox" name="remember_me" id="remember_me" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="ml-2">Remember me</span>
                </label>
            </div>
            
            <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-md transition duration-300">Login</button>
        </form>
        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">Don't have an account? <a href="register.php" class="text-indigo-600 hover:underline font-medium">Register here</a></p>
        </div>
    </div>
</body>
</html>