<?php
/**
 * Manchester Side - Public Players Directory
 */
require_once 'includes/config.php';

$db = getDB();

// Get filter parameter
$club_filter = $_GET['club'] ?? 'all';
$position_filter = $_GET['position'] ?? 'all';

// Build query
$query = "SELECT p.*, c.name as club_name, c.code as club_code, c.color_primary
FROM players p
JOIN clubs c ON p.club_id = c.id
WHERE p.is_active = 1";

if ($club_filter === 'city') {
    $query .= " AND c.code = 'CITY'";
} elseif ($club_filter === 'united') {
    $query .= " AND c.code = 'UNITED'";
}

if ($position_filter !== 'all') {
    $query .= " AND p.position = '$position_filter'";
}

$query .= " ORDER BY c.id, p.position, p.jersey_number";

$players_result = $db->query($query);

// Group by club and position
$players_by_club = ['CITY' => [], 'UNITED' => []];
$players_result->data_seek(0);
while ($player = $players_result->fetch_assoc()) {
    if (!isset($players_by_club[$player['club_code']][$player['position']])) {
        $players_by_club[$player['club_code']][$player['position']] = [];
    }
    $players_by_club[$player['club_code']][$player['position']][] = $player;
}

$current_user = getCurrentUser();

// Player default images based on position
$position_images = [
    'Goalkeeper' => '🧤',
    'Defender' => '🛡️',
    'Midfielder' => '⚙️',
    'Forward' => '⚽'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Players Squad - Manchester Side</title>
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
        
        .player-card {
            transition: all 0.3s ease;
        }
        
        .player-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .player-photo-frame {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        }
        
        .player-photo-frame::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #6CABDD, #DA291C, #6CABDD);
            border-radius: 1rem;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .player-card:hover .player-photo-frame::before {
            opacity: 1;
            animation: gradient-rotate 3s linear infinite;
        }
        
        @keyframes gradient-rotate {
            0% { filter: hue-rotate(0deg); }
            100% { filter: hue-rotate(360deg); }
        }
        
        .jersey-number {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            width: 3rem;
            height: 3rem;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 900;
            color: white;
            border: 3px solid rgba(255,255,255,0.3);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        
        .position-badge {
            position: absolute;
            bottom: 0.5rem;
            left: 0.5rem;
            padding: 0.25rem 0.75rem;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(10px);
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
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
                    <a href="players-public.php" class="text-city-blue font-bold">Pemain</a>
                    <a href="staff-public.php" class="text-gray-700 hover:text-city-blue font-semibold transition">Staff</a>
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
    <section class="bg-gradient-to-r from-city-blue via-gray-800 to-united-red text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl md:text-6xl font-black mb-4">
                ⚽ Squad Pemain Manchester
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-white/90">
                Skuad Lengkap Manchester City & Manchester United
            </p>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Filter Buttons -->
        <div class="mb-8">
            <div class="flex flex-wrap justify-center gap-4 mb-6">
                <a href="?club=all&position=<?php echo $position_filter; ?>" class="px-8 py-3 <?php echo $club_filter === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-bold shadow-lg transition">
                    Semua Klub
                </a>
                <a href="?club=city&position=<?php echo $position_filter; ?>" class="px-8 py-3 <?php echo $club_filter === 'city' ? 'bg-city-blue text-white' : 'bg-white text-gray-700 hover:bg-city-blue hover:text-white'; ?> rounded-lg font-bold shadow-lg transition">
                    🔵 Man City
                </a>
                <a href="?club=united&position=<?php echo $position_filter; ?>" class="px-8 py-3 <?php echo $club_filter === 'united' ? 'bg-united-red text-white' : 'bg-white text-gray-700 hover:bg-united-red hover:text-white'; ?> rounded-lg font-bold shadow-lg transition">
                    🔴 Man United
                </a>
            </div>

            <div class="flex flex-wrap justify-center gap-3">
                <a href="?club=<?php echo $club_filter; ?>&position=all" class="px-4 py-2 <?php echo $position_filter === 'all' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-purple-100'; ?> rounded-lg font-semibold text-sm shadow transition">
                    Semua Posisi
                </a>
                <a href="?club=<?php echo $club_filter; ?>&position=Goalkeeper" class="px-4 py-2 <?php echo $position_filter === 'Goalkeeper' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-purple-100'; ?> rounded-lg font-semibold text-sm shadow transition">
                    🧤 Goalkeeper
                </a>
                <a href="?club=<?php echo $club_filter; ?>&position=Defender" class="px-4 py-2 <?php echo $position_filter === 'Defender' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-purple-100'; ?> rounded-lg font-semibold text-sm shadow transition">
                    🛡️ Defender
                </a>
                <a href="?club=<?php echo $club_filter; ?>&position=Midfielder" class="px-4 py-2 <?php echo $position_filter === 'Midfielder' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-purple-100'; ?> rounded-lg font-semibold text-sm shadow transition">
                    ⚙️ Midfielder
                </a>
                <a href="?club=<?php echo $club_filter; ?>&position=Forward" class="px-4 py-2 <?php echo $position_filter === 'Forward' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-purple-100'; ?> rounded-lg font-semibold text-sm shadow transition">
                    ⚽ Forward
                </a>
            </div>
        </div>

        <!-- Manchester City Players -->
        <?php if ($club_filter === 'all' || $club_filter === 'city'): ?>
            <?php if (!empty($players_by_club['CITY'])): ?>
                <section class="mb-16">
                    <div class="bg-gradient-to-r from-city-blue to-city-navy text-white rounded-2xl shadow-xl p-8 mb-8">
                        <div class="flex items-center justify-center space-x-4">
                            <span class="text-6xl">🔵</span>
                            <h2 class="text-4xl font-black">Manchester City Squad</h2>
                        </div>
                    </div>

                    <?php foreach (['Goalkeeper', 'Defender', 'Midfielder', 'Forward'] as $position): ?>
                        <?php if (!empty($players_by_club['CITY'][$position])): ?>
                            <div class="mb-10">
                                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                                    <span class="text-3xl mr-3"><?php echo $position_images[$position]; ?></span>
                                    <?php 
                                    echo match($position) {
                                        'Goalkeeper' => 'Penjaga Gawang',
                                        'Defender' => 'Bek',
                                        'Midfielder' => 'Gelandang',
                                        'Forward' => 'Penyerang'
                                    };
                                    ?>
                                </h3>
                                
                                <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6">
                                    <?php foreach ($players_by_club['CITY'][$position] as $player): ?>
                                        <div class="player-card bg-white rounded-xl shadow-lg overflow-hidden">
                                            <div class="player-photo-frame relative">
                                                <div class="h-64 bg-gradient-to-br from-city-blue to-city-navy flex items-center justify-center text-white text-6xl">
                                                    <?php echo $position_images[$position]; ?>
                                                </div>
                                                <div class="jersey-number"><?php echo $player['jersey_number']; ?></div>
                                                <div class="position-badge"><?php echo $position_images[$position]; ?> <?php echo strtoupper(substr($position, 0, 2)); ?></div>
                                            </div>
                                            
                                            <div class="p-5">
                                                <h4 class="font-bold text-gray-900 text-lg mb-2"><?php echo $player['name']; ?></h4>
                                                <div class="space-y-2 text-sm text-gray-600">
                                                    <div class="flex items-center">
                                                        <span class="w-6">🌍</span>
                                                        <span><?php echo $player['nationality']; ?></span>
                                                    </div>
                                                    <?php if ($player['height']): ?>
                                                        <div class="flex items-center">
                                                            <span class="w-6">📏</span>
                                                            <span><?php echo $player['height']; ?> cm</span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($player['birth_date']): ?>
                                                        <div class="flex items-center">
                                                            <span class="w-6">🎂</span>
                                                            <span><?php echo date('Y') - date('Y', strtotime($player['birth_date'])); ?> tahun</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Manchester United Players -->
        <?php if ($club_filter === 'all' || $club_filter === 'united'): ?>
            <?php if (!empty($players_by_club['UNITED'])): ?>
                <section class="mb-16">
                    <div class="bg-gradient-to-r from-united-red to-red-900 text-white rounded-2xl shadow-xl p-8 mb-8">
                        <div class="flex items-center justify-center space-x-4">
                            <span class="text-6xl">🔴</span>
                            <h2 class="text-4xl font-black">Manchester United Squad</h2>
                        </div>
                    </div>

                    <?php foreach (['Goalkeeper', 'Defender', 'Midfielder', 'Forward'] as $position): ?>
                        <?php if (!empty($players_by_club['UNITED'][$position])): ?>
                            <div class="mb-10">
                                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                                    <span class="text-3xl mr-3"><?php echo $position_images[$position]; ?></span>
                                    <?php 
                                    echo match($position) {
                                        'Goalkeeper' => 'Penjaga Gawang',
                                        'Defender' => 'Bek',
                                        'Midfielder' => 'Gelandang',
                                        'Forward' => 'Penyerang'
                                    };
                                    ?>
                                </h3>
                                
                                <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6">
                                    <?php foreach ($players_by_club['UNITED'][$position] as $player): ?>
                                        <div class="player-card bg-white rounded-xl shadow-lg overflow-hidden">
                                            <div class="player-photo-frame relative">
                                                <div class="h-64 bg-gradient-to-br from-united-red to-red-900 flex items-center justify-center text-white text-6xl">
                                                    <?php echo $position_images[$position]; ?>
                                                </div>
                                                <div class="jersey-number"><?php echo $player['jersey_number']; ?></div>
                                                <div class="position-badge"><?php echo $position_images[$position]; ?> <?php echo strtoupper(substr($position, 0, 2)); ?></div>
                                            </div>
                                            
                                            <div class="p-5">
                                                <h4 class="font-bold text-gray-900 text-lg mb-2"><?php echo $player['name']; ?></h4>
                                                <div class="space-y-2 text-sm text-gray-600">
                                                    <div class="flex items-center">
                                                        <span class="w-6">🌍</span>
                                                        <span><?php echo $player['nationality']; ?></span>
                                                    </div>
                                                    <?php if ($player['height']): ?>
                                                        <div class="flex items-center">
                                                            <span class="w-6">📏</span>
                                                            <span><?php echo $player['height']; ?> cm</span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($player['birth_date']): ?>
                                                        <div class="flex items-center">
                                                            <span class="w-6">🎂</span>
                                                            <span><?php echo date('Y') - date('Y', strtotime($player['birth_date'])); ?> tahun</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        <?php endif; ?>

        <!-- CTA Section -->
        <div class="mt-16 bg-gradient-to-r from-purple-600 to-purple-900 text-white rounded-2xl shadow-xl p-8 text-center">
            <h3 class="text-3xl font-bold mb-4">⚽ Complete Squad Information</h3>
            <p class="text-lg mb-6">
                Lihat profil lengkap pemain dan staff dari kedua klub Manchester
            </p>
            <div class="flex justify-center gap-6">
                <a href="staff-public.php" class="px-8 py-3 bg-white text-purple-900 font-bold rounded-lg hover:bg-gray-100 transition">
                    👔 Lihat Staff
                </a>
                <a href="index.php" class="px-8 py-3 bg-white/20 backdrop-blur text-white font-bold rounded-lg hover:bg-white/30 transition">
                    📰 Berita Terbaru
                </a>
            </div>
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