<?php
require_once '../config/db_connect.php';

// Security check: Ensure a coordinator is logged in.
if(!isset($_SESSION["user_loggedin"]) || $_SESSION["user_loggedin"] !== true || $_SESSION['user_type'] !== 'coordinator'){
    header("location: ../users/login.php");
    exit;
}

// Get the event_id from the URL and validate it.
$event_id = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
if (!$event_id) {
    header("location: dashboard.php");
    exit;
}

// --- Fetch data for the page ---
// 1. Get the name of the event for the page title.
$event_sql = "SELECT event_name FROM events WHERE event_id = ?";
$event_stmt = $conn->prepare($event_sql);
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_stmt->bind_result($event_name);
$event_stmt->fetch();
$event_stmt->close();

if (empty($event_name)) {
    // If no event is found with that ID, redirect back to the dashboard.
    header("location: dashboard.php");
    exit;
}

// 2. Get all participants who are registered for this specific event.
$participants_sql = "SELECT u.full_name, u.roll_number, u.college_name 
                     FROM registrations r
                     JOIN users u ON r.student_id = u.student_id
                     WHERE r.event_id = ?
                     ORDER BY u.full_name ASC";
$participants = [];
if ($part_stmt = $conn->prepare($participants_sql)) {
    $part_stmt->bind_param("i", $event_id);
    $part_stmt->execute();
    $result = $part_stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $participants[] = $row;
    }
    $part_stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participant List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <main class="max-w-4xl mx-auto py-10 px-4">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <div class="flex flex-col md:flex-row justify-between md:items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Participant List</h1>
                    <p class="text-xl text-indigo-600 font-semibold"><?php echo htmlspecialchars($event_name); ?></p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-3">
                    <a href="dashboard.php" class="text-blue-500 hover:underline py-2">&larr; Back to Dashboard</a>
                    
                    <!-- *** THE FIX IS HERE *** -->
                    <!-- The Export button will only be displayed if there are participants -->
                    <?php if (!empty($participants)): ?>
                        <a href="export_participants_pdf.php?event_id=<?php echo $event_id; ?>" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded transition duration-300">
                            Export as PDF
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Participant Name</th>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Roll Number / ID</th>
                            <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">College</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($participants)): ?>
                            <tr>
                                <td colspan="3" class="text-center p-4 text-gray-500">No participants have registered for this event yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($participants as $participant): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 font-medium text-gray-900"><?php echo htmlspecialchars($participant['full_name']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo htmlspecialchars($participant['roll_number'] ?: 'N/A'); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo htmlspecialchars($participant['college_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>