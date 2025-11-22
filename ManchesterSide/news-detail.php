<?php
/**
 * Manchester Side - News Detail Page
 */
require_once 'includes/config.php';

$db = getDB();

// Get article slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    redirect('index.php');
}

// Get article details
$stmt = $db->prepare("SELECT 
    a.id, a.title, a.slug, a.content, a.excerpt, a.image_url, a.category, a.views, a.published_at,
    c.name as club_name, c.code as club_code, c.color_primary, c.color_secondary,
    ad.full_name as author_name
FROM articles a
LEFT JOIN clubs c ON a.club_id = c.id
JOIN admins ad ON a.author_id = ad.id
WHERE a.slug = ? AND a.is_published = 1");

$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('index.php');
}

$article = $result->fetch_assoc();

// Update views count
$update_views = $db->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
$update_views->bind_param("i", $article['id']);
$update_views->execute();

// Check if user has favorited this article
$is_favorited = false;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $fav_check = $db->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND article_id = ?");
    $fav_check->bind_param("ii", $user_id, $article['id']);
    $fav_check->execute();
    $is_favorited = $fav_check->get_result()->num_rows > 0;
}

// Handle favorite toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'])) {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Silakan login untuk menyimpan favorit');
        redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
    
    $user_id = $_SESSION['user_id'];
    $article_id = $article['id'];
    
    if ($is_favorited) {
        // Remove from favorites
        $stmt = $db->prepare("DELETE FROM user_favorites WHERE user_id = ? AND article_id = ?");
        $stmt->bind_param("ii", $user_id, $article_id);
        $stmt->execute();
        setFlashMessage('success', 'Berita dihapus dari favorit');
    } else {
        // Add to favorites
        $stmt = $db->prepare("INSERT INTO user_favorites (user_id, article_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $article_id);
        $stmt->execute();
        setFlashMessage('success', 'Berita ditambahkan ke favorit');
    }
    
    redirect($_SERVER['REQUEST_URI']);
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Silakan login untuk berkomentar');
        redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
    
    $user_id = $_SESSION['user_id'];
    $comment = sanitize($_POST['comment'] ?? '');
    
    if (!empty($comment)) {
        $stmt = $db->prepare("INSERT INTO comments (article_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $article['id'], $user_id, $comment);
        $stmt->execute();
        setFlashMessage('success', 'Komentar berhasil ditambahkan');
        redirect($_SERVER['REQUEST_URI']);
    }
}

// Get comments
$comments_query = $db->prepare("SELECT 
    c.id, c.comment, c.created_at,
    u.username, u.avatar, u.favorite_team
FROM comments c
JOIN users u ON c.user_id = u.id
WHERE c.article_id = ? AND c.is_approved = 1
ORDER BY c.created_at DESC
LIMIT 50");

$comments_query->bind_param("i", $article['id']);
$comments_query->execute();
$comments_result = $comments_query->get_result();

// Get related articles (same club)
$related_query = $db->prepare("SELECT 
    a.id, a.title, a.slug, a.image_url, a.published_at,
    c.code as club_code
FROM articles a
LEFT JOIN clubs c ON a.club_id = c.id
WHERE a.is_published = 1 AND a.id != ? AND a.club_id = ?
ORDER BY a.published_at DESC
LIMIT 3");

$related_query->bind_param("ii", $article['id'], $article['club_code'] ? 1 : 2);
$related_query->execute();
$related_result = $related_query->get_result();

$current_user = getCurrentUser();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $article['title']; ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo $article['excerpt']; ?>">
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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        .article-content p {
            margin-bottom: 1rem;
            line-height: 1.8;
        }
        
        .article-content h2 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        
        .article-content h3 {
            font-size: 1.25rem;
            font-weight: bold;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navigation (Same as index.php) -->
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
                    <?php if ($current_user): ?>
                        <a href="profile.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-gray-100 rounded-lg transition">
                            <div class="w-8 h-8 bg-gradient-to-r from-city-blue to-united-red rounded-full flex items-center justify-center text-white font-bold">
                                <?php echo strtoupper(substr($current_user['username'], 0, 1)); ?>
                            </div>
                            <span class="font-semibold"><?php echo $current_user['username']; ?></span>
                        </a>
                        <a href="logout.php" class="px-4 py-2 text-gray-700 hover:text-red-600 font-semibold transition">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="px-4 py-2 text-gray-700 hover:text-city-blue font-semibold transition">Masuk</a>
                        <a href="register.php" class="px-5 py-2 bg-gradient-to-r from-city-blue to-united-red text-white font-semibold rounded-lg hover:shadow-lg transition">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <?php if ($flash): ?>
        <div class="max-w-4xl mx-auto px-4 mt-4">
            <div class="bg-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-50 border border-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-200 text-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-800 px-4 py-3 rounded-lg">
                <?php echo $flash['message']; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Article Content -->
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Breadcrumb -->
        <nav class="mb-6 text-sm">
            <a href="index.php" class="text-gray-600 hover:text-city-blue">Beranda</a>
            <span class="text-gray-400 mx-2">/</span>
            <a href="news.php" class="text-gray-600 hover:text-city-blue">Berita</a>
            <span class="text-gray-400 mx-2">/</span>
            <span class="text-gray-900 font-semibold"><?php echo $article['club_name'] ?? 'Umum'; ?></span>
        </nav>

        <!-- Article Header -->
        <header class="mb-8">
            <?php if ($article['club_code']): ?>
                <div class="mb-4">
                    <span class="inline-block px-4 py-2 bg-gradient-to-r from-<?php echo $article['club_code'] === 'CITY' ? 'city-blue' : 'united-red'; ?>-500 to-<?php echo $article['club_code'] === 'CITY' ? 'city-navy' : 'red'; ?>-900 text-white rounded-full text-sm font-bold">
                        <?php echo getClubEmoji($article['club_code']); ?> <?php echo strtoupper($article['club_name']); ?>
                    </span>
                    <span class="ml-3 text-gray-500 text-sm uppercase font-semibold"><?php echo $article['category']; ?></span>
                </div>
            <?php endif; ?>
            
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4 leading-tight">
                <?php echo $article['title']; ?>
            </h1>
            
            <div class="flex items-center justify-between py-4 border-y border-gray-200">
                <div class="flex items-center space-x-4 text-gray-600">
                    <div class="flex items-center space-x-2">
                        <span class="text-lg">✍️</span>
                        <span class="font-semibold"><?php echo $article['author_name']; ?></span>
                    </div>
                    <span class="text-gray-400">•</span>
                    <div class="flex items-center space-x-2">
                        <span class="text-lg">📅</span>
                        <span><?php echo formatDateIndo($article['published_at']); ?></span>
                    </div>
                    <span class="text-gray-400">•</span>
                    <div class="flex items-center space-x-2">
                        <span class="text-lg">👁️</span>
                        <span><?php echo formatNumber($article['views']); ?> views</span>
                    </div>
                </div>
                
                <!-- Favorite Button -->
                <form method="POST" action="">
                    <button type="submit" name="toggle_favorite" class="flex items-center space-x-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                        <span class="text-xl"><?php echo $is_favorited ? '❤️' : '🤍'; ?></span>
                        <span class="font-semibold"><?php echo $is_favorited ? 'Tersimpan' : 'Simpan'; ?></span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Featured Image -->
        <div class="mb-8">
            <div class="aspect-w-16 aspect-h-9 bg-gradient-to-br from-<?php echo $article['club_code'] === 'CITY' ? 'city-blue' : ($article['club_code'] === 'UNITED' ? 'united-red' : 'gray'); ?>-500 to-<?php echo $article['club_code'] === 'CITY' ? 'city-navy' : ($article['club_code'] === 'UNITED' ? 'red' : 'gray'); ?>-900 rounded-xl overflow-hidden shadow-xl">
                <div class="flex items-center justify-center text-white text-9xl">
                    <?php 
                    if ($article['club_code'] === 'CITY') echo '🔵';
                    elseif ($article['club_code'] === 'UNITED') echo '🔴';
                    else echo '⚽';
                    ?>
                </div>
            </div>
        </div>

        <!-- Article Excerpt -->
        <?php if ($article['excerpt']): ?>
            <div class="bg-gray-100 border-l-4 border-<?php echo $article['club_code'] === 'CITY' ? 'city-blue' : 'united-red'; ?> p-6 mb-8 rounded-r-lg">
                <p class="text-lg text-gray-700 italic leading-relaxed">
                    <?php echo $article['excerpt']; ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Article Body -->
        <div class="article-content prose prose-lg max-w-none text-gray-800">
            <?php echo nl2br($article['content']); ?>
        </div>

        <!-- Share Buttons -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <p class="text-gray-600 font-semibold mb-4">Bagikan artikel ini:</p>
            <div class="flex space-x-3">
                <button class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                    📘 Facebook
                </button>
                <button class="px-6 py-3 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition font-semibold">
                    🐦 Twitter
                </button>
                <button class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                    💬 WhatsApp
                </button>
            </div>
        </div>

    </article>

    <!-- Comments Section -->
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 border-t border-gray-200">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">
            Komentar (<?php echo $comments_result->num_rows; ?>)
        </h2>

        <!-- Comment Form -->
        <?php if (isLoggedIn()): ?>
            <form method="POST" action="" class="mb-8 bg-white rounded-xl shadow-lg p-6">
                <textarea 
                    name="comment" 
                    rows="4" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent resize-none"
                    placeholder="Tulis komentar Anda..."
                ></textarea>
                <div class="mt-4 flex justify-end">
                    <button 
                        type="submit" 
                        name="submit_comment"
                        class="px-6 py-3 bg-gradient-to-r from-city-blue to-united-red text-white font-bold rounded-lg hover:shadow-lg transition"
                    >
                        Kirim Komentar
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                <p class="text-gray-700 mb-4">Silakan login untuk berkomentar</p>
                <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="inline-block px-6 py-3 bg-city-blue text-white font-bold rounded-lg hover:bg-city-navy transition">
                    Login Sekarang
                </a>
            </div>
        <?php endif; ?>

        <!-- Comments List -->
        <div class="space-y-6">
            <?php while ($comment = $comments_result->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-city-blue to-united-red rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                            <?php echo strtoupper(substr($comment['username'], 0, 1)); ?>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="font-bold text-gray-900"><?php echo $comment['username']; ?></span>
                                <?php if ($comment['favorite_team']): ?>
                                    <span class="px-2 py-1 bg-<?php echo $comment['favorite_team'] === 'CITY' ? 'city-blue' : 'united-red'; ?> text-white text-xs rounded-full font-bold">
                                        <?php echo getClubEmoji($comment['favorite_team']); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="text-gray-500 text-sm"><?php echo timeAgo($comment['created_at']); ?></span>
                            </div>
                            <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <?php if ($comments_result->num_rows === 0): ?>
                <div class="text-center py-12 text-gray-500">
                    <p class="text-6xl mb-4">💬</p>
                    <p class="text-lg">Belum ada komentar. Jadilah yang pertama berkomentar!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Related Articles -->
    <?php if ($related_result->num_rows > 0): ?>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 bg-gray-100">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Berita Terkait</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <?php while ($related = $related_result->fetch_assoc()): ?>
                <a href="news-detail.php?slug=<?php echo $related['slug']; ?>" class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition group">
                    <div class="h-48 bg-gradient-to-br from-<?php echo $related['club_code'] === 'CITY' ? 'city-blue' : 'united-red'; ?>-500 to-<?php echo $related['club_code'] === 'CITY' ? 'city-navy' : 'red'; ?>-900 flex items-center justify-center text-white text-4xl">
                        <?php echo $related['club_code'] === 'CITY' ? '🔵' : '🔴'; ?>
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-city-blue transition">
                            <?php echo truncateText($related['title'], 70); ?>
                        </h3>
                        <p class="text-sm text-gray-500"><?php echo timeAgo($related['published_at']); ?></p>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="flex items-center justify-center space-x-2 mb-4">
                <div class="flex">
                    <div class="w-6 h-6 bg-city-blue rounded-full"></div>
                    <div class="w-6 h-6 bg-united-red rounded-full -ml-2"></div>
                </div>
                <span class="text-xl font-bold">Manchester Side</span>
            </div>
            <p class="text-gray-400 text-sm mb-4"><?php echo SITE_TAGLINE; ?></p>
            <p class="text-gray-500 text-xs">&copy; 2025 Manchester Side. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>