<?php
require_once '../config/db_connect.php'; // Includes the database connection

// --- NEW, MORE ROBUST QUERY ---
// This version uses subqueries to ensure that results are shown even if only one winner is selected.
$sql = "SELECT 
            e.event_name,
            r.submitted_at,
            (SELECT GROUP_CONCAT(u.full_name ORDER BY u.full_name SEPARATOR ', ') FROM result_winners rw JOIN users u ON rw.user_id = u.student_id WHERE rw.result_id = r.result_id AND rw.position = 1) AS first_place_winners,
            (SELECT GROUP_CONCAT(u.full_name ORDER BY u.full_name SEPARATOR ', ') FROM result_winners rw JOIN users u ON rw.user_id = u.student_id WHERE rw.result_id = r.result_id AND rw.position = 2) AS second_place_winners,
            (SELECT GROUP_CONCAT(u.full_name ORDER BY u.full_name SEPARATOR ', ') FROM result_winners rw JOIN users u ON rw.user_id = u.student_id WHERE rw.result_id = r.result_id AND rw.position = 3) AS third_place_winners
        FROM 
            results r
        JOIN 
            events e ON r.event_id = e.event_id
        ORDER BY 
            e.event_date DESC";

$results = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Results</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <a href="../index.html" class="flex items-center text-xl font-bold text-indigo-600">Event Hub</a>
                <div class="flex items-center">
                    <?php if(isset($_SESSION["user_loggedin"]) && $_SESSION["user_loggedin"] === true): ?>
                        <a href="dashboard.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">My Dashboard</a>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-5xl mx-auto py-10 px-4">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Official Event Results</h1>

        <div class="space-y-6">
            <?php if ($results && $results->num_rows > 0): ?>
                <?php while($row = $results->fetch_assoc()): ?>
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h2 class="text-2xl font-bold text-indigo-700 mb-4"><?php echo htmlspecialchars($row['event_name']); ?></h2>
                        <div class="space-y-3">
                            <div class="flex items-start bg-yellow-100 p-3 rounded-md">
                                <span class="text-2xl mr-4 pt-1">🥇</span>
                                <div>
                                    <span class="text-lg font-semibold text-yellow-800">1st Place:</span>
                                    <p class="ml-2 text-lg text-gray-800"><?php echo htmlspecialchars($row['first_place_winners'] ?: 'Not Announced'); ?></p>
                                </div>
                            </div>
                            <div class="flex items-start bg-gray-200 p-3 rounded-md">
                                <span class="text-2xl mr-4 pt-1">🥈</span>
                                <div>
                                    <span class="text-lg font-semibold text-gray-700">2nd Place:</span>
                                    <p class="ml-2 text-lg text-gray-800"><?php echo htmlspecialchars($row['second_place_winners'] ?: 'Not Announced'); ?></p>
                                </div>
                            </div>
                            <div class="flex items-start bg-orange-200 p-3 rounded-md">
                                <span class="text-2xl mr-4 pt-1">🥉</span>
                                <div>
                                    <span class="text-lg font-semibold text-orange-800">3rd Place:</span>
                                    <p class="ml-2 text-lg text-gray-800"><?php echo htmlspecialchars($row['third_place_winners'] ?: 'Not Announced'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center bg-white p-10 rounded-lg shadow-md">
                    <p class="text-gray-500 text-lg">No results have been published yet. Please check back soon!</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>