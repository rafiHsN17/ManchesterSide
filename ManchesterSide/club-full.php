<?php
/**
 * Manchester Side - Club Full Squad & Staff Page
 * Gabungan dari players-public.php dan public-staff.php
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

// Get players grouped by position
$players_query = $db->prepare("SELECT * FROM players WHERE club_id = ? AND is_active = 1 ORDER BY position, jersey_number");
$players_query->bind_param("i", $club['id']);
$players_query->execute();
$players_result = $players_query->get_result();

// Group by position
$players_by_position = [
    'Goalkeeper' => [],
    'Defender' => [],
    'Midfielder' => [],
    'Forward' => []
];

while ($player = $players_result->fetch_assoc()) {
    $players_by_position[$player['position']][] = $player;
}

// Get staff
$staff_query = $db->prepare("SELECT * FROM staff WHERE club_id = ? AND is_active = 1 ORDER BY role");
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
    <title>Skuad Lengkap <?php echo $club['name']; ?> - Manchester Side</title>
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
                    <a href="club.php?team=city" class="text-<?php echo $team === 'CITY' ? 'city-blue font-bold' : 'gray-700 hover:text-city-blue font-semibold'; ?> transition">Man City</a>
                    <a href="club.php?team=united" class="text-<?php echo $team === 'UNITED' ? 'united-red font-bold' : 'gray-700 hover:text-united-red font-semibold'; ?> transition">Man United</a>
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
                <div class="text-8xl mb-4"><?php echo getClubEmoji($team); ?></div>
                <h1 class="text-5xl font-black mb-4">
                    Skuad Lengkap
                </h1>
                <p class="text-2xl mb-2"><?php echo $club['full_name']; ?></p>
                <p class="text-lg text-white/80">Musim 2024/2025</p>
            </div>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- SECTION: PLAYERS -->
        <div class="mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-8 text-center flex items-center justify-center">
                <span class="text-5xl mr-3">👥</span>
                Skuad Pemain
            </h2>

            <!-- Players by Position -->
            <?php foreach ($players_by_position as $position => $players): ?>
                <?php if (!empty($players)): ?>
                    <div class="mb-12">
                        <h3 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                            <span class="text-4xl mr-3">
                                <?php 
                                echo match($position) {
                                    'Goalkeeper' => '🧤',
                                    'Defender' => '🛡️',
                                    'Midfielder' => '⚙️',
                                    'Forward' => '⚽',
                                    default => '👤'
                                };
                                ?>
                            </span>
                            <?php 
                            echo match($position) {
                                'Goalkeeper' => 'Penjaga Gawang',
                                'Defender' => 'Bek',
                                'Midfielder' => 'Gelandang',
                                'Forward' => 'Penyerang',
                                default => $position
                            };
                            ?>
                            <span class="ml-3 text-lg text-gray-500">(<?php echo count($players); ?>)</span>
                        </h3>
                        
                        <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6">
                            <?php foreach ($players as $player): ?>
                                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition group">
                                    <div class="h-32 bg-gradient-to-br from-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $team === 'CITY' ? 'city-navy' : 'red'; ?>-900 flex items-center justify-center relative">
                                        <div class="text-7xl font-black text-white group-hover:scale-110 transition">
                                            <?php echo $player['jersey_number']; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="p-5">
                                        <h3 class="font-bold text-gray-900 text-lg mb-2 group-hover:text-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> transition">
                                            <?php echo $player['name']; ?>
                                        </h3>
                                        
                                        <div class="text-sm text-gray-600 space-y-2">
                                            <p class="flex items-center">
                                                <span class="mr-2">🌍</span>
                                                <?php echo $player['nationality']; ?>
                                            </p>
                                            <?php if ($player['height']): ?>
                                                <p class="flex items-center">
                                                    <span class="mr-2">📏</span>
                                                    <?php echo $player['height']; ?> cm
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($player['birth_date']): ?>
                                                <p class="flex items-center">
                                                    <span class="mr-2">🎂</span>
                                                    <?php echo date('Y') - date('Y', strtotime($player['birth_date'])); ?> tahun
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($player['joined_date']): ?>
                                                <p class="flex items-center text-xs text-gray-500">
                                                    <span class="mr-2">📅</span>
                                                    Bergabung <?php echo date('Y', strtotime($player['joined_date'])); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($player['biography']): ?>
                                            <div class="mt-4 pt-4 border-t border-gray-200">
                                                <p class="text-xs text-gray-600 line-clamp-3">
                                                    <?php echo $player['biography']; ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- SECTION: STAFF -->
        <div class="mb-12">
            <h2 class="text-4xl font-bold text-gray-900 mb-8 text-center flex items-center justify-center">
                <span class="text-5xl mr-3">🎯</span>
                Tim Pelatih & Staff
            </h2>

            <?php if ($staff_result->num_rows > 0): ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php 
                    $staff_result->data_seek(0);
                    while ($staff = $staff_result->fetch_assoc()): 
                    ?>
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
                    <p class="text-gray-600">
                        Informasi tim pelatih dan staff akan segera ditambahkan
                    </p>
                </div>
            <?php endif; ?>
        </div>

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