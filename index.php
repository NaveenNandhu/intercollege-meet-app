<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inter-College Meet Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="container mx-auto px-4 py-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-indigo-600">Inter-College Meet Hub</h1>
            <p class="text-lg text-gray-600 mt-4">The central place for all inter-collegiate events and competitions.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Admin Panel</h2>
                <p class="mb-6 text-gray-600">Faculty and HODs can manage meets and events.</p>
                <a href="admin/" class="inline-block bg-indigo-600 text-white font-semibold px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors duration-300">Admin Login</a>
            </div>
            <div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">User Portal</h2>
                <p class="mb-6 text-gray-600">Browse events, register, and access your QR codes.</p>
                <!-- CORRECTED LINK HERE -->
                <a href="users/login.php" class="inline-block bg-green-500 text-white font-semibold px-6 py-3 rounded-lg hover:bg-green-600 transition-colors duration-300">User Login / Register</a>
            </div>
        </div>
        
        <!-- New Section for Viewing Results -->
        <div class="text-center mt-12 max-w-4xl mx-auto">
            <div class="bg-white p-8 rounded-lg shadow-lg">
                 <h2 class="text-2xl font-bold text-gray-900 mb-4">View Event Results</h2>
                 <p class="mb-6 text-gray-600">See the winners and official standings for all completed events.</p>
                 <!-- CORRECTED LINK HERE -->
                 <a href="users/view_results.php" class="inline-block bg-yellow-500 text-white font-semibold px-6 py-3 rounded-lg hover:bg-yellow-600 transition-colors duration-300">View All Results</a>
            </div>
        </div>
    </div>
</body>
</html>
