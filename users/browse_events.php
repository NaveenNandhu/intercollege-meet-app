<?php
require_once '../config/db_connect.php';

// Security Check
if(!isset($_SESSION["user_loggedin"]) || $_SESSION["user_loggedin"] !== true){
    header("location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// Get IDs of events the user has already registered for
$registered_event_ids = [];
$reg_sql = "SELECT event_id FROM registrations WHERE student_id = ?";
if($reg_stmt = $conn->prepare($reg_sql)){
    $reg_stmt->bind_param("i", $user_id);
    if($reg_stmt->execute()){
        $reg_result = $reg_stmt->get_result();
        while($row = $reg_result->fetch_assoc()){
            $registered_event_ids[] = $row['event_id'];
        }
    }
    $reg_stmt->close();
}

// Fetch all available, upcoming events. This query will now work correctly.
$events_sql = "SELECT e.event_id, e.event_name, e.event_date, m.meet_name, e.venue, e.description, e.event_type 
               FROM events e 
               JOIN meets m ON e.meet_id = m.meet_id 
               WHERE e.event_date > NOW() 
               ORDER BY e.event_date ASC";
$events = $conn->query($events_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <!-- Navbar -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4"><div class="flex justify-between h-16"><div class="flex items-center text-xl font-bold text-indigo-600">User Portal</div><div class="flex items-center"><a href="dashboard.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2">My Dashboard</a><a href="logout.php" class="ml-4 bg-red-500 text-white px-3 py-2 rounded-md">Logout</a></div></div></div>
    </nav>
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Browse & Register for Events</h1>
        
        <div id="feedback-message" class="hidden p-3 rounded mb-4 text-center"></div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if($events && $events->num_rows > 0): ?>
                <?php while($event = $events->fetch_assoc()): 
                    $is_registered = in_array($event['event_id'], $registered_event_ids);
                ?>
                <div class="bg-white p-6 rounded-lg shadow-md flex flex-col justify-between">
                    <div>
                        <span class="text-sm font-semibold text-indigo-600 uppercase"><?php echo htmlspecialchars($event['meet_name']); ?></span>
                        <h3 class="text-xl font-bold mt-1 text-gray-900"><?php echo htmlspecialchars($event['event_name']); ?></h3>
                        <p class="text-gray-600 mt-2 text-sm h-16 overflow-hidden"><?php echo htmlspecialchars($event['description']); ?></p>
                        <div class="mt-4 text-sm space-y-1">
                            <p><span class="font-semibold">When:</span> <?php echo date("D, M j, Y, g:i A", strtotime($event['event_date'])); ?></p>
                            <p><span class="font-semibold">Where:</span> <?php echo htmlspecialchars($event['venue']); ?></p>
                            <p><span class="font-semibold">Type:</span> <span class="capitalize bg-gray-200 px-2 py-1 rounded-full"><?php echo htmlspecialchars($event['event_type']); ?></span></p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button 
                            <?php if($is_registered) echo 'disabled'; ?>
                            onclick="registerForEvent(<?php echo $event['event_id']; ?>, this)"
                            class="w-full text-white font-bold py-2 px-4 rounded transition duration-300 <?php echo $is_registered ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-500 hover:bg-green-600'; ?>">
                            <?php echo $is_registered ? '✓ Already Registered' : 'Register Now'; ?>
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="col-span-full text-center text-gray-500 py-10">No upcoming events found at the moment. Please check back later!</p>
            <?php endif; ?>
        </div>
    </main>
    <script>
    function registerForEvent(eventId, buttonElement) {
        buttonElement.disabled = true;
        buttonElement.textContent = 'Registering...';
        fetch('../api/register_for_event.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json',},
            body: JSON.stringify({ event_id: eventId }),
        })
        .then(response => response.json())
        .then(data => {
            const feedbackDiv = document.getElementById('feedback-message');
            feedbackDiv.className = 'p-3 rounded mb-4 text-center';
            if (data.success) {
                feedbackDiv.textContent = data.message;
                feedbackDiv.classList.add('bg-green-100', 'text-green-800');
                buttonElement.textContent = '✓ Already Registered';
                buttonElement.classList.add('bg-gray-400', 'cursor-not-allowed');
            } else {
                feedbackDiv.textContent = 'Error: ' + data.message;
                feedbackDiv.classList.add('bg-red-100', 'text-red-800');
                buttonElement.disabled = false;
                buttonElement.textContent = 'Register Now';
            }
            feedbackDiv.classList.remove('hidden');
        })
        .catch(error => { /* ... */ });
    }
    </script>
</body>
</html>