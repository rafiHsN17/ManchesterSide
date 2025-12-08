<?php
/**
 * Admin Sidebar Component - Konsisten untuk semua halaman
 * Usage: include '../includes/sidebar.php'; atau include 'includes/sidebar.php';
 */

// Determine current page for active menu
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Get admin info
if (!isset($admin)) {
    $admin = getCurrentAdmin();
}
?>

<!-- Sidebar -->
<aside class="w-64 bg-gray-900 text-white flex flex-col flex-shrink-0">
    <div class="p-6 border-b border-gray-800">
        <div class="flex items-center space-x-3">
            <div class="flex">
                <div class="w-8 h-8 bg-city-blue rounded-full"></div>
                <div class="w-8 h-8 bg-united-red rounded-full -ml-3"></div>
            </div>
            <div>
                <h1 class="text-xl font-bold">Admin Panel</h1>
                <p class="text-xs text-gray-400">Manchester Side</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 p-4 space-y-2">
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../' : ''; ?>dashboard.php" 
           class="flex items-center space-x-3 px-4 py-3 <?php echo $current_page === 'dashboard.php' ? 'bg-city-blue text-white font-semibold' : 'hover:bg-gray-800'; ?> rounded-lg transition">
            <span class="text-xl">📊</span>
            <span>Dashboard</span>
        </a>
        
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../' : ''; ?>article/index.php" 
           class="flex items-center space-x-3 px-4 py-3 <?php echo $current_dir === 'article' ? 'bg-city-blue text-white font-semibold' : 'hover:bg-gray-800'; ?> rounded-lg transition">
            <span class="text-xl">📰</span>
            <span>Berita</span>
        </a>
        
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../' : ''; ?>players/index.php" 
           class="flex items-center space-x-3 px-4 py-3 <?php echo $current_dir === 'players' ? 'bg-city-blue text-white font-semibold' : 'hover:bg-gray-800'; ?> rounded-lg transition">
            <span class="text-xl">👥</span>
            <span>Pemain</span>
        </a>
        
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../' : ''; ?>staff/index.php" 
           class="flex items-center space-x-3 px-4 py-3 <?php echo $current_dir === 'staff' ? 'bg-city-blue text-white font-semibold' : 'hover:bg-gray-800'; ?> rounded-lg transition">
            <span class="text-xl">🎯</span>
            <span>Staff Kepelatihan</span>
        </a>
        
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../' : ''; ?>profil-klub/index.php" 
           class="flex items-center space-x-3 px-4 py-3 <?php echo $current_dir === 'profil-klub' ? 'bg-city-blue text-white font-semibold' : 'hover:bg-gray-800'; ?> rounded-lg transition">
            <span class="text-xl">🏆</span>
            <span>Profil Klub</span>
        </a>
        
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../' : ''; ?>schedule/index.php" 
           class="flex items-center space-x-3 px-4 py-3 <?php echo $current_dir === 'schedule' ? 'bg-city-blue text-white font-semibold' : 'hover:bg-gray-800'; ?> rounded-lg transition">
            <span class="text-xl">📅</span>
            <span>Jadwal & Hasil</span>
        </a>
        
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../' : ''; ?>users/index.php" 
           class="flex items-center space-x-3 px-4 py-3 <?php echo $current_dir === 'users' ? 'bg-city-blue text-white font-semibold' : 'hover:bg-gray-800'; ?> rounded-lg transition">
            <span class="text-xl">👤</span>
            <span>Users</span>
        </a>
        
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../' : ''; ?>settings.php" 
           class="flex items-center space-x-3 px-4 py-3 <?php echo $current_page === 'settings.php' ? 'bg-city-blue text-white font-semibold' : 'hover:bg-gray-800'; ?> rounded-lg transition">
            <span class="text-xl">⚙️</span>
            <span>Settings</span>
        </a>
    </nav>

    <div class="p-4 border-t border-gray-800">
        <div class="flex items-center space-x-3 mb-3">
            <?php 
            $is_in_subdir = strpos($_SERVER['PHP_SELF'], '/admin/') !== false && substr_count($_SERVER['PHP_SELF'], '/') > 2;
            if (!empty($admin['photo_url']) && file_exists(($is_in_subdir ? '../../' : '../') . $admin['photo_url'])): 
            ?>
                <img src="<?php echo $is_in_subdir ? '../../' : '../'; ?><?php echo $admin['photo_url']; ?>" 
                     alt="<?php echo $admin['full_name'] ?? $admin['username']; ?>" 
                     class="w-10 h-10 rounded-full object-cover border-2 border-city-blue">
            <?php else: ?>
                <div class="w-10 h-10 bg-gradient-to-r from-city-blue to-united-red rounded-full flex items-center justify-center text-white font-bold">
                    <?php echo strtoupper(substr($admin['username'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            <div class="flex-1">
                <p class="font-semibold text-sm"><?php echo $admin['full_name'] ?? $admin['username']; ?></p>
                <p class="text-xs text-gray-400"><?php echo ucfirst($admin['role']); ?></p>
            </div>
        </div>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../../' : '../'; ?>index.php" target="_blank" 
           class="block w-full text-center px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg text-sm font-semibold transition mb-2">
            👁️ View Site
        </a>
        <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../' : ''; ?>logout.php" 
           class="block w-full text-center px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm font-semibold transition">
            🚪 Logout
        </a>
    </div>
</aside>
