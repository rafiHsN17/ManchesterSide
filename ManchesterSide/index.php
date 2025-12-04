<?php
/**
 * Manchester Side - Homepage with Real Club Logos
 */
require_once 'includes/config.php';

$db = getDB();

// Get filter parameter
$filter = $_GET['filter'] ?? 'all'; // all, city, united

// Build query based on filter
$query = "SELECT 
    a.id, a.title, a.slug, a.excerpt, a.image_url, a.category, a.views, a.published_at,
    c.name as club_name, c.code as club_code, c.color_primary,
    ad.full_name as author_name
FROM articles a
LEFT JOIN clubs c ON a.club_id = c.id
JOIN admins ad ON a.author_id = ad.id
WHERE a.is_published = 1";

if ($filter === 'city') {
    $query .= " AND c.code = 'CITY'";
} elseif ($filter === 'united') {
    $query .= " AND c.code = 'UNITED'";
}

$query .= " ORDER BY a.published_at DESC LIMIT 20";

$articles_result = $db->query($query);

// Get featured articles (top 2)
$featured_query = "SELECT 
    a.id, a.title, a.slug, a.excerpt, a.image_url, a.published_at,
    c.name as club_name, c.code as club_code, c.color_primary
FROM articles a
LEFT JOIN clubs c ON a.club_id = c.id
WHERE a.is_published = 1 AND a.is_featured = 1
ORDER BY a.published_at DESC LIMIT 2";

$featured_result = $db->query($featured_query);
$featured_articles = [];
while ($row = $featured_result->fetch_assoc()) {
    $featured_articles[] = $row;
}

// Get upcoming matches
$matches_query = "SELECT 
    m.*,
    h.name as home_team, h.code as home_code,
    a.name as away_team, a.code as away_code
FROM matches m
JOIN clubs h ON m.home_team_id = h.id
JOIN clubs a ON m.away_team_id = a.id
WHERE m.match_date > NOW() AND m.status = 'scheduled'
ORDER BY m.match_date ASC LIMIT 2";

$matches_result = $db->query($matches_query);

$current_user = getCurrentUser();
$flash = getFlashMessage();

// Club logo URLs
$club_logos = [
    'CITY' => 'https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg',
    'UNITED' => 'https://upload.wikimedia.org/wikipedia/en/7/7a/Manchester_United_FC_crest.svg'
];

$page_title = SITE_NAME . " - " . SITE_TAGLINE;
include 'includes/header.php';
?>

<style>
    .hero-gradient {
        background: linear-gradient(135deg, #6CABDD 0%, #1C2C5B 50%, #DA291C 100%);
    }
    
    .card-hover {
        transition: all 0.3s ease;
    }
    
    .card-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .badge-city {
        background: linear-gradient(135deg, #6CABDD, #1C2C5B);
    }
    
    .badge-united {
        background: linear-gradient(135deg, #DA291C, #8B0000);
    }

    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .animate-marquee {
        display: inline-block;
        animation: marquee 20s linear infinite;
    }

    .club-logo {
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
    }

    .club-logo-small {
        width: 80px;
        height: 80px;
    }

    .club-logo-large {
        width: 180px;
        height: 180px;
    }
</style>
    

    <?php if ($flash): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-50 border border-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-200 text-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-800 px-4 py-3 rounded-lg">
                <?php echo $flash['message']; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Dual Logos -->
            <div class="flex justify-center items-center gap-8 mb-8">
                <img src="<?php echo $club_logos['CITY']; ?>" alt="Manchester City" class="club-logo-large">
                <div class="text-6xl font-black text-white/50">VS</div>
                <img src="<?php echo $club_logos['UNITED']; ?>" alt="Manchester United" class="club-logo-large">
            </div>
            
            <h1 class="text-5xl md:text-6xl font-bold mb-4">
                Two Sides, One City
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-gray-100">
                Berita Eksklusif Manchester City & Manchester United
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 mt-8">
                <a href="?filter=city" class="flex items-center justify-center gap-3 px-8 py-4 bg-city-blue hover:bg-city-navy text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition transform hover:scale-105">
                    <img src="<?php echo $club_logos['CITY']; ?>" alt="Man City" class="w-6 h-6">
                    Man City News
                </a>
                <a href="?filter=united" class="flex items-center justify-center gap-3 px-8 py-4 bg-united-red hover:bg-red-800 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition transform hover:scale-105">
                    <img src="<?php echo $club_logos['UNITED']; ?>" alt="Man United" class="w-6 h-6">
                    Man United News
                </a>
            </div>
        </div>
    </section>

    <!-- Breaking News Ticker -->
    <div class="bg-gray-900 text-white py-3 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 flex items-center">
            <span class="bg-united-red px-4 py-1 rounded font-bold mr-4">LIVE</span>
            <div class="flex-1 overflow-hidden">
                <div class="animate-marquee whitespace-nowrap">
                    <?php 
                    $ticker_query = "SELECT title, slug FROM articles WHERE is_published = 1 ORDER BY published_at DESC LIMIT 5";
                    $ticker_result = $db->query($ticker_query);
                    while ($ticker = $ticker_result->fetch_assoc()):
                    ?>
                        <a href="news-detail.php?slug=<?php echo $ticker['slug']; ?>" class="mx-8 hover:text-city-blue transition">
                            ⚽ <?php echo $ticker['title']; ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Filter Buttons -->
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-4xl font-bold text-gray-900">Berita Terkini</h2>
            <div class="flex gap-2">
                <a href="index.php" class="px-4 py-2 <?php echo $filter === 'all' ? 'bg-gray-800 text-white' : 'bg-gray-200 hover:bg-gray-300'; ?> rounded-lg font-semibold transition">
                    Semua
                </a>
                <a href="?filter=city" class="flex items-center gap-2 px-4 py-2 <?php echo $filter === 'city' ? 'bg-city-blue text-white' : 'bg-gray-200 hover:bg-city-blue hover:text-white'; ?> rounded-lg font-semibold transition">
                    <img src="<?php echo $club_logos['CITY']; ?>" alt="City" class="w-4 h-4">
                    City
                </a>
                <a href="?filter=united" class="flex items-center gap-2 px-4 py-2 <?php echo $filter === 'united' ? 'bg-united-red text-white' : 'bg-gray-200 hover:bg-united-red hover:text-white'; ?> rounded-lg font-semibold transition">
                    <img src="<?php echo $club_logos['UNITED']; ?>" alt="United" class="w-4 h-4">
                    United
                </a>
            </div>
        </div>

        <!-- Featured Articles -->
        <?php if (count($featured_articles) > 0): ?>
        <div class="grid md:grid-cols-2 gap-8 mb-12">
            <?php foreach ($featured_articles as $article): ?>
                <a href="news-detail.php?slug=<?php echo $article['slug']; ?>" class="card-hover bg-white rounded-xl shadow-lg overflow-hidden group">
                    <div class="h-64 bg-gray-200 overflow-hidden">
                        <?php 
                        // Debug: Test image display
                        $image_src = getArticleImage($article['image_url'], $article['club_code']);
                        // For debugging, let's also show the image source
                        ?>
                        <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-full object-cover" 
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=800&q=80'; console.log('Image failed: <?php echo $image_src; ?>');"
                             onload="console.log('Image loaded: <?php echo $image_src; ?>');">
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="badge-<?php echo strtolower($article['club_code']); ?> text-white px-3 py-1 rounded-full text-xs font-bold">
                                <?php echo strtoupper($article['club_name']); ?>
                            </span>
                            <span class="text-gray-500 text-sm"><?php echo timeAgo($article['published_at']); ?></span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3 group-hover:text-<?php echo $article['club_code'] === 'CITY' ? 'city-blue' : 'united-red'; ?> transition">
                            <?php echo $article['title']; ?>
                        </h3>
                        <p class="text-gray-600 mb-4">
                            <?php echo truncateText($article['excerpt'], 120); ?>
                        </p>
                        <span class="text-<?php echo $article['club_code'] === 'CITY' ? 'city-blue' : 'united-red'; ?> font-bold hover:underline">
                            Baca Selengkapnya →
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- All Articles Grid -->
        <div class="grid md:grid-cols-3 gap-6">
            <?php while ($article = $articles_result->fetch_assoc()): ?>
                <a href="news-detail.php?slug=<?php echo $article['slug']; ?>" class="card-hover bg-white rounded-xl shadow-lg overflow-hidden group">
                    <div class="h-48 bg-gray-200 overflow-hidden">
                        <?php 
                        $image_src = getArticleImage($article['image_url'], $article['club_code']);
                        ?>
                        <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-full object-cover" 
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=800&q=80'; console.log('Image failed: <?php echo $image_src; ?>');"
                             onload="console.log('Image loaded: <?php echo $image_src; ?>');">
                    </div>
                    <div class="p-5">
                        <?php if ($article['club_code']): ?>
                            <span class="badge-<?php echo strtolower($article['club_code']); ?> text-white px-3 py-1 rounded-full text-xs font-bold">
                                <?php echo strtoupper($article['club_name']); ?>
                            </span>
                        <?php endif; ?>
                        <h4 class="text-lg font-bold text-gray-900 mt-3 mb-2 group-hover:text-city-blue transition">
                            <?php echo truncateText($article['title'], 60); ?>
                        </h4>
                        <p class="text-gray-600 text-sm mb-3">
                            <?php echo truncateText($article['excerpt'], 80); ?>
                        </p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span><?php echo timeAgo($article['published_at']); ?></span>
                            <span>👁️ <?php echo formatNumber($article['views']); ?></span>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>

        <!-- Upcoming Matches -->
        <?php if ($matches_result->num_rows > 0): ?>
        <section class="mt-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Jadwal Pertandingan</h2>
            <div class="grid md:grid-cols-2 gap-6">
                <?php while ($match = $matches_result->fetch_assoc()): ?>
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="text-center mb-4">
                            <span class="bg-gray-800 text-white px-4 py-1 rounded-full text-sm font-bold">
                                <?php echo $match['competition']; ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="text-center flex-1">
                                <div class="flex justify-center mb-3">
                                    <img src="<?php echo $club_logos[$match['home_code']]; ?>" alt="<?php echo $match['home_team']; ?>" class="club-logo-small">
                                </div>
                                <p class="font-bold text-gray-900"><?php echo $match['home_team']; ?></p>
                            </div>
                            <div class="text-center px-6">
                                <p class="text-3xl font-bold text-gray-400">VS</p>
                                <p class="text-sm text-gray-600 mt-2">
                                    <?php echo formatDateIndo($match['match_date']); ?>
                                </p>
                            </div>
                            <div class="text-center flex-1">
                                <div class="flex justify-center mb-3">
                                    <img src="<?php echo $club_logos[$match['away_code']]; ?>" alt="<?php echo $match['away_team']; ?>" class="club-logo-small">
                                </div>
                                <p class="font-bold text-gray-900"><?php echo $match['away_team']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
        <?php endif; ?>

    </main>

<?php include 'includes/footer.php'; ?>
