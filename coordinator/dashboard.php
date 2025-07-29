<?php
require_once '../config/db_connect.php';

// Security Check
if(!isset($_SESSION["user_loggedin"]) || $_SESSION["user_loggedin"] !== true || $_SESSION['user_type'] !== 'coordinator'){
    header("location: ../users/login.php");
    exit;
}

$coordinator_id = $_SESSION['user_id'];

// Fetch all events assigned to this coordinator
$sql = "SELECT e.event_id, e.event_name, e.event_date, e.venue, m.meet_name
        FROM event_assignments a
        JOIN events e ON a.event_id = e.event_id
        JOIN meets m ON e.meet_id = m.meet_id
        WHERE a.user_id = ?
        ORDER BY e.event_date ASC";

$assigned_events = [];
if($stmt = $conn->prepare($sql)){
    $stmt->bind_param("i", $coordinator_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $assigned_events[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center text-xl font-bold text-blue-600">Coordinator Portal</div>
                <div class="flex items-center">
                    <a href="../users/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <p class="text-gray-600 mb-8">Here are the events you have been assigned to coordinate.</p>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Your Assigned Events</h2>
            <div class="space-y-4">
                <?php if(empty($assigned_events)): ?>
                    <p class="text-center text-gray-500 py-10">You have not been assigned to any events yet.</p>
                <?php else: ?>
                    <?php foreach($assigned_events as $event): ?>
                        <div class="border border-gray-200 p-4 rounded-lg flex flex-col md:flex-row justify-between items-center hover:bg-gray-50">
                            <div>
                                <span class="text-sm font-semibold text-indigo-600 uppercase"><?php echo htmlspecialchars($event['meet_name']); ?></span>
                                <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($event['event_name']); ?></h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?php echo htmlspecialchars($event['venue']); ?> - 
                                    <span class="font-medium"><?php echo date("D, M j, Y - g:i A", strtotime($event['event_date'])); ?></span>
                                </p>
                            </div>
                            <div class="flex flex-wrap justify-end space-x-2 mt-4 md:mt-0">
                                <!-- New "View Participants" Button -->
                                <a href="view_participants.php?event_id=<?php echo $event['event_id']; ?>" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded transition duration-300">
                                    View Participants
                                </a>
                                <a href="scan.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition duration-300">
                                    Scan QR Codes
                                </a>
                                <a href="submit_results.php?event_id=<?php echo $event['event_id']; ?>" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded transition duration-300">
                                    Submit Results
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>