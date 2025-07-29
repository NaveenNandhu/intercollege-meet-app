<?php
// Must include the database connection, which also starts the session.
require_once '../config/db_connect.php';

// Security Check: If the admin is not logged in, redirect them to the login page.
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: index.php");
    exit;
}

// --- Function to safely execute a count query ---
function getCount($conn, $sql) {
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_assoc()['count'];
    }
    return 0; // Return 0 if the query fails for any reason
}

// --- Fetch Statistics from the Database using the new table name 'users' ---

// Count total meets
$meets_count = getCount($conn, "SELECT COUNT(meet_id) as count FROM meets");

// Count total events
$events_count = getCount($conn, "SELECT COUNT(event_id) as count FROM events");

// Count total users registered as 'student'
$students_count = getCount($conn, "SELECT COUNT(student_id) as count FROM users WHERE user_type = 'student'");

// Count total users registered as 'coordinator'
$coordinators_count = getCount($conn, "SELECT COUNT(student_id) as count FROM users WHERE user_type = 'coordinator'");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include '_sidebar.php'; // Include the reusable sidebar ?>
        
        <!-- Main Content -->
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Admin Dashboard</h1>
            <p class="text-gray-600 mb-8">This is the central dashboard. From here you can manage all aspects of the inter-college meets.</p>
            
            <!-- Updated Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                 <div class="bg-white p-6 rounded-lg shadow-md transition-transform transform hover:scale-105">
                    <h3 class="font-bold text-lg text-gray-600 mb-2">Total Meets</h3>
                    <p class="text-4xl font-bold text-indigo-600"><?php echo $meets_count; ?></p>
                 </div>
                 <div class="bg-white p-6 rounded-lg shadow-md transition-transform transform hover:scale-105">
                    <h3 class="font-bold text-lg text-gray-600 mb-2">Total Events</h3>
                    <p class="text-4xl font-bold text-green-600"><?php echo $events_count; ?></p>
                 </div>
                 <div class="bg-white p-6 rounded-lg shadow-md transition-transform transform hover:scale-105">
                    <h3 class="font-bold text-lg text-gray-600 mb-2">Student Participants</h3>
                    <p class="text-4xl font-bold text-blue-600"><?php echo $students_count; ?></p>
                 </div>
                 <div class="bg-white p-6 rounded-lg shadow-md transition-transform transform hover:scale-105">
                    <h3 class="font-bold text-lg text-gray-600 mb-2">Registered Coordinators</h3>
                    <p class="text-4xl font-bold text-yellow-500"><?php echo $coordinators_count; ?></p>
                 </div>
            </div>
        </div>
    </div>
</body>
</html>