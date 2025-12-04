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

$page_title = $club['name'];
include 'includes/header.php';
?>

<style>
    .hero-gradient-city {
        background: linear-gradient(135deg, #6CABDD 0%, #1C2C5B 100%);
    }
    
    .hero-gradient-united {
        background: linear-gradient(135deg, #DA291C 0%, #8B0000 100%);
    }
    
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
            <div class="flex justify-center gap-6 text-lg mb-6">
                <a href="profil-klub.php?team=<?php echo strtolower($team); ?>" 
                   class="group flex items-center justify-center gap-2 px-6 py-3 
                          bg-white hover:bg-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> 
                          border-2 border-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> 
                          text-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> 
                          hover:text-white 
                          font-bold rounded-lg shadow-md hover:shadow-xl transition-all">
                    <span class="text-xl">👥</span>
                    <span>Lihat Profil Lengkap</span>
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
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

<?php include 'includes/footer.php'; ?>
