<?php
require_once '../config/db_connect.php';

// Security Check: If coordinator is not "logged in", redirect to the login page.
if(!isset($_SESSION["coordinator_loggedin"]) || $_SESSION["coordinator_loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        #qr-reader {
            width: 100%;
            max-width: 500px;
            border: 5px solid #3b82f6; /* blue-500 */
            border-radius: 8px;
            overflow: hidden; /* Ensures the border-radius is respected by the video element */
        }
        #qr-reader-results {
            transition: all 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4 md:p-8">

    <div class="w-full max-w-2xl bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Live QR Scanner</h1>
                <p class="text-gray-600">Logged in as: <?php echo htmlspecialchars($_SESSION['coordinator_name']); ?></p>
            </div>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">Logout</a>
        </div>

        <!-- The container for the QR Code scanner video feed -->
        <div id="qr-reader" class="mx-auto"></div>
        
        <!-- Area to display the scan results -->
        <div id="qr-reader-results" class="mt-6 p-4 rounded-lg text-center font-semibold text-lg">
            Point the camera at a QR Code to begin scanning.
        </div>
    </div>

    <!-- Include the QR Code scanning library -->
    <script src="https://unpkg.com/html5-qrcode/html5-qrcode.min.js"></script>

    <script>
        // This function runs when a QR code is successfully scanned
        function onScanSuccess(decodedText, decodedResult) {
            console.log(`Scan result: ${decodedText}`);

            const resultDiv = document.getElementById('qr-reader-results');
            
            // Provide immediate feedback to the user
            resultDiv.textContent = "Processing...";
            resultDiv.className = 'mt-6 p-4 rounded-lg text-center font-semibold text-lg bg-yellow-100 text-yellow-800';
            
            // --- Send the scanned data to our backend API for verification ---
            fetch('../api/verify_qr.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ qr_data: decodedText })
            })
            .then(response => response.json())
            .then(data => {
                // Display the result from the API
                if (data.success) {
                    // Success! The QR code is valid.
                    resultDiv.textContent = `SUCCESS: ${data.message}`;
                    resultDiv.className = 'mt-6 p-4 rounded-lg text-center font-semibold text-lg bg-green-100 text-green-800';
                    // Play a success sound
                    new Audio('https://cdn.pixabay.com/audio/2022/03/15/audio_2c64e9a052.mp3').play();
                } else {
                    // Failure! The QR code is invalid or already used.
                    resultDiv.textContent = `FAILED: ${data.message}`;
                    resultDiv.className = 'mt-6 p-4 rounded-lg text-center font-semibold text-lg bg-red-100 text-red-800';
                    // Play an error sound
                    new Audio('https://cdn.pixabay.com/audio/2022/03/10/audio_c81c107383.mp3').play();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.textContent = "Verification Error: Could not connect to the server.";
                resultDiv.className = 'mt-6 p-4 rounded-lg text-center font-semibold text-lg bg-red-100 text-red-800';
            });
        }

        // This function runs if the scan fails (e.g., blurry image)
        function onScanFailure(error) {
            // This is useful for debugging but we don't need to show it to the user.
            // console.warn(`Code scan error = ${error}`);
        }

        // Create a new QR code scanner instance
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", // The ID of the div where the scanner will be placed
            { 
                fps: 10, // Frames per second to scan
                qrbox: { width: 250, height: 250 } // The size of the scanning box
            },
            /* verbose= */ false // Set to true for detailed logs
        );

        // Start the scanner
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    </script>
</body>
</html>