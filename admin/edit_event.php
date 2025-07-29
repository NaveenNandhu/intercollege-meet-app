<?php
require_once '../config/db_connect.php';

// Security Check
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Get event ID from URL
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$event_id) {
    header("location: manage_events.php");
    exit;
}

$feedback_message = '';

// --- Handle form submission for UPDATING the event ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $meet_id = $_POST['meet_id'];
    $event_name = trim($_POST['event_name']);
    $description = trim($_POST['description']);
    $event_date = trim($_POST['event_date']);
    $venue = trim($_POST['venue']);
    $event_type = $_POST['event_type'];

    // Convert empty datetime to NULL
    $event_date_for_db = !empty($event_date) ? date("Y-m-d H:i:s", strtotime($event_date)) : null;

    $sql = "UPDATE events SET meet_id = ?, event_name = ?, description = ?, event_date = ?, venue = ?, event_type = ? WHERE event_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("isssssi", $meet_id, $event_name, $description, $event_date_for_db, $venue, $event_type, $event_id);
        if ($stmt->execute()) {
            header("location: manage_events.php");
            exit;
        } else {
            $feedback_message = "<div class='bg-red-100 text-red-800 p-3 rounded'>Error updating event.</div>";
        }
        $stmt->close();
    }
}

// --- Fetch existing event data to pre-fill the form ---
$sql_fetch = "SELECT meet_id, event_name, description, event_date, venue, event_type FROM events WHERE event_id = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $event_id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();
if ($result->num_rows === 1) {
    $event = $result->fetch_assoc();
} else {
    // If no event found, redirect
    header("location: manage_events.php");
    exit;
}
$stmt_fetch->close();

// Fetch all meets for the dropdown
$meets = $conn->query("SELECT meet_id, meet_name FROM meets");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <?php include '_sidebar.php'; ?>
        
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Event</h1>
            <?php echo $feedback_message; ?>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <form action="edit_event.php?id=<?php echo $event_id; ?>" method="post" class="space-y-4">
                    <div>
                        <label for="meet_id" class="block font-bold">Meet</label>
                        <select name="meet_id" id="meet_id" class="w-full p-2 border rounded mt-1" required>
                            <?php while($meet = $meets->fetch_assoc()): ?>
                                <option value="<?php echo $meet['meet_id']; ?>" <?php echo ($meet['meet_id'] == $event['meet_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($meet['meet_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="event_name" class="block font-bold">Event Name</label>
                        <input type="text" name="event_name" id="event_name" value="<?php echo htmlspecialchars($event['event_name']); ?>" class="w-full p-2 border rounded mt-1" required>
                    </div>
                    <div>
                        <label for="description" class="block font-bold">Description</label>
                        <textarea name="description" id="description" class="w-full p-2 border rounded mt-1"><?php echo htmlspecialchars($event['description']); ?></textarea>
                    </div>
                    <div>
                        <label for="event_date" class="block font-bold">Event Date & Time</label>
                        <input type="datetime-local" name="event_date" id="event_date" value="<?php echo !empty($event['event_date']) ? date('Y-m-d\TH:i', strtotime($event['event_date'])) : ''; ?>" class="w-full p-2 border rounded mt-1" required>
                    </div>
                     <div>
                        <label for="venue" class="block font-bold">Venue</label>
                        <input type="text" name="venue" id="venue" value="<?php echo htmlspecialchars($event['venue']); ?>" class="w-full p-2 border rounded mt-1" required>
                    </div>
                    <div>
                        <label for="event_type" class="block font-bold">Event Type</label>
                        <select name="event_type" id="event_type" class="w-full p-2 border rounded mt-1" required>
                            <option value="individual" <?php echo ($event['event_type'] == 'individual') ? 'selected' : ''; ?>>Individual</option>
                            <option value="team" <?php echo ($event['event_type'] == 'team') ? 'selected' : ''; ?>>Team</option>
                        </select>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700">Save Changes</button>
                        <a href="manage_events.php" class="text-gray-600 ml-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>