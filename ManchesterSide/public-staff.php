<?php
/**
 * Manchester Side - Club Staff Page
 */
require_once 'includes/config.php';

$db = getDB();

// Get team parameter
$team = $_GET['team'] ?? 'city';
$team = strtoupper($team);

if (!in_array($team, ['CITY', 'UNITED'])) {
    redirect('index.php');
}

// Get club data
$stmt = $db->prepare("SELECT * FROM clubs WHERE code = ?");
$stmt->bind_param("s", $team);
$stmt->execute();
$club = $stmt->get_result()->fetch_assoc();

if (!$club) {
    redirect('index.php');
}

// Get staff
$staff_query = $db->prepare("SELECT * FROM staff WHERE club_id = ? AND is_active = 1 ORDER BY 
    CASE role 
        WHEN 'Manager' THEN 1
        WHEN 'Assistant Manager' THEN 2
        WHEN 'Fitness Coach' THEN 3
        WHEN 'Goalkeeping Coach' THEN 4
        ELSE 5
    END, role");
$staff_query->bind_param("i", $club['id']);
$staff_query->execute();
$staff_result = $staff_query->get_result();

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tim Pelatih <?php echo $club['name']; ?> - Manchester Side</title>
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
        
        .dropdown:hover .dropdown-menu {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="flex items-center space-x-3">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-city-blue rounded-full"></div>
                        <div class="w-8 h-8 bg-united-red rounded-full -ml-3"></div>
                    </div>
                    <span class="text-2xl font-bold bg-gradient-to-r from-city-blue via-gray-800 to-united-red bg-clip-text text-transparent">
                        Manchester Side
                    </span>
                </a>

                <div class="hidden md:flex items-center space-x-6">
                    <a href="index.php" class="text-gray-700 hover:text-city-blue font-semibold transition">Beranda</a>
                    <a href="news.php" class="text-gray-700 hover:text-city-blue font-semibold transition">Berita</a>
                    
                    <!-- Man City Dropdown -->
                    <div class="relative dropdown">
                        <a href="club.php?team=city" class="text-<?php echo $team === 'CITY' ? 'city-blue font-bold' : 'gray-700 hover:text-city-blue font-semibold'; ?> transition flex items-center">
                            Man City
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <div class="dropdown-menu hidden absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 border border-gray-200">
                            <a href="club.php?team=city" class="block px-4 py-2 text-gray-700 hover:bg-city-blue hover:text-white transition">
                                🏠 Profil Klub
                            </a>
                            <a href="club-players.php?team=city" class="block px-4 py-2 text-gray-700 hover:bg-city-blue hover:text-white transition">
                                👥 Skuad Pemain
                            </a>
                            <a href="club-staff.php?team=city" class="block px-4 py-2 text-gray-700 hover:bg-city-blue hover:text-white transition">
                                🎯 Tim Pelatih
                            </a>
                        </div>
                    </div>

                    <!-- Man United Dropdown -->
                    <div class="relative dropdown">
                        <a href="club.php?team=united" class="text-<?php echo $team === 'UNITED' ? 'united-red font-bold' : 'gray-700 hover:text-united-red font-semibold'; ?> transition flex items-center">
                            Man United
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <div class="dropdown-menu hidden absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 border border-gray-200">
                            <a href="club.php?team=united" class="block px-4 py-2 text-gray-700 hover:bg-united-red hover:text-white transition">
                                🏠 Profil Klub
                            </a>
                            <a href="club-players.php?team=united" class="block px-4 py-2 text-gray-700 hover:bg-united-red hover:text-white transition">
                                👥 Skuad Pemain
                            </a>
                            <a href="club-staff.php?team=united" class="block px-4 py-2 text-gray-700 hover:bg-united-red hover:text-white transition">
                                🎯 Tim Pelatih
                            </a>
                        </div>
                    </div>

                    <?php if ($current_user): ?>
                        <a href="profile.php" class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gradient-to-r from-city-blue to-united-red rounded-full flex items-center justify-center text-white font-bold">
                                <?php echo strtoupper(substr($current_user['username'], 0, 1)); ?>
                            </div>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="px-4 py-2 text-gray-700 hover:text-city-blue font-semibold transition">Masuk</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $team === 'CITY' ? 'city-navy' : 'red'; ?>-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="text-8xl mb-4">🎯</div>
                <h1 class="text-5xl font-black mb-4">
                    Tim Pelatih & Staff
                </h1>
                <p class="text-2xl mb-2"><?php echo $club['full_name']; ?></p>
                <p class="text-lg text-white/80">Musim 2024/2025</p>
            </div>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <?php if ($staff_result->num_rows > 0): ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while ($staff = $staff_result->fetch_assoc()): ?>
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition group">
                        <!-- Header with role -->
                        <div class="h-24 bg-gradient-to-br from-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $team === 'CITY' ? 'city-navy' : 'red'; ?>-900 flex items-center justify-center">
                            <h3 class="text-white font-bold text-lg text-center px-4">
                                <?php echo $staff['role']; ?>
                            </h3>
                        </div>

                        <div class="p-6">
                            <!-- Avatar -->
                            <div class="flex justify-center -mt-16 mb-4">
                                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center text-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> font-black text-4xl shadow-xl border-4 border-white group-hover:scale-110 transition">
                                    <?php echo strtoupper(substr($staff['name'], 0, 1)); ?>
                                </div>
                            </div>

                            <!-- Name -->
                            <h2 class="text-2xl font-bold text-gray-900 text-center mb-4 group-hover:text-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> transition">
                                <?php echo $staff['name']; ?>
                            </h2>

                            <!-- Info -->
                            <div class="space-y-3 mb-4">
                                <div class="flex items-center justify-center text-gray-600">
                                    <span class="text-xl mr-2">🌍</span>
                                    <span><?php echo $staff['nationality']; ?></span>
                                </div>
                                
                                <?php if ($staff['join_date']): ?>
                                    <div class="flex items-center justify-center text-gray-600 text-sm">
                                        <span class="mr-2">📅</span>
                                        <span>Bergabung sejak <?php echo formatDateIndo($staff['join_date']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Biography -->
                            <?php if ($staff['biography']): ?>
                                <div class="pt-4 border-t border-gray-200">
                                    <p class="text-sm text-gray-600 text-center leading-relaxed">
                                        <?php echo $staff['biography']; ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20">
                <div class="text-8xl mb-6">🎯</div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Belum Ada Data Staff
                </h2>
                <p class="text-gray-600 mb-8">
                    Informasi tim pelatih dan staff akan segera ditambahkan
                </p>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="text-center mt-12">
            <a href="club.php?team=<?php echo strtolower($team); ?>" class="inline-block px-8 py-4 bg-gradient-to-r from-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $team === 'CITY' ? 'city-navy' : 'red'; ?>-900 text-white font-bold rounded-lg hover:shadow-lg transition">
                ← Kembali ke Profil Klub
            </a>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex items-center justify-center space-x-2 mb-4">
                <div class="flex">
                    <div class="w-6 h-6 bg-city-blue rounded-full"></div>
                    <div class="w-6 h-6 bg-united-red rounded-full -ml-2"></div>
                </div>
                <span class="text-xl font-bold">Manchester Side</span>
            </div>
            <p class="text-gray-400 text-sm mb-4">Two Sides, One City, Endless Rivalry</p>
            <p class="text-gray-500 text-xs">&copy; 2025 Manchester Side. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>