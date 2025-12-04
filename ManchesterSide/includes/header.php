<?php
/**
 * Manchester Side - Header Component
 * Universal header untuk semua halaman
 */

// Get current user if logged in
if (!isset($current_user)) {
    $current_user = getCurrentUser();
}

// Get current page for active menu
$current_page = basename($_SERVER['PHP_SELF']);

// Club logos
$club_logos = [
    'CITY' => 'https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg',
    'UNITED' => 'https://upload.wikimedia.org/wikipedia/en/7/7a/Manchester_United_FC_crest.svg'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Manchester Side</title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Portal berita Manchester City dan Manchester United'; ?>">
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
        
        /* Navigation logo styles */
        .nav-logo {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        .nav-logo-container {
            display: flex;
            align-items: center;
            gap: 0;
        }

        .nav-logo-wrapper {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .nav-logo-wrapper:not(:first-child) {
            margin-left: -12px;
        }

        /* Dropdown menu */
        .dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                
                <!-- Logo -->
                <a href="index.php" class="flex items-center space-x-3">
                    <div class="nav-logo-container">
                        <div class="nav-logo-wrapper">
                            <img src="<?php echo $club_logos['CITY']; ?>" alt="Man City" class="nav-logo">
                        </div>
                        <div class="nav-logo-wrapper">
                            <img src="<?php echo $club_logos['UNITED']; ?>" alt="Man United" class="nav-logo">
                        </div>
                    </div>
                    <span class="text-2xl font-bold bg-gradient-to-r from-city-blue via-gray-800 to-united-red bg-clip-text text-transparent">
                        Manchester Side
                    </span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="index.php" class="text-gray-700 hover:text-city-blue font-semibold transition <?php echo $current_page === 'index.php' ? 'text-city-blue' : ''; ?>">
                        Beranda
                    </a>
                    <a href="news.php" class="text-gray-700 hover:text-city-blue font-semibold transition <?php echo $current_page === 'news.php' ? 'text-city-blue' : ''; ?>">
                        Berita
                    </a>
                    
                    <!-- Dropdown Klub -->
                    <div class="relative dropdown">
                        <button class="text-gray-700 hover:text-city-blue font-semibold transition flex items-center <?php echo in_array($current_page, ['club.php', 'profil-klub.php']) ? 'text-city-blue' : ''; ?>">
                            Klub
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="dropdown-menu absolute left-0 mt-2 w-56 bg-white shadow-xl rounded-lg py-2">
                            <a href="club.php?team=city" class="flex items-center gap-3 px-4 py-3 hover:bg-city-blue hover:text-white transition">
                                <img src="<?php echo $club_logos['CITY']; ?>" alt="Man City" class="w-6 h-6">
                                <span>Manchester City</span>
                            </a>
                            <a href="club.php?team=united" class="flex items-center gap-3 px-4 py-3 hover:bg-united-red hover:text-white transition">
                                <img src="<?php echo $club_logos['UNITED']; ?>" alt="Man United" class="w-6 h-6">
                                <span>Manchester United</span>
                            </a>
                            <div class="border-t border-gray-200 my-2"></div>
                            <a href="profil-klub.php?team=city" class="block px-4 py-2 text-sm hover:bg-gray-100 transition">
                                Profil Lengkap City
                            </a>
                            <a href="profil-klub.php?team=united" class="block px-4 py-2 text-sm hover:bg-gray-100 transition">
                                Profil Lengkap United
                            </a>
                        </div>
                    </div>

                    <a href="standings.php" class="text-gray-700 hover:text-city-blue font-semibold transition <?php echo $current_page === 'standings.php' ? 'text-city-blue' : ''; ?>">
                        Klasemen
                    </a>
                    <a href="schedule.php" class="text-gray-700 hover:text-city-blue font-semibold transition <?php echo $current_page === 'schedule.php' ? 'text-city-blue' : ''; ?>">
                        Jadwal
                    </a>
                    
                    <?php if ($current_user): ?>
                        <a href="favorites.php" class="text-gray-700 hover:text-city-blue font-semibold transition <?php echo $current_page === 'favorites.php' ? 'text-city-blue' : ''; ?>">
                            Favorit
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Auth Buttons -->
                <div class="hidden md:flex items-center space-x-3">
                    <?php if ($current_user): ?>
                        <a href="profile.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-gray-100 rounded-lg transition">
                            <div class="w-8 h-8 bg-gradient-to-r from-city-blue to-united-red rounded-full flex items-center justify-center text-white font-bold">
                                <?php echo strtoupper(substr($current_user['username'], 0, 1)); ?>
                            </div>
                            <span class="font-semibold text-gray-700"><?php echo $current_user['username']; ?></span>
                        </a>
                        <a href="logout.php" class="px-4 py-2 text-gray-700 hover:text-red-600 font-semibold transition">
                            Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="px-4 py-2 text-gray-700 hover:text-city-blue font-semibold transition">
                            Masuk
                        </a>
                        <a href="register.php" class="px-5 py-2 bg-gradient-to-r from-city-blue to-united-red text-white font-semibold rounded-lg hover:shadow-lg transition">
                            Daftar
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <button onclick="toggleMobileMenu()" class="md:hidden text-gray-700 p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-white border-t">
            <div class="px-4 py-3 space-y-2">
                <a href="index.php" class="block py-2 text-gray-700 hover:text-city-blue font-semibold">Beranda</a>
                <a href="news.php" class="block py-2 text-gray-700 hover:text-city-blue font-semibold">Berita</a>
                <a href="club.php?team=city" class="flex items-center gap-2 py-2 text-gray-700 hover:text-city-blue font-semibold">
                    <img src="<?php echo $club_logos['CITY']; ?>" alt="Man City" class="w-5 h-5">
                    Manchester City
                </a>
                <a href="club.php?team=united" class="flex items-center gap-2 py-2 text-gray-700 hover:text-united-red font-semibold">
                    <img src="<?php echo $club_logos['UNITED']; ?>" alt="Man United" class="w-5 h-5">
                    Manchester United
                </a>
                <a href="standings.php" class="block py-2 text-gray-700 hover:text-city-blue font-semibold">Klasemen</a>
                <a href="schedule.php" class="block py-2 text-gray-700 hover:text-city-blue font-semibold">Jadwal</a>
                
                <?php if ($current_user): ?>
                    <a href="favorites.php" class="block py-2 text-gray-700 hover:text-city-blue font-semibold">Favorit</a>
                    <a href="profile.php" class="block py-2 text-gray-700 hover:text-city-blue font-semibold">Profil</a>
                    <a href="logout.php" class="block py-2 text-red-600 font-semibold">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="block py-2 text-gray-700 hover:text-city-blue font-semibold">Masuk</a>
                    <a href="register.php" class="block py-2 text-gray-700 hover:text-city-blue font-semibold">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
    </script>