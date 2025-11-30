<?php
/**
 * Manchester Side - Club Profile Page (Simplified Version)
 */
require_once 'includes/config.php';

$db = getDB();

// Get team parameter (city or united)
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

// Get players
$players_query = $db->prepare("SELECT * FROM players WHERE club_id = ? AND is_active = 1 ORDER BY position, jersey_number");
$players_query->bind_param("i", $club['id']);
$players_query->execute();
$players_result = $players_query->get_result();

// Group players by position
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

// Get recent articles
$articles_query = $db->prepare("SELECT 
    a.id, a.title, a.slug, a.excerpt, a.published_at, a.views
FROM articles a
WHERE a.club_id = ? AND a.is_published = 1
ORDER BY a.published_at DESC
LIMIT 6");
$articles_query->bind_param("i", $club['id']);
$articles_query->execute();
$articles_result = $articles_query->get_result();

// Get statistics
$stats = [];
$stats['total_players'] = $db->query("SELECT COUNT(*) as c FROM players WHERE club_id = {$club['id']} AND is_active = 1")->fetch_assoc()['c'];
$stats['total_staff'] = $db->query("SELECT COUNT(*) as c FROM staff WHERE club_id = {$club['id']} AND is_active = 1")->fetch_assoc()['c'];
$stats['total_articles'] = $db->query("SELECT COUNT(*) as c FROM articles WHERE club_id = {$club['id']} AND is_published = 1")->fetch_assoc()['c'];

$current_user = getCurrentUser();

// Player default images based on position
$position_images = [
    'Goalkeeper' => 'https://ui-avatars.com/api/?name=GK&size=400&background=0D8ABC&color=fff&bold=true',
    'Defender' => 'https://ui-avatars.com/api/?name=DF&size=400&background=059669&color=fff&bold=true',
    'Midfielder' => 'https://ui-avatars.com/api/?name=MF&size=400&background=7C3AED&color=fff&bold=true',
    'Forward' => 'https://ui-avatars.com/api/?name=FW&size=400&background=DC2626&color=fff&bold=true'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $club['name']; ?> - Manchester Side</title>
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
        
        .hero-gradient-city {
            background: linear-gradient(135deg, #6CABDD 0%, #1C2C5B 100%);
        }
        
        .hero-gradient-united {
            background: linear-gradient(135deg, #DA291C 0%, #8B0000 100%);
        }
        
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

        /* Dropdown Styles */
        .dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        
        .dropdown-content.active {
            max-height: 500px;
        }
        
        .dropdown-arrow {
            transition: transform 0.3s ease;
        }
        
        .dropdown-arrow.rotated {
            transform: rotate(180deg);
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
    <section class="hero-gradient-<?php echo strtolower($team); ?> text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="text-9xl mb-6"><?php echo getClubEmoji($team); ?></div>
            <h1 class="text-5xl md:text-6xl font-black mb-4">
                <?php echo $club['full_name']; ?>
            </h1>
            <p class="text-2xl mb-6 text-white/90">
                Founded <?php echo $club['founded_year']; ?>
            </p>
            <div class="flex justify-center gap-6 text-lg">
                <div>
                    <span class="font-bold"><?php echo $stats['total_players']; ?></span>
                    <span class="text-white/80 ml-2">Pemain</span>
                </div>
                <div class="text-white/50">•</div>
                <div>
                    <span class="font-bold"><?php echo $stats['total_staff']; ?></span>
                    <span class="text-white/80 ml-2">Staff</span>
                </div>
                <div class="text-white/50">•</div>
                <div>
                    <span class="font-bold"><?php echo $stats['total_articles']; ?></span>
                    <span class="text-white/80 ml-2">Berita</span>
                </div>
            </div>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Quick Info Dropdown -->
        <div class="mb-12">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Dropdown Header (Always Visible) -->
                <button 
                    onclick="toggleDropdown()"
                    class="w-full px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition"
                >
                    <div class="flex items-center space-x-3">
                        <span class="text-3xl">ℹ️</span>
                        <h3 class="text-xl font-bold text-gray-900">Informasi Klub</h3>
                    </div>
                    <svg class="dropdown-arrow w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Content (Collapsible) -->
                <div id="clubInfoDropdown" class="dropdown-content">
                    <div class="px-6 pb-6 border-t border-gray-200">
                        <div class="grid md:grid-cols-3 gap-6 mt-6">
                            
                            <!-- Stadium Info -->
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center mb-3">
                                    <span class="text-3xl mr-3">🏟️</span>
                                    <h4 class="font-bold text-gray-900">Stadion</h4>
                                </div>
                                <p class="font-bold text-lg text-gray-900 mb-1"><?php echo $club['stadium_name']; ?></p>
                                <p class="text-sm text-gray-600 mb-2"><?php echo $club['stadium_location']; ?></p>
                                <div class="text-sm">
                                    <span class="text-gray-600">Kapasitas:</span>
                                    <span class="font-bold text-gray-900 ml-2"><?php echo number_format($club['stadium_capacity']); ?></span>
                                </div>
                            </div>

                            <!-- Founded -->
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center mb-3">
                                    <span class="text-3xl mr-3">📅</span>
                                    <h4 class="font-bold text-gray-900">Didirikan</h4>
                                </div>
                                <p class="font-bold text-4xl text-gray-900 mb-2"><?php echo $club['founded_year']; ?></p>
                                <p class="text-sm text-gray-600"><?php echo date('Y') - $club['founded_year']; ?> tahun yang lalu</p>
                            </div>

                            <!-- Statistics -->
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center mb-3">
                                    <span class="text-3xl mr-3">📊</span>
                                    <h4 class="font-bold text-gray-900">Statistik</h4>
                                </div>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Pemain Aktif:</span>
                                        <span class="font-bold text-gray-900"><?php echo $stats['total_players']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Total Staff:</span>
                                        <span class="font-bold text-gray-900"><?php echo $stats['total_staff']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Berita:</span>
                                        <span class="font-bold text-gray-900"><?php echo $stats['total_articles']; ?></span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Section -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                <span class="text-4xl mr-3">📖</span>
                Sejarah Klub
            </h2>
            <div class="prose prose-lg max-w-none text-gray-700">
                <?php echo nl2br($club['history']); ?>
            </div>
        </div>

        <!-- Achievements Section -->
        <div class="bg-gradient-to-br from-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $team === 'CITY' ? 'city-navy' : 'red'; ?>-900 rounded-2xl shadow-xl p-8 text-white mb-12">
            <h2 class="text-3xl font-bold mb-6 flex items-center">
                <span class="text-4xl mr-3">🏆</span>
                Prestasi & Trofi
            </h2>
            <div class="prose prose-lg max-w-none text-white/90">
                <?php echo nl2br($club['achievements']); ?>
            </div>
        </div>

        <!-- Players Section with Dropdown -->
        <div class="mb-12">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Dropdown Header -->
                <button 
                    onclick="toggleSection('players')"
                    class="w-full px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition"
                >
                    <div class="flex items-center space-x-3">
                        <span class="text-3xl">👥</span>
                        <h2 class="text-2xl font-bold text-gray-900">Skuad Pemain</h2>
                        <span class="ml-3 px-3 py-1 bg-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> text-white rounded-full text-sm font-bold">
                            <?php echo $stats['total_players']; ?> Pemain
                        </span>
                    </div>
                    <svg id="players-arrow" class="dropdown-arrow w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Content -->
                <div id="playersDropdown" class="dropdown-content">
                    <div class="px-6 pb-6 border-t border-gray-200">
                        <?php foreach ($players_by_position as $position => $players): ?>
                            <?php if (!empty($players)): ?>
                                <div class="mt-6">
                                    <!-- Position Dropdown -->
                                    <button 
                                        onclick="togglePosition('<?php echo strtolower($position); ?>')"
                                        class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition mb-4"
                                    >
                                        <div class="flex items-center space-x-3">
                                            <span class="text-2xl">
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
                                            <h3 class="text-lg font-bold text-gray-900">
                                                <?php 
                                                echo match($position) {
                                                    'Goalkeeper' => 'Penjaga Gawang',
                                                    'Defender' => 'Bek',
                                                    'Midfielder' => 'Gelandang',
                                                    'Forward' => 'Penyerang',
                                                    default => $position
                                                };
                                                ?>
                                            </h3>
                                            <span class="px-2 py-1 bg-white rounded-full text-xs font-bold text-gray-600">
                                                <?php echo count($players); ?>
                                            </span>
                                        </div>
                                        <svg id="<?php echo strtolower($position); ?>-arrow" class="dropdown-arrow w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    
                                    <!-- Position Players -->
                                    <div id="<?php echo strtolower($position); ?>Dropdown" class="dropdown-content">
                                        <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6 mb-6">
                                            <?php foreach ($players as $player): ?>
                                                <div class="player-card bg-white rounded-xl shadow-lg overflow-hidden">
                                                    <!-- Player Photo with Frame -->
                                                    <div class="player-photo-frame relative">
                                                        <img 
                                                            src="<?php echo $player['photo_url'] ?? $position_images[$position]; ?>" 
                                                            alt="<?php echo $player['name']; ?>"
                                                            class="w-full h-64 object-cover"
                                                            onerror="this.src='<?php echo $position_images[$position]; ?>'"
                                                        >
                                                        <!-- Jersey Number Badge -->
                                                        <div class="jersey-number">
                                                            <?php echo $player['jersey_number']; ?>
                                                        </div>
                                                        <!-- Position Badge -->
                                                        <div class="position-badge">
                                                            <?php 
                                                            echo match($position) {
                                                                'Goalkeeper' => '🧤 GK',
                                                                'Defender' => '🛡️ DF',
                                                                'Midfielder' => '⚙️ MF',
                                                                'Forward' => '⚽ FW',
                                                                default => $position
                                                            };
                                                            ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Player Info -->
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
                                                            <?php if ($player['joined_date']): ?>
                                                                <div class="flex items-center">
                                                                    <span class="w-6">📅</span>
                                                                    <span>Sejak <?php echo date('Y', strtotime($player['joined_date'])); ?></span>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Section with Dropdown -->
        <?php if ($staff_result->num_rows > 0): ?>
        <div class="mb-12">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Dropdown Header -->
                <button 
                    onclick="toggleSection('staff')"
                    class="w-full px-6 py-5 flex items-center justify-between hover:bg-gray-50 transition"
                >
                    <div class="flex items-center space-x-3">
                        <span class="text-3xl">🎯</span>
                        <h2 class="text-2xl font-bold text-gray-900">Tim Pelatih & Staff</h2>
                        <span class="ml-3 px-3 py-1 bg-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> text-white rounded-full text-sm font-bold">
                            <?php echo $stats['total_staff']; ?> Staff
                        </span>
                    </div>
                    <svg id="staff-arrow" class="dropdown-arrow w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Content -->
                <div id="staffDropdown" class="dropdown-content">
                    <div class="px-6 pb-6 border-t border-gray-200">
                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                            <?php 
                            $staff_result->data_seek(0);
                            while ($staff = $staff_result->fetch_assoc()): 
                            ?>
                                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                                    <div class="flex items-center mb-4">
                                        <div class="w-14 h-14 bg-gradient-to-br from-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $team === 'CITY' ? 'city-navy' : 'red'; ?>-900 rounded-full flex items-center justify-center text-white font-bold text-2xl">
                                            <?php echo strtoupper(substr($staff['name'], 0, 1)); ?>
                                        </div>
                                        <div class="ml-4">
                                            <h4 class="font-bold text-gray-900"><?php echo $staff['name']; ?></h4>
                                            <p class="text-sm text-gray-600"><?php echo $staff['role']; ?></p>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-700">
                                        <p class="mb-2">🌍 <?php echo $staff['nationality']; ?></p>
                                        <?php if ($staff['join_date']): ?>
                                            <p class="text-xs text-gray-500">Bergabung: <?php echo formatDateIndo($staff['join_date']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent News -->
        <?php if ($articles_result->num_rows > 0): ?>
        <div class="mb-12">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-3xl font-bold text-gray-900 flex items-center">
                    <span class="text-4xl mr-3">📰</span>
                    Berita Terbaru
                </h2>
                <a href="news.php?club=<?php echo strtolower($team); ?>" class="text-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> hover:underline font-bold">
                    Lihat Semua →
                </a>
            </div>
            
            <div class="grid md:grid-cols-3 gap-6">
                <?php while ($article = $articles_result->fetch_assoc()): ?>
                    <a href="news-detail.php?slug=<?php echo $article['slug']; ?>" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition group">
                        <div class="h-40 bg-gradient-to-br from-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $team === 'CITY' ? 'city-navy' : 'red'; ?>-900 flex items-center justify-center text-white text-4xl">
                            <?php echo getClubEmoji($team); ?>
                        </div>
                        <div class="p-5">
                            <h4 class="font-bold text-gray-900 mb-2 group-hover:text-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> transition line-clamp-2">
                                <?php echo $article['title']; ?>
                            </h4>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                <?php echo truncateText($article['excerpt'], 80); ?>
                            </p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>📅 <?php echo formatDateIndo($article['published_at']); ?></span>
                                <span>👁️ <?php echo formatNumber($article['views']); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Switch Club Button -->
        <div class="text-center py-12 bg-white rounded-2xl shadow-xl">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Ingin lihat klub lainnya?</h3>
            <a href="club.php?team=<?php echo $team === 'CITY' ? 'united' : 'city'; ?>" class="inline-block px-8 py-4 bg-gradient-to-r from-<?php echo $team === 'CITY' ? 'united-red' : 'city-blue'; ?> to-<?php echo $team === 'CITY' ? 'red' : 'city-navy'; ?>-900 text-white font-bold rounded-lg hover:shadow-lg transition text-lg">
                <?php echo $team === 'CITY' ? '🔴 Manchester United' : '🔵 Manchester City'; ?>
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

    <script>
        // Toggle main sections (club info, players, staff)
        function toggleSection(sectionName) {
            const dropdown = document.getElementById(sectionName + 'Dropdown');
            const arrow = document.getElementById(sectionName + '-arrow');
            
            dropdown.classList.toggle('active');
            arrow.classList.toggle('rotated');
        }

        // Toggle for club info (legacy support)
        function toggleDropdown() {
            toggleSection('clubInfo');
        }

        // Toggle position dropdowns inside players section
        function togglePosition(positionName) {
            const dropdown = document.getElementById(positionName + 'Dropdown');
            const arrow = document.getElementById(positionName + '-arrow');
            
            dropdown.classList.toggle('active');
            arrow.classList.toggle('rotated');
        }
    </script>

</body>
</html>