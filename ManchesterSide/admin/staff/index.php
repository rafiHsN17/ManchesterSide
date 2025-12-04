<?php
/**
 * Manchester Side - Admin Dashboard
 */
require_once '../includes/config.php';

// Check if user is admin
if (!isAdmin()) {
    setFlashMessage('error', 'Anda tidak memiliki akses ke halaman ini');
    redirect('../index.php');
}

$db = getDB();

// Get statistics
$stats = [
    'total_news' => 0,
    'published_news' => 0,
    'draft_news' => 0,
    'total_users' => 0
];

// Total news
$result = $db->query("SELECT COUNT(*) as count FROM news");
$stats['total_news'] = $result->fetch_assoc()['count'];

// Published news
$result = $db->query("SELECT COUNT(*) as count FROM news WHERE status = 'published'");
$stats['published_news'] = $result->fetch_assoc()['count'];

// Draft news
$result = $db->query("SELECT COUNT(*) as count FROM news WHERE status = 'draft'");
$stats['draft_news'] = $result->fetch_assoc()['count'];

// Total users
$result = $db->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $result->fetch_assoc()['count'];

// Get recent news
$recent_news = [];
$result = $db->query("
    SELECT n.*, u.full_name as author_name 
    FROM news n 
    LEFT JOIN users u ON n.author_id = u.id 
    ORDER BY n.created_at DESC 
    LIMIT 10
");
while ($row = $result->fetch_assoc()) {
    $recent_news[] = $row;
}

// Get flash message
$flash = getFlashMessage();

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $news_id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM news WHERE id = ?");
    $stmt->bind_param("i", $news_id);
    if ($stmt->execute()) {
        setFlashMessage('success', 'Berita berhasil dihapus');
    } else {
        setFlashMessage('error', 'Gagal menghapus berita');
    }
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="../index.php" class="flex items-center space-x-3">
                    <div class="flex items-center">
                        <img src="https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg" alt="Manchester City" class="w-10 h-10 object-contain">
                        <img src="https://upload.wikimedia.org/wikipedia/en/7/7a/Manchester_United_FC_crest.svg" alt="Manchester United" class="w-10 h-10 object-contain -ml-2">
                    </div>
                    <span class="text-2xl font-bold bg-gradient-to-r from-city-blue via-gray-800 to-united-red bg-clip-text text-transparent">
                        Manchester Side
                    </span>
                </a>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">👤 <?php echo $_SESSION['username']; ?></span>
                    <a href="../index.php" class="text-gray-700 hover:text-city-blue font-semibold">Ke Website</a>
                    <a href="../logout.php" class="text-united-red hover:text-red-700 font-semibold">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard Admin</h1>
            <p class="text-gray-600">Kelola berita dan konten Manchester Side</p>
        </div>

        <!-- Flash Message -->
        <?php if ($flash): ?>
            <div class="mb-6 bg-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-50 border border-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-200 text-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-800 px-4 py-3 rounded-lg">
                <p class="flex items-center">
                    <span class="text-xl mr-2"><?php echo $flash['type'] === 'success' ? '✅' : '❌'; ?></span>
                    <?php echo $flash['message']; ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold">Total Berita</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $stats['total_news']; ?></p>
                    </div>
                    <div class="text-4xl">📰</div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold">Dipublikasi</p>
                        <p class="text-3xl font-bold text-green-600 mt-2"><?php echo $stats['published_news']; ?></p>
                    </div>
                    <div class="text-4xl">✅</div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold">Draft</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-2"><?php echo $stats['draft_news']; ?></p>
                    </div>
                    <div class="text-4xl">📝</div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold">Total Users</p>
                        <p class="text-3xl font-bold text-city-blue mt-2"><?php echo $stats['total_users']; ?></p>
                    </div>
                    <div class="text-4xl">👥</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Aksi Cepat</h2>
            <div class="flex flex-wrap gap-3">
                <a href="create.php" class="bg-gradient-to-r from-city-blue to-city-navy text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transform hover:scale-[1.02] transition">
                    ➕ Tambah Berita Baru
                </a>
                <a href="users.php" class="bg-gray-700 text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transform hover:scale-[1.02] transition">
                    👥 Kelola Users
                </a>
                <a href="../index.php" class="border-2 border-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-50 transition">
                    🌐 Lihat Website
                </a>
            </div>
        </div>

        <!-- Recent News Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-gray-900">Berita Terbaru</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Penulis</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($recent_news)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    Belum ada berita. <a href="create.php" class="text-city-blue font-semibold hover:underline">Tambah berita pertama</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_news as $news): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <?php if ($news['featured']): ?>
                                                <span class="text-yellow-500 mr-2">⭐</span>
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($news['title']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($news['slug'], 0, 40)); ?>...</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $category_colors = [
                                            'CITY' => 'bg-city-blue text-white',
                                            'UNITED' => 'bg-united-red text-white',
                                            'BOTH' => 'bg-purple-600 text-white',
                                            'GENERAL' => 'bg-gray-600 text-white'
                                        ];
                                        $color_class = $category_colors[$news['category']] ?? 'bg-gray-400 text-white';
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $color_class; ?>">
                                            <?php echo $news['category']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <?php echo htmlspecialchars($news['author_name']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($news['status'] === 'published'): ?>
                                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Published</span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <?php echo date('d M Y', strtotime($news['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <a href="../news.php?slug=<?php echo $news['slug']; ?>" target="_blank" class="text-city-blue hover:text-city-navy font-semibold text-sm" title="Lihat">
                                                👁️
                                            </a>
                                            <a href="edit.php?id=<?php echo $news['id']; ?>" class="text-green-600 hover:text-green-800 font-semibold text-sm" title="Edit">
                                                ✏️
                                            </a>
                                            <a href="?delete=<?php echo $news['id']; ?>" onclick="return confirm('Yakin ingin menghapus berita ini?')" class="text-united-red hover:text-red-800 font-semibold text-sm" title="Hapus">
                                                🗑️
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>