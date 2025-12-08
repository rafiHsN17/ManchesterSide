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
</style>

<!-- Hero Section -->
    <section class="hero-gradient-<?php echo strtolower($team); ?> text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="flex justify-center mb-6">
                <img src="<?php echo $team === 'CITY' ? 'https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg' : 'https://upload.wikimedia.org/wikipedia/en/7/7a/Manchester_United_FC_crest.svg'; ?>" 
                     alt="<?php echo $club['full_name']; ?>" 
                     class="w-40 h-40 object-contain filter drop-shadow-2xl">
            </div>
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
            <a href="club.php?team=<?php echo $team === 'CITY' ? 'united' : 'city'; ?>" class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-<?php echo $team === 'CITY' ? 'united-red' : 'city-blue'; ?> to-<?php echo $team === 'CITY' ? 'red' : 'city-navy'; ?>-900 text-white font-bold rounded-lg hover:shadow-lg transition text-lg">
                <img src="<?php echo $team === 'CITY' ? 'https://upload.wikimedia.org/wikipedia/en/7/7a/Manchester_United_FC_crest.svg' : 'https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg'; ?>" 
                     alt="<?php echo $team === 'CITY' ? 'Manchester United' : 'Manchester City'; ?>" 
                     class="w-6 h-6 object-contain">
                <?php echo $team === 'CITY' ? 'Manchester United' : 'Manchester City'; ?>
            </a>
        </div>

    </main>


<?php include 'includes/footer.php'; ?>
