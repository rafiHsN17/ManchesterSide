<?php
/**
 * Manchester Side - Admin Header Component
 */

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

// Get current admin
$current_admin = getCurrentAdmin();

// Get current page for active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin - Manchester Side</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'city-blue': '#6CABDD',
                        'city-navy': '#1C2C5B',
                        'united-red': '#DA291C',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Admin Navigation -->
    <nav class="bg-gray-900 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="flex items-center space-x-3">
                        <div class="flex">
                            <div class="w-8 h-8 bg-city-blue rounded-full"></div>
                            <div class="w-8 h-8 bg-united-red rounded-full -ml-3"></div>
                        </div>
                        <span class="text-xl font-bold">Admin Panel</span>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="dashboard.php" class="px-4 py-2 rounded-lg <?php echo $current_page === 'dashboard.php' ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?> transition">
                        📊 Dashboard
                    </a>
                    <a href="article/index.php" class="px-4 py-2 rounded-lg <?php echo strpos($_SERVER['PHP_SELF'], '/article/') !== false ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?> transition">
                        📰 Artikel
                    </a>
                    <a href="players/index.php" class="px-4 py-2 rounded-lg <?php echo strpos($_SERVER['PHP_SELF'], '/players/') !== false ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?> transition">
                        👥 Pemain
                    </a>
                    <a href="users/index.php" class="px-4 py-2 rounded-lg <?php echo strpos($_SERVER['PHP_SELF'], '/users/') !== false ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?> transition">
                        👤 Users
                    </a>
                    <a href="staff/index.php" class="px-4 py-2 rounded-lg <?php echo strpos($_SERVER['PHP_SELF'], '/staff/') !== false ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?> transition">
                        🔧 Staff
                    </a>
                    <a href="schedule/index.php" class="px-4 py-2 rounded-lg <?php echo strpos($_SERVER['PHP_SELF'], '/schedule/') !== false ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?> transition">
                        📅 Jadwal
                    </a>
                    <a href="settings.php" class="px-4 py-2 rounded-lg <?php echo $current_page === 'settings.php' ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?> transition">
                        ⚙️ Settings
                    </a>
                </div>

                <!-- Admin Info & Logout -->
                <div class="flex items-center space-x-4">
                    <!-- View Site -->
                    <a href="../index.php" target="_blank" class="hidden md:flex items-center space-x-2 px-4 py-2 bg-city-blue hover:bg-city-navy rounded-lg transition">
                        <span>🌐</span>
                        <span class="font-semibold">View Site</span>
                    </a>
                    
                    <!-- Admin Profile -->
                    <div class="flex items-center space-x-3 px-4 py-2 bg-gray-800 rounded-lg">
                        <div class="w-8 h-8 bg-gradient-to-r from-city-blue to-united-red rounded-full flex items-center justify-center text-white font-bold text-sm">
                            <?php echo strtoupper(substr($current_admin['username'], 0, 1)); ?>
                        </div>
                        <div class="hidden md:block">
                            <p class="text-sm font-semibold"><?php echo $current_admin['username']; ?></p>
                            <p class="text-xs text-gray-400"><?php echo ucfirst($current_admin['role']); ?></p>
                        </div>
                    </div>
                    
                    <!-- Logout -->
                    <a href="logout.php" class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg font-semibold transition">
                        Logout
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button onclick="toggleMobileMenu()" class="md:hidden text-white p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-gray-800 border-t border-gray-700">
            <div class="px-4 py-3 space-y-2">
                <a href="dashboard.php" class="block py-2 px-4 rounded <?php echo $current_page === 'dashboard.php' ? 'bg-gray-700' : 'hover:bg-gray-700'; ?>">📊 Dashboard</a>
                <a href="article/index.php" class="block py-2 px-4 rounded <?php echo strpos($_SERVER['PHP_SELF'], '/article/') !== false ? 'bg-gray-700' : 'hover:bg-gray-700'; ?>">📰 Artikel</a>
                <a href="players/index.php" class="block py-2 px-4 rounded <?php echo strpos($_SERVER['PHP_SELF'], '/players/') !== false ? 'bg-gray-700' : 'hover:bg-gray-700'; ?>">👥 Pemain</a>
                <a href="users/index.php" class="block py-2 px-4 rounded <?php echo strpos($_SERVER['PHP_SELF'], '/users/') !== false ? 'bg-gray-700' : 'hover:bg-gray-700'; ?>">👤 Users</a>
                <a href="staff/index.php" class="block py-2 px-4 rounded <?php echo strpos($_SERVER['PHP_SELF'], '/staff/') !== false ? 'bg-gray-700' : 'hover:bg-gray-700'; ?>">🔧 Staff</a>
                <a href="schedule/index.php" class="block py-2 px-4 rounded <?php echo strpos($_SERVER['PHP_SELF'], '/schedule/') !== false ? 'bg-gray-700' : 'hover:bg-gray-700'; ?>">📅 Jadwal</a>
                <a href="settings.php" class="block py-2 px-4 rounded <?php echo $current_page === 'settings.php' ? 'bg-gray-700' : 'hover:bg-gray-700'; ?>">⚙️ Settings</a>
                <a href="../index.php" target="_blank" class="block py-2 px-4 rounded hover:bg-gray-700">🌐 View Site</a>
            </div>
        </div>
    </nav>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
    </script>
