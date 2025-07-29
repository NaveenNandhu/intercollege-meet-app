<?php
require_once '../config/db_connect.php';

// If a coordinator is already logged in, redirect to their new dashboard
if(isset($_SESSION["coordinator_loggedin"]) && $_SESSION["coordinator_loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if(empty($email) || empty($password)){
        $error = "Please enter both email and password.";
    } else {
        // Step 1: Validate user credentials against the 'users' table
        $sql = "SELECT student_id, full_name, email, password FROM users WHERE email = ?";
        
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $email);
            
            if($stmt->execute()){
                $stmt->store_result();
                
                if($stmt->num_rows == 1){
                    $stmt->bind_result($user_id, $full_name, $db_email, $hashed_password);
                    if($stmt->fetch()){
                        // Verify password
                        if(password_verify($password, $hashed_password)){
                            
                            // Step 2: Check if this valid user has been assigned to any event
                            $assignment_sql = "SELECT COUNT(assignment_id) as assignment_count FROM event_assignments WHERE user_id = ?";
                            if($assign_stmt = $conn->prepare($assignment_sql)) {
                                $assign_stmt->bind_param("i", $user_id);
                                $assign_stmt->execute();
                                $assign_stmt->bind_result($assignment_count);
                                $assign_stmt->fetch();
                                $assign_stmt->close();

                                if ($assignment_count > 0) {
                                    // SUCCESS! User is valid and is an assigned coordinator.
                                    // Store data in session variables
                                    $_SESSION["coordinator_loggedin"] = true;
                                    $_SESSION["coordinator_id"] = $user_id;
                                    $_SESSION["coordinator_name"] = $full_name;
                                    
                                    // Redirect to the coordinator dashboard
                                    header("location: dashboard.php");
                                    exit;
                                } else {
                                    $error = "Login successful, but you have not been assigned to coordinate any events.";
                                }
                            }
                        } else {
                            $error = "Invalid password.";
                        }
                    }
                } else {
                    $error = "No user account found with that email.";
                }
            } else {
                $error = "Oops! Something went wrong.";
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
    <title>Coordinator Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">Coordinator Portal</h2>
        <p class="text-center text-gray-500 mb-6">Log in with your registered user account to access coordinator tools.</p>
        
        <?php if(!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post" class="space-y-4">
            <div>
                <label for="email" class="sr-only">Email</label>
                <input type="email" name="email" id="email" placeholder="Email Address" class="w-full p-3 border border-gray-300 rounded-md" required>
            </div>
            <div>
                <label for="password" class="sr-only">Password</label>
                <input type="password" name="password" id="password" placeholder="Password" class="w-full p-3 border border-gray-300 rounded-md" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-md transition duration-300">
                Login as Coordinator
            </button>
        </form>
    </div>
</body>
</html>