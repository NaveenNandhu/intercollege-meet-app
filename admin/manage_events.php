<?php
require_once '../config/db_connect.php';

// Security Check
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: index.php");
    exit;
}

$feedback_message = "";
// Logic for creating an event (remains the same)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_event'])) {
    $meet_id = $_POST['meet_id'];
    $event_name = trim($_POST['event_name']);
    $description = trim($_POST['description']);
    $event_date = trim($_POST['event_date']);
    $venue = trim($_POST['venue']);
    $event_type = $_POST['event_type'];
    
    $sql = "INSERT INTO events (meet_id, event_name, description, event_date, venue, event_type) VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("isssss", $meet_id, $event_name, $description, $event_date, $venue, $event_type);
        if ($stmt->execute()) {
            $feedback_message = "<div class='bg-green-100 text-green-800 p-3 rounded mb-4'>Event created successfully!</div>";
        } else {
            $feedback_message = "<div class='bg-red-100 text-red-800 p-3 rounded mb-4'>Error creating event.</div>";
        }
        $stmt->close();
    }
}

// Fetch meets for the creation dropdown
$meets = $conn->query("SELECT meet_id, meet_name FROM meets");
// Fetch all events with meet name
$events = $conn->query("SELECT e.event_id, e.event_name, m.meet_name, e.event_date 
                        FROM events e 
                        JOIN meets m ON e.meet_id = m.meet_id 
                        ORDER BY e.event_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <?php include '_sidebar.php'; ?>
        
        <div class="flex-1 p-10 overflow-y-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Events</h1>
            <?php echo $feedback_message; ?>

            <!-- Add New Event Form -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h2 class="text-xl font-bold mb-4">Add New Event</h2>
                <form action="manage_events.php" method="post" class="space-y-4">
                    <div>
                        <label>Select Meet</label>
                        <select name="meet_id" class="w-full p-2 border rounded mt-1" required>
                            <option value="">-- Choose a Meet --</option>
                            <?php while($meet = $meets->fetch_assoc()): ?>
                                <option value="<?php echo $meet['meet_id']; ?>"><?php echo htmlspecialchars($meet['meet_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <input type="text" name="event_name" placeholder="Event Name" class="w-full p-2 border rounded" required>
                    <textarea name="description" placeholder="Description" class="w-full p-2 border rounded"></textarea>
                    <input type="datetime-local" name="event_date" class="w-full p-2 border rounded" required>
                    <input type="text" name="venue" placeholder="Venue" class="w-full p-2 border rounded" required>
                    <select name="event_type" class="w-full p-2 border rounded" required>
                        <option value="individual">Individual</option>
                        <option value="team">Team</option>
                    </select>
                    <button type="submit" name="create_event" class="bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700">Add Event</button>
                </form>
            </div>

            <!-- Existing Events Table -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Existing Events</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Meet</th>
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <!-- New Column for Actions -->
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($events && $events->num_rows > 0): ?>
                            <?php while($event = $events->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-medium text-gray-900"><?php echo htmlspecialchars($event['event_name']); ?></td>
                                <td class="p-3 text-gray-500"><?php echo htmlspecialchars($event['meet_name']); ?></td>
                                <td class="p-3 text-gray-500"><?php echo date("M j, Y, g:i A", strtotime($event['event_date'])); ?></td>
                                <td class="p-3 text-sm font-medium">
                                    <!-- New Edit and Delete Links -->
                                    <a href="edit_event.php?id=<?php echo $event['event_id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <a href="delete_event.php?id=<?php echo $event['event_id']; ?>" class="text-red-600 hover:text-red-900 ml-4" onclick="return confirm('Are you sure you want to delete this event? All registrations and assignments for it will also be removed.');">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center p-4 text-gray-500">No events found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>