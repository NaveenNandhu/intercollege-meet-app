<?php
require_once '../config/db_connect.php';

// --- NEW UNIFIED SECURITY CHECK ---
// It now checks for the generic "user_loggedin" session.
if(!isset($_SESSION["user_loggedin"]) || $_SESSION["user_loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Hide deprecated warnings from the QR code library
error_reporting(E_ALL & ~E_DEPRECATED);

// Include the QR Code library.
$qrlib_path = '../lib/php-qrcode/qrlib.php';
if (!file_exists($qrlib_path)) {
    die("Error: QR Code library not found. Please follow installation instructions.");
}
require_once $qrlib_path;

// Prepare the directory for storing generated QR codes
$qr_temp_dir = 'qrcodes/';
if (!file_exists($qr_temp_dir)) {
    mkdir($qr_temp_dir, 0777, true);
}

// Use the new session variable for the user's ID
$user_id = $_SESSION['user_id'];

// --- Fetch all events the user is registered for ---
$sql = "SELECT r.qr_code_data, e.event_name, e.event_date, e.venue 
        FROM registrations r 
        JOIN events e ON r.event_id = e.event_id 
        WHERE r.student_id = ?
        ORDER BY e.event_date ASC";

$registrations = [];
if($stmt = $conn->prepare($sql)){
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $registrations[] = $row;
    }
    $stmt->close();
}

// --- Generate Lunch Token QR if applicable ---
$has_registrations = count($registrations) > 0;
$lunch_qr_file = '';
if ($has_registrations) {
    // Use the new session variable for the user's name
    $lunch_qr_data = "LUNCH_TOKEN;SID=" . $user_id . ";NAME=" . rawurlencode($_SESSION['user_name']);
    $lunch_qr_filename = 'lunch_sid_' . $user_id . '.png';
    $lunch_qr_file = $qr_temp_dir . $lunch_qr_filename;
    
    QRcode::png($lunch_qr_data, $lunch_qr_file, QR_ECLEVEL_L, 5);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center text-xl font-bold text-indigo-600">User Portal</div>
                <div class="flex items-center">
                    <a href="browse_events.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium">Browse Events</a>
                    <a href="logout.php" class="ml-4 bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Use the new session variable for the user's name -->
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <p class="text-gray-600 mb-8">This is your central hub. Here you can find all your QR codes for event entry and lunch.</p>
        
        <?php if($has_registrations): ?>
        <div class="bg-green-100 border-l-4 border-green-500 p-6 rounded-r-lg shadow-md mb-8">
            <h2 class="text-2xl font-bold mb-4 text-green-800">Your Universal Lunch Token</h2>
            <p class="mb-4 text-green-700">Show this QR code at the food counter to redeem your lunch. This token is valid only once.</p>
            <div class="flex justify-center md:justify-start">
                 <img src="<?php echo $lunch_qr_file; ?>?t=<?php echo time(); ?>" alt="Lunch Token QR Code" class="border-4 border-white rounded-lg shadow-lg">
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Your Registered Events</h2>
            <?php if(empty($registrations)): ?>
                <div class="text-center py-12">
                    <p class="text-gray-500">You haven't registered for any events yet.</p>
                    <a href="browse_events.php" class="mt-4 inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Browse & Register for Events</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($registrations as $reg): 
                        $qr_filename = 'event_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $reg['qr_code_data']) . '.png';
                        $qr_code_file_path = $qr_temp_dir . $qr_filename;
                        QRcode::png($reg['qr_code_data'], $qr_code_file_path, QR_ECLEVEL_L, 4);
                    ?>
                    <div class="border border-gray-200 p-4 rounded-lg flex flex-col items-center text-center shadow-sm">
                        <h3 class="font-bold text-lg text-gray-900"><?php echo htmlspecialchars($reg['event_name']); ?></h3>
                        <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($reg['venue']); ?></p>
                        <p class="text-sm text-gray-600"><?php echo date("D, M j, Y - g:i A", strtotime($reg['event_date'])); ?></p>
                        <img src="<?php echo $qr_code_file_path; ?>?t=<?php echo time(); ?>" alt="Event Entry QR Code" class="mt-4 border-2 border-gray-300 p-1 rounded-md">
                        <p class="text-xs text-center mt-2 font-semibold">Event Entry Pass</p>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>