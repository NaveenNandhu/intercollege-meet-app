<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="w-64 bg-gray-800 text-white flex flex-col">
    <div class="p-6">
        <h2 class="text-2xl font-bold text-white">Admin Menu</h2>
        <span class="text-sm text-gray-400">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
    </div>
    <nav class="flex-1 px-4 space-y-2">
        <a href="dashboard.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 <?php echo ($current_page == 'dashboard.php') ? 'bg-gray-700' : ''; ?>">
            Dashboard
        </a>
        <a href="manage_meets.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 <?php echo ($current_page == 'manage_meets.php') ? 'bg-gray-700' : ''; ?>">
            Manage Meets
        </a>
        <a href="manage_events.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 <?php echo ($current_page == 'manage_events.php') ? 'bg-gray-700' : ''; ?>">
            Manage Events
        </a>
        <a href="assign_coordinators.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 <?php echo ($current_page == 'assign_coordinators.php') ? 'bg-gray-700' : ''; ?>">
            Assign Coordinators
        </a>
        <a href="participant_status.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 <?php echo ($current_page == 'participant_status.php') ? 'bg-gray-700' : ''; ?>">
            Participant Status
        </a>
        <a href="manage_users.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 <?php echo ($current_page == 'manage_users.php') ? 'bg-gray-700' : ''; ?>">
            Manage Users
        </a>
        <!-- New Link Here -->
        <a href="release_results.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 <?php echo ($current_page == 'release_results.php') ? 'bg-gray-700' : ''; ?>">
            Release Results
        </a>
    </nav>
    <div class="p-4">
        <a href="logout.php" class="block text-center py-2.5 px-4 rounded transition duration-200 bg-red-600 hover:bg-red-700">
            Logout
        </a>
    </div>
</div>