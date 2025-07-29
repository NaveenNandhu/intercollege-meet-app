<?php
require_once '../config/db_connect.php';

// --- COORDINATOR-SPECIFIC SECURITY CHECK ---
if(!isset($_SESSION["user_loggedin"]) || $_SESSION["user_loggedin"] !== true || $_SESSION['user_type'] !== 'coordinator'){
    header("location: ../users/login.php");
    exit;
}
$coordinator_id = $_SESSION['user_id'];

// Get the event_id from the URL
$event_id = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
if (!$event_id) {
    header("location: dashboard.php");
    exit;
}

$feedback_message = '';
$feedback_type = 'error';

// --- Handle form submission for MULTIPLE winners ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction(); // Use a transaction for data integrity

    try {
        // Find or create the main result entry for this event
        $result_id = null;
        $check_sql = "SELECT result_id FROM results WHERE event_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $event_id);
        $check_stmt->execute();
        $check_stmt->bind_result($result_id);
        $check_stmt->fetch();
        $check_stmt->close();

        if (!$result_id) {
            // No result entry exists, create one
            $insert_result_sql = "INSERT INTO results (event_id, submitted_by_coordinator_id) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_result_sql);
            $insert_stmt->bind_param("ii", $event_id, $coordinator_id);
            $insert_stmt->execute();
            $result_id = $insert_stmt->insert_id;
            $insert_stmt->close();
        }

        // Clear any old winners for this result to prevent duplicates
        $delete_winners_sql = "DELETE FROM result_winners WHERE result_id = ?";
        $delete_stmt = $conn->prepare($delete_winners_sql);
        $delete_stmt->bind_param("i", $result_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        // Prepare to insert the new winners
        $insert_winner_sql = "INSERT INTO result_winners (result_id, user_id, position) VALUES (?, ?, ?)";
        $winner_stmt = $conn->prepare($insert_winner_sql);

        $positions = [
            1 => $_POST['first_place_users'] ?? [],
            2 => $_POST['second_place_users'] ?? [],
            3 => $_POST['third_place_users'] ?? []
        ];

        // Loop through and insert each selected winner
        foreach ($positions as $position => $user_ids) {
            foreach ($user_ids as $user_id) {
                $winner_stmt->bind_param("iii", $result_id, $user_id, $position);
                $winner_stmt->execute();
            }
        }
        $winner_stmt->close();

        $conn->commit(); // Save all changes if no errors occurred
        $feedback_type = 'success';
        $feedback_message = "Results have been saved successfully!";

    } catch (Exception $e) {
        $conn->rollback(); // Undo all changes if an error occurred
        $feedback_message = "An error occurred while saving the results: " . $e->getMessage();
    }
}

// --- Fetch data for the page ---
$event_name_sql = "SELECT event_name FROM events WHERE event_id = ?";
$event_stmt = $conn->prepare($event_name_sql);
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_name = $event_stmt->get_result()->fetch_assoc()['event_name'];
$event_stmt->close();

$participants_sql = "SELECT u.student_id, u.full_name FROM users u JOIN registrations r ON u.student_id = r.student_id WHERE r.event_id = ? ORDER BY u.full_name";
$part_stmt = $conn->prepare($participants_sql);
$part_stmt->bind_param("i", $event_id);
$part_stmt->execute();
$participants = $part_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$part_stmt->close();

// Fetch current winners to pre-select them in the form
$current_winners = [1 => [], 2 => [], 3 => []];
$winners_sql = "SELECT rw.user_id, rw.position 
                FROM result_winners rw 
                JOIN results r ON rw.result_id = r.result_id 
                WHERE r.event_id = ?";
$win_stmt = $conn->prepare($winners_sql);
$win_stmt->bind_param("i", $event_id);
$win_stmt->execute();
$result = $win_stmt->get_result();
while($row = $result->fetch_assoc()) {
    $current_winners[$row['position']][] = $row['user_id'];
}
$win_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Team Results</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <main class="max-w-4xl mx-auto py-10 px-4">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Submit Results</h1>
                    <p class="text-xl text-indigo-600 font-semibold"><?php echo htmlspecialchars($event_name); ?></p>
                </div>
                <a href="dashboard.php" class="text-blue-500 hover:underline">&larr; Back to Dashboard</a>
            </div>

            <?php if(!empty($feedback_message)): ?>
                <div class="p-3 rounded mb-6 text-center <?php echo $feedback_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $feedback_message; ?>
                </div>
            <?php endif; ?>

            <form action="submit_results.php?event_id=<?php echo $event_id; ?>" method="post" class="space-y-8">
                <?php if (empty($participants)): ?>
                    <p class="text-center text-gray-500 py-10">No participants have registered for this event yet.</p>
                <?php else: ?>
                    <!-- 1st Place Multi-Select -->
                    <div>
                        <label for="first_place_users" class="block text-lg font-medium text-gray-700">🥇 First Place Winners</label>
                        <p class="text-sm text-gray-500">Hold Ctrl (or Cmd on Mac) to select multiple students for a team prize.</p>
                        <select name="first_place_users[]" id="first_place_users" multiple class="mt-1 block w-full p-2 border rounded-md h-40">
                            <?php foreach ($participants as $p): ?>
                                <option value="<?php echo $p['student_id']; ?>" <?php echo in_array($p['student_id'], $current_winners[1]) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- 2nd Place Multi-Select -->
                    <div>
                        <label for="second_place_users" class="block text-lg font-medium text-gray-700">🥈 Second Place Winners</label>
                        <select name="second_place_users[]" id="second_place_users" multiple class="mt-1 block w-full p-2 border rounded-md h-40">
                             <?php foreach ($participants as $p): ?>
                                <option value="<?php echo $p['student_id']; ?>" <?php echo in_array($p['student_id'], $current_winners[2]) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- 3rd Place Multi-Select -->
                    <div>
                        <label for="third_place_users" class="block text-lg font-medium text-gray-700">🥉 Third Place Winners</label>
                        <select name="third_place_users[]" id="third_place_users" multiple class="mt-1 block w-full p-2 border rounded-md h-40">
                             <?php foreach ($participants as $p): ?>
                                <option value="<?php echo $p['student_id']; ?>" <?php echo in_array($p['student_id'], $current_winners[3]) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-md text-lg">
                            Save Results
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </main>
</body>
</html>