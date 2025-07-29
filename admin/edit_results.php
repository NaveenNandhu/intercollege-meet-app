<?php
require_once '../config/db_connect.php';
require_once '../lib/certificate_generator.php';
require_once '../lib/email_sender.php';

// Security Check
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: index.php");
    exit;
}
$admin_id = $_SESSION['admin_id'];

$event_id = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
if (!$event_id) {
    header("location: release_results.php");
    exit;
}

$feedback_message = '';
$feedback_type = 'error';

// --- Handle form submission for MULTIPLE winners ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction(); 

    try {
        // Find or create the main result entry
        $result_id = null;
        $check_sql = "SELECT result_id FROM results WHERE event_id = ?";
        if($check_stmt = $conn->prepare($check_sql)) {
            $check_stmt->bind_param("i", $event_id);
            $check_stmt->execute();
            $check_stmt->bind_result($result_id);
            $check_stmt->fetch();
            $check_stmt->close();
        }

        if (!$result_id) {
            $insert_result_sql = "INSERT INTO results (event_id, submitted_by_coordinator_id) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_result_sql);
            $insert_stmt->bind_param("ii", $event_id, $admin_id);
            $insert_stmt->execute();
            $result_id = $insert_stmt->insert_id;
            $insert_stmt->close();
        }

        // Clear old winners and insert new ones
        $delete_winners_sql = "DELETE FROM result_winners WHERE result_id = ?";
        $delete_stmt = $conn->prepare($delete_winners_sql);
        $delete_stmt->bind_param("i", $result_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        $positions = [
            1 => $_POST['first_place_users'] ?? [],
            2 => $_POST['second_place_users'] ?? [],
            3 => $_POST['third_place_users'] ?? []
        ];
        
        $insert_winner_sql = "INSERT INTO result_winners (result_id, user_id, position) VALUES (?, ?, ?)";
        $winner_stmt = $conn->prepare($insert_winner_sql);
        
        $all_winner_ids = [];
        foreach ($positions as $position => $user_ids) {
            foreach ($user_ids as $user_id) {
                $winner_stmt->bind_param("iii", $result_id, $user_id, $position);
                $winner_stmt->execute();
                if(!in_array($user_id, $all_winner_ids)) {
                    $all_winner_ids[] = $user_id;
                }
            }
        }
        $winner_stmt->close();
        
        $conn->commit();
        $feedback_type = 'success';
        $feedback_message = "Results saved successfully!";

        // --- EFFICIENT EMAIL LOGIC ---
        if (!empty($all_winner_ids)) {
            // 1. Fetch event name once
            $event_name_res = $conn->query("SELECT event_name FROM events WHERE event_id = $event_id");
            $event_name = $event_name_res->fetch_assoc()['event_name'];

            // 2. Fetch all winners' details in a single query
            $id_placeholders = implode(',', array_fill(0, count($all_winner_ids), '?'));
            $user_info_sql = "SELECT student_id, full_name, email FROM users WHERE student_id IN ($id_placeholders)";
            $user_stmt = $conn->prepare($user_info_sql);
            $user_stmt->bind_param(str_repeat('i', count($all_winner_ids)), ...$all_winner_ids);
            $user_stmt->execute();
            $winners_data_result = $user_stmt->get_result();
            $winners_details = [];
            while($row = $winners_data_result->fetch_assoc()) {
                $winners_details[$row['student_id']] = $row;
            }
            $user_stmt->close();

            // 3. Loop through winners and send emails
            foreach ($positions as $rank_num => $user_ids) {
                $rank_text = ($rank_num == 1) ? '1st Place' : (($rank_num == 2) ? '2nd Place' : '3rd Place');
                foreach($user_ids as $user_id) {
                    $winner = $winners_details[$user_id];
                    $certificate_path = generate_certificate($conn, $user_id, $event_id, 'Winner', $rank_text);
                    if ($certificate_path) {
                        $subject = "Congratulations! You're a Winner!";
                        $body = "Dear " . $winner['full_name'] . ",<br><br>Congratulations on securing " . $rank_text . " in the event: <strong>" . $event_name . "</strong>.<br><br>Please find your certificate attached.<br><br>Best Regards,<br>The Event Committee";
                        if (send_certificate_email($winner['email'], $winner['full_name'], $subject, $body, $certificate_path)) {
                            $feedback_message .= "<br>Emailed certificate to " . $winner['full_name'] . ".";
                        } else {
                            $feedback_message .= "<br><span class='text-red-500'>Failed to email " . $winner['full_name'] . ".</span>";
                        }
                        unlink($certificate_path);
                    }
                }
            }
        }
    } catch (Exception $e) {
        $conn->rollback();
        $feedback_message = "An error occurred: " . $e->getMessage();
    }
}


// --- Fetch data for the page (remains the same) ---
$event_name_res = $conn->query("SELECT event_name FROM events WHERE event_id = $event_id");
$event_name = $event_name_res ? $event_name_res->fetch_assoc()['event_name'] : 'Unknown Event';

$participants_sql = "SELECT u.student_id, u.full_name FROM users u JOIN registrations r ON u.student_id = r.student_id WHERE r.event_id = ? ORDER BY u.full_name";
$part_stmt = $conn->prepare($participants_sql);
$part_stmt->bind_param("i", $event_id);
$part_stmt->execute();
$participants = $part_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$part_stmt->close();

$current_winners = [1 => [], 2 => [], 3 => []];
$winners_sql = "SELECT rw.user_id, rw.position FROM result_winners rw JOIN results r ON rw.result_id = r.result_id WHERE r.event_id = ?";
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
    <title>Edit Team Results</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <?php include '_sidebar.php'; ?>
        <main class="flex-1 p-10 overflow-y-auto">
            <div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Edit/Release Team Results</h1>
                        <p class="text-xl text-indigo-600 font-semibold"><?php echo htmlspecialchars($event_name); ?></p>
                    </div>
                    <a href="release_results.php" class="text-blue-500 hover:underline">&larr; Back to Event Selection</a>
                </div>

                <?php if(!empty($feedback_message)): ?>
                    <div class="p-3 rounded mb-6 text-center <?php echo $feedback_type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'; ?>">
                        <?php echo $feedback_message; ?>
                    </div>
                <?php endif; ?>

                <form action="edit_results.php?event_id=<?php echo $event_id; ?>" method="post" class="space-y-8">
                    <?php if (empty($participants)): ?>
                        <p class="text-center text-gray-500 py-10">No participants registered for this event.</p>
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
                                Save Results & Email Winners
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </main>
    </div>
</body>
</html>