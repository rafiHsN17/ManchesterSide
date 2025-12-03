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
$stats['total_articles'] = $db->query("SELECT COUNT(*) as c FROM articles WHERE club_id = {$club['id']} AND is_published = 1")->fetch_assoc()['c'];

$current_user = getCurrentUser();

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
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 mb-8">
    <!-- Button Lihat Skuad Lengkap - RECOMMENDED -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
    <a href="club-full.php?team=<?php echo strtolower($team); ?>" 
       class="group flex items-center justify-center gap-2 px-6 py-3 
              bg-white hover:bg-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> 
              border-2 border-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> 
              text-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> 
              hover:text-white 
              font-bold rounded-lg shadow-md hover:shadow-xl transition-all 
              max-w-md mx-auto">
        <span class="text-xl">👥</span>
        <span>Lihat Skuad Lengkap</span>

        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
        </svg>
    </a>
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
            <div class="flex justify-center space-x-4 mb-4">
                <a href="index.php" class="text-gray-400 hover:text-city-blue transition">Beranda</a>
                <a href="news.php" class="text-gray-400 hover:text-city-blue transition">Berita</a>
                <a href="tentang-kami.php" class="text-gray-400 hover:text-city-blue transition">Tentang Kami</a>
            </div>
            <p class="text-gray-500 text-xs">&copy; 2025 Manchester Side. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Toggle main sections (club info)
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
    </script>

</body>
</html>