<?php
require_once '../config/db_connect.php';

$feedback_message = '';
$feedback_type = 'error';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Form Validation ---
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $full_name = trim($_POST['full_name']);
    $college_name = trim($_POST['college_name']);
    $user_type = $_POST['user_type']; 
    $roll_number = trim($_POST['roll_number']); // Get the new roll number field

    // Simple validation
    if(empty($email) || empty($password) || empty($full_name) || empty($college_name) || empty($user_type)) {
        $feedback_message = "Please fill all required fields.";
    } elseif (strlen($password) < 6) {
        $feedback_message = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $sql_check = "SELECT student_id FROM users WHERE email = ?";
        if($stmt_check = $conn->prepare($sql_check)){
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $stmt_check->store_result();
            
            if($stmt_check->num_rows > 0){
                $feedback_message = "This email is already registered.";
            } else {
                // --- Email is available, proceed with registration ---
                $sql_insert = "INSERT INTO users (full_name, email, phone, college_name, user_type, roll_number, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
                if($stmt_insert = $conn->prepare($sql_insert)){
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $phone = trim($_POST['phone']);

                    // Bind all 7 parameters now
                    $stmt_insert->bind_param("sssssss", $full_name, $email, $phone, $college_name, $user_type, $roll_number, $hashed_password);
                    
                    if($stmt_insert->execute()){
                        $feedback_type = 'success';
                        $feedback_message = "Registration successful! You can now <a href='login.php' class='font-bold text-green-700 hover:underline'>login</a>.";
                    } else {
                        $feedback_message = "Something went wrong. Please try again later.";
                    }
                    $stmt_insert->close();
                }
            }
            $stmt_check->close();
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
    <title>User Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen py-10">
    <div class="w-full max-w-lg bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">Create an Account</h2>
        
        <?php if(!empty($feedback_message)): ?>
            <div class="<?php echo $feedback_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'; ?> p-3 rounded mb-4 text-center">
                <?php echo $feedback_message; ?>
            </div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-4">
            <input type="text" name="full_name" placeholder="Full Name *" class="w-full p-3 border border-gray-300 rounded-md" required>
            <input type="email" name="email" placeholder="Email Address *" class="w-full p-3 border border-gray-300 rounded-md" required>
            <input type="text" name="college_name" placeholder="College / Institution Name *" class="w-full p-3 border border-gray-300 rounded-md" required>
            
            <!-- New Roll Number Field -->
            <input type="text" name="roll_number" placeholder="Roll Number / Employee ID (if applicable)" class="w-full p-3 border border-gray-300 rounded-md">

            <input type="text" name="phone" placeholder="Phone Number (Optional)" class="w-full p-3 border border-gray-300 rounded-md">
            <div>
                <label for="user_type" class="block text-sm font-medium text-gray-700">I am a:</label>
                <select name="user_type" id="user_type" class="w-full p-3 border border-gray-300 rounded-md mt-1" required>
                    <option value="student">Student (Participant)</option>
                    <option value="coordinator">Faculty / Internal Student Volunteer</option>
                </select>
            </div>
            <input type="password" name="password" placeholder="Password (min. 6 characters) *" class="w-full p-3 border border-gray-300 rounded-md" required>
            <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-md transition duration-300">Register</button>
        </form>
         <div class="text-center mt-6">
            <p class="text-sm text-gray-600">Already have an account? <a href="login.php" class="text-indigo-600 hover:underline font-medium">Login here</a></p>
        </div>
    </div>
</body>
</html>