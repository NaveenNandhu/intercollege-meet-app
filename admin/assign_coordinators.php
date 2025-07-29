<?php
require_once '../config/db_connect.php';

// Security Check
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: index.php");
    exit;
}

$feedback_message = '';
$feedback_type = 'error';

// --- Handle form submission for assigning MULTIPLE coordinators to ONE event ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_assignments'])) {
    $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
    $assigned_user_ids = $_POST['user_ids'] ?? []; // This will be an array of selected user IDs

    if (empty($event_id)) {
        $feedback_message = "Please select an event first.";
    } else {
        // --- Smart Update Logic: Delete old assignments, then insert new ones ---
        
        // 1. First, delete all existing assignments for this event.
        $delete_sql = "DELETE FROM event_assignments WHERE event_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $event_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        // 2. Now, insert the new set of assignments.
        if (!empty($assigned_user_ids)) {
            $insert_sql = "INSERT INTO event_assignments (event_id, user_id) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            
            $success_count = 0;
            foreach ($assigned_user_ids as $user_id) {
                $insert_stmt->bind_param("ii", $event_id, $user_id);
                if ($insert_stmt->execute()) {
                    $success_count++;
                }
            }
            $insert_stmt->close();
            
            $feedback_type = 'success';
            $feedback_message = "Successfully saved " . $success_count . " coordinator assignments for the event.";
        } else {
            // This case handles when all coordinators are un-checked
            $feedback_type = 'success';
            $feedback_message = "All coordinators have been unassigned from this event.";
        }
    }
}

// --- Fetch data for the page ---

// 1. Fetch all events for the primary dropdown
$events_sql = "SELECT e.event_id, e.event_name, m.meet_name 
               FROM events e JOIN meets m ON e.meet_id = m.meet_id 
               ORDER BY m.meet_name, e.event_date DESC";
$events_result = $conn->query($events_sql);

// 2. Fetch all potential coordinators
$coordinators_sql = "SELECT student_id, full_name, roll_number FROM users WHERE user_type = 'coordinator' ORDER BY full_name";
$coordinators_result = $conn->query($coordinators_sql);
$all_coordinators = $coordinators_result->fetch_all(MYSQLI_ASSOC);

// 3. Fetch all existing assignments to display in the main table at the bottom
$assignments_sql = "SELECT u.full_name, u.roll_number, e.event_name 
                    FROM event_assignments a
                    JOIN users u ON a.user_id = u.student_id
                    JOIN events e ON a.event_id = e.event_id
                    ORDER BY e.event_name, u.full_name";
$assignments_result = $conn->query($assignments_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Coordinators</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <?php include '_sidebar.php'; ?>
        
        <div class="flex-1 p-10 overflow-y-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Assign Coordinators to Events</h1>

            <?php if(!empty($feedback_message)): ?>
                <div class="p-3 rounded mb-6 text-center <?php echo $feedback_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $feedback_message; ?>
                </div>
            <?php endif; ?>

            <!-- Assignment Form -->
            <form id="assignment_form" action="assign_coordinators.php" method="post">
                <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Step 1: Select an Event</h2>
                    <select name="event_id" id="event_selector" class="mt-1 block w-full md:w-1/2 p-2 border border-gray-300 rounded-md" required>
                        <option value="">-- Choose an Event to Manage --</option>
                        <?php while($event = $events_result->fetch_assoc()): ?>
                            <option value="<?php echo $event['event_id']; ?>">
                                <?php echo htmlspecialchars($event['meet_name']) . ' - ' . htmlspecialchars($event['event_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div id="coordinators_section" class="bg-white p-6 rounded-lg shadow-md mb-8 hidden">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Step 2: Assign Coordinators</h2>
                    <p class="text-sm text-gray-500 mb-4">Check the boxes for all faculty or volunteers you want to assign to this event.</p>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <?php foreach($all_coordinators as $coordinator): ?>
                            <label class="flex items-center space-x-3 p-2 rounded hover:bg-gray-100">
                                <input type="checkbox" name="user_ids[]" value="<?php echo $coordinator['student_id']; ?>" class="h-5 w-5 text-indigo-600 border-gray-300 rounded coordinator-checkbox">
                                <div>
                                    <span class="font-medium"><?php echo htmlspecialchars($coordinator['full_name']); ?></span>
                                    <span class="text-xs text-gray-500">(ID: <?php echo htmlspecialchars($coordinator['roll_number'] ?: 'N/A'); ?>)</span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" name="save_assignments" class="mt-6 w-full bg-indigo-600 text-white font-bold py-3 px-4 rounded hover:bg-indigo-700">
                        Save Assignments for this Event
                    </button>
                </div>
            </form>
            
            <!-- Existing Assignments Table -->
            <div class="bg-white p-6 rounded-lg shadow-md mt-8">
                <h2 class="text-xl font-bold mb-4">Current Assignment Overview</h2>
                <div class="overflow-x-auto">
                    <!-- Table content remains the same as before -->
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned Coordinator</th>
                                <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Coordinator ID</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($assignments_result && $assignments_result->num_rows > 0): ?>
                                <?php while($assignment = $assignments_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 font-medium text-gray-900"><?php echo htmlspecialchars($assignment['event_name']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo htmlspecialchars($assignment['full_name']); ?></td>
                                    <td class="p-3 text-gray-500"><?php echo htmlspecialchars($assignment['roll_number']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center p-4 text-gray-500">No coordinators assigned to any event yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const eventSelector = document.getElementById('event_selector');
            const coordinatorsSection = document.getElementById('coordinators_section');
            const checkboxes = document.querySelectorAll('.coordinator-checkbox');

            eventSelector.addEventListener('change', function() {
                const eventId = this.value;

                // First, uncheck all boxes
                checkboxes.forEach(cb => cb.checked = false);

                if (!eventId) {
                    coordinatorsSection.classList.add('hidden');
                    return;
                }

                // Show the coordinator list
                coordinatorsSection.classList.remove('hidden');

                // Fetch the current assignments for this event to pre-check the boxes
                fetch(`../api/get_assignments_by_event.php?event_id=${eventId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.assigned_user_ids) {
                            checkboxes.forEach(checkbox => {
                                // Check if the checkbox's value (user_id) is in the array of assigned IDs
                                if (data.assigned_user_ids.includes(checkbox.value)) {
                                    checkbox.checked = true;
                                }
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching assignments:', error));
            });
        });
    </script>
</body>
</html>
