<?php
require_once '../config/db_connect.php';

// Security Check
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Fetch all meets for the first dropdown
$meets_result = $conn->query("SELECT meet_id, meet_name FROM meets ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Release Event Results</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <?php include '_sidebar.php'; ?>
        
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Release Event Results</h1>
            <p class="text-gray-600 mb-8">First, select a meet, then select the specific event to manage its results.</p>
            
            <div class="bg-white p-6 rounded-lg shadow-md max-w-lg mx-auto">
                <form action="edit_results.php" method="GET" class="space-y-6">
                    <!-- Step 1: Select Meet -->
                    <div>
                        <label for="meet_filter" class="block text-lg font-medium text-gray-700">Step 1: Select a Meet</label>
                        <select name="meet_filter" id="meet_filter" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm" required>
                            <option value="">-- Choose a Meet --</option>
                            <?php while($meet = $meets_result->fetch_assoc()): ?>
                                <option value="<?php echo $meet['meet_id']; ?>">
                                    <?php echo htmlspecialchars($meet['meet_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Step 2: Select Event (Populated by JavaScript) -->
                    <div>
                        <label for="event_id" class="block text-lg font-medium text-gray-700">Step 2: Select an Event</label>
                        <select name="event_id" id="event_id" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm" required disabled>
                            <option value="">-- First Select a Meet --</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" id="submit_button" class="w-full bg-green-600 text-white font-bold py-3 px-4 rounded-md text-lg opacity-50 cursor-not-allowed" disabled>
                            Manage Results
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const meetFilter = document.getElementById('meet_filter');
        const eventSelect = document.getElementById('event_id');
        const submitButton = document.getElementById('submit_button');

        meetFilter.addEventListener('change', function() {
            const meetId = this.value;

            // Reset and disable the event dropdown
            eventSelect.innerHTML = '<option value="">Loading...</option>';
            eventSelect.disabled = true;
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');

            if (!meetId) {
                eventSelect.innerHTML = '<option value="">-- First Select a Meet --</option>';
                return;
            }

            // Fetch the events for the selected meet from our new API
            fetch(`../api/get_events_by_meet.php?meet_id=${meetId}`)
                .then(response => response.json())
                .then(data => {
                    // Clear the loading message
                    eventSelect.innerHTML = '<option value="">-- Choose an Event --</option>';

                    if (data.length > 0) {
                        data.forEach(event => {
                            const option = document.createElement('option');
                            option.value = event.event_id;
                            option.textContent = event.event_name;
                            eventSelect.appendChild(option);
                        });
                        // Enable the dropdown
                        eventSelect.disabled = false;
                    } else {
                        eventSelect.innerHTML = '<option value="">-- No events found for this meet --</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    eventSelect.innerHTML = '<option value="">-- Error loading events --</option>';
                });
        });

        eventSelect.addEventListener('change', function() {
            // Enable the submit button only if a valid event is chosen
            if (this.value) {
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });
    </script>
</body>
</html>