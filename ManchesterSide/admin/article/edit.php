<?php
/**
 * Manchester Side - Edit Article (Admin)
 * Enhanced version with delete functionality
 */
require_once '../../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

$db = getDB();
$admin = getCurrentAdmin();
$errors = [];

// Get article ID
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id === 0) {
    setFlashMessage('error', 'ID artikel tidak valid');
    redirect('index.php');
}

// Handle delete action
if (isset($_GET['delete']) && $_GET['delete'] == $article_id) {
    $delete_stmt = $db->prepare("DELETE FROM articles WHERE id = ?");
    $delete_stmt->bind_param("i", $article_id);
    
    if ($delete_stmt->execute()) {
        setFlashMessage('success', 'Artikel berhasil dihapus');
        redirect('index.php');
    } else {
        setFlashMessage('error', 'Gagal menghapus artikel');
    }
}

// Get article data
$stmt = $db->prepare("SELECT a.*, c.name as club_name, c.code as club_code 
                      FROM articles a 
                      LEFT JOIN clubs c ON a.club_id = c.id 
                      WHERE a.id = ?");
$stmt->bind_param("i", $article_id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    setFlashMessage('error', 'Artikel tidak ditemukan');
    redirect('index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_article'])) {
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $club_id = !empty($_POST['club_id']) ? (int)$_POST['club_id'] : null;
    $category = sanitize($_POST['category'] ?? 'news');
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Validation
    if (empty($title)) {
        $errors[] = 'Judul berita wajib diisi';
    }
    
    if (empty($content)) {
        $errors[] = 'Konten berita wajib diisi';
    }
    
    // Generate new slug if title changed
    if ($title !== $article['title']) {
        $slug = generateSlug($title);
        
        // Check if slug exists (excluding current article)
        $check_slug = $db->prepare("SELECT id FROM articles WHERE slug = ? AND id != ?");
        $check_slug->bind_param("si", $slug, $article_id);
        $check_slug->execute();
        if ($check_slug->get_result()->num_rows > 0) {
            $slug = $slug . '-' . time();
        }
    } else {
        $slug = $article['slug'];
    }
    
    // Auto-generate excerpt if empty
    if (empty($excerpt)) {
        $excerpt = truncateText(strip_tags($content), 200);
    }
    
    if (empty($errors)) {
        // Update article
        $update_stmt = $db->prepare("UPDATE articles SET 
            title = ?, 
            slug = ?, 
            content = ?, 
            excerpt = ?, 
            club_id = ?, 
            category = ?, 
            is_published = ?, 
            is_featured = ?,
            published_at = ?,
            updated_at = NOW()
            WHERE id = ?");
        
        // Set published_at
        if ($is_published && !$article['published_at']) {
            $published_at = date('Y-m-d H:i:s');
        } else {
            $published_at = $article['published_at'];
        }
        
        $update_stmt->bind_param("ssssissssi", 
            $title, $slug, $content, $excerpt, $club_id, 
            $category, $is_published, $is_featured, $published_at, $article_id
        );
        
        if ($update_stmt->execute()) {
            setFlashMessage('success', 'Berita berhasil diperbarui!');
            redirect('edit.php?id=' . $article_id);
        } else {
            $errors[] = 'Gagal memperbarui berita: ' . $db->error;
        }
    }
    
    // Reload article data after update
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT a.*, c.name as club_name, c.code as club_code 
                              FROM articles a 
                              LEFT JOIN clubs c ON a.club_id = c.id 
                              WHERE a.id = ?");
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $article = $stmt->get_result()->fetch_assoc();
    }
}

// Get clubs for dropdown
$clubs = $db->query("SELECT id, name, code FROM clubs ORDER BY name");

// Get comment count
$comment_count = $db->query("SELECT COUNT(*) as c FROM comments WHERE article_id = {$article_id}")->fetch_assoc()['c'];

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Berita - Admin Panel</title>
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
<body class="bg-gray-100">

    <div class="flex h-screen">
        
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex flex-col">
            <div class="p-6 border-b border-gray-800">
                <div class="flex items-center space-x-3">
                    <div class="flex">
                        <div class="w-8 h-8 bg-city-blue rounded-full"></div>
                        <div class="w-8 h-8 bg-united-red rounded-full -ml-3"></div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Admin Panel</h1>
                        <p class="text-xs text-gray-400">Manchester Side</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 p-4 space-y-2">
                <a href="../dashboard.php" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition">
                    <span class="text-xl">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="index.php" class="flex items-center space-x-3 px-4 py-3 bg-city-blue rounded-lg text-white font-semibold">
                    <span class="text-xl">📰</span>
                    <span>Berita</span>
                </a>
                <a href="../players/" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition">
                    <span class="text-xl">👥</span>
                    <span>Pemain</span>
                </a>
                <a href="../staff/" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition">
                    <span class="text-xl">🎯</span>
                    <span>Staff</span>
                </a>
                <a href="../comments/" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition">
                    <span class="text-xl">💬</span>
                    <span>Komentar</span>
                </a>
            </nav>

            <div class="p-4 border-t border-gray-800">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-city-blue to-united-red rounded-full flex items-center justify-center text-white font-bold">
                        <?php echo strtoupper(substr($admin['username'], 0, 1)); ?>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm"><?php echo $admin['full_name']; ?></p>
                        <p class="text-xs text-gray-400"><?php echo ucfirst($admin['role']); ?></p>
                    </div>
                </div>
                <a href="../../index.php" target="_blank" class="block w-full text-center px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg text-sm font-semibold transition mb-2">
                    👁️ View Site
                </a>
                <a href="../logout.php" class="block w-full text-center px-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg text-sm font-semibold transition">
                    🚪 Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Berita</h1>
                        <p class="text-gray-600 mt-1">Perbarui artikel: <span class="font-semibold"><?php echo truncateText($article['title'], 50); ?></span></p>
                    </div>
                    <div class="flex gap-3">
                        <a href="../../news-detail.php?slug=<?php echo $article['slug']; ?>" target="_blank" class="px-6 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition">
                            👁️ Preview
                        </a>
                        <a href="index.php" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition">
                            ← Kembali
                        </a>
                    </div>
                </div>
            </header>

            <div class="p-6">

                <?php if ($flash): ?>
                    <div class="mb-6 bg-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-50 border border-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-200 text-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-800 px-4 py-3 rounded-lg">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                        <p class="font-semibold mb-2">❌ Terjadi kesalahan:</p>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Article Info Card -->
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-xl p-6 mb-6">
                    <div class="grid md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">ID Artikel</p>
                            <p class="text-2xl font-bold text-gray-900">#<?php echo $article['id']; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Status</p>
                            <span class="inline-block px-3 py-1 bg-<?php echo $article['is_published'] ? 'green' : 'yellow'; ?>-100 text-<?php echo $article['is_published'] ? 'green' : 'yellow'; ?>-800 rounded-full text-sm font-bold">
                                <?php echo $article['is_published'] ? '✅ Published' : '📝 Draft'; ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Views</p>
                            <p class="text-2xl font-bold text-gray-900">👁️ <?php echo formatNumber($article['views']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Komentar</p>
                            <p class="text-2xl font-bold text-gray-900">💬 <?php echo $comment_count; ?></p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-blue-200 flex items-center justify-between text-sm text-gray-600">
                        <div>
                            <span class="font-semibold">Dibuat:</span> <?php echo formatDateIndo($article['created_at']); ?>
                        </div>
                        <?php if ($article['updated_at'] && $article['updated_at'] != $article['created_at']): ?>
                            <div>
                                <span class="font-semibold">Diupdate:</span> <?php echo timeAgo($article['updated_at']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($article['published_at']): ?>
                            <div>
                                <span class="font-semibold">Dipublikasi:</span> <?php echo formatDateIndo($article['published_at']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <form method="POST" action="" class="space-y-6">
                    
                    <div class="grid lg:grid-cols-3 gap-6">
                        
                        <!-- Main Content Area -->
                        <div class="lg:col-span-2 space-y-6">
                            
                            <!-- Title -->
                            <div class="bg-white rounded-xl shadow-lg p-6">
                                <label for="title" class="block text-sm font-bold text-gray-700 mb-2">
                                    Judul Berita <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="title" 
                                    name="title" 
                                    value="<?php echo htmlspecialchars($article['title']); ?>"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent text-lg"
                                    placeholder="Judul berita"
                                >
                                <p class="mt-2 text-xs text-gray-500">
                                    Slug: <span class="font-mono bg-gray-100 px-2 py-1 rounded"><?php echo $article['slug']; ?></span>
                                </p>
                            </div>

                            <!-- Content -->
                            <div class="bg-white rounded-xl shadow-lg p-6">
                                <label for="content" class="block text-sm font-bold text-gray-700 mb-2">
                                    Konten Berita <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    id="content" 
                                    name="content" 
                                    rows="15"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent resize-none font-mono text-sm"
                                    placeholder="Tulis konten berita lengkap di sini..."
                                ><?php echo htmlspecialchars($article['content']); ?></textarea>
                                <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                    <span id="charCount">0 karakter</span>
                                    <span>💡 Tip: Pisahkan paragraf dengan enter</span>
                                </div>
                            </div>

                            <!-- Excerpt -->
                            <div class="bg-white rounded-xl shadow-lg p-6">
                                <label for="excerpt" class="block text-sm font-bold text-gray-700 mb-2">
                                    Ringkasan (Excerpt)
                                </label>
                                <textarea 
                                    id="excerpt" 
                                    name="excerpt" 
                                    rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent resize-none"
                                    placeholder="Ringkasan singkat berita (opsional, akan di-generate otomatis jika kosong)"
                                ><?php echo htmlspecialchars($article['excerpt']); ?></textarea>
                                <p class="mt-2 text-xs text-gray-500">
                                    ℹ️ Ringkasan akan muncul di preview card dan daftar berita
                                </p>
                            </div>

                        </div>

                        <!-- Sidebar Settings -->
                        <div class="space-y-6">
                            
                            <!-- Publish Settings -->
                            <div class="bg-white rounded-xl shadow-lg p-6">
                                <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                                    <span class="text-xl mr-2">📤</span>
                                    Publikasi
                                </h3>
                                
                                <div class="space-y-4">
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            name="is_published" 
                                            value="1"
                                            <?php echo $article['is_published'] ? 'checked' : ''; ?>
                                            class="w-5 h-5 text-city-blue border-gray-300 rounded focus:ring-city-blue"
                                        >
                                        <div>
                                            <span class="font-semibold text-gray-900">Publish Artikel</span>
                                            <p class="text-xs text-gray-500">Berita tayang di website</p>
                                        </div>
                                    </label>

                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            name="is_featured" 
                                            value="1"
                                            <?php echo $article['is_featured'] ? 'checked' : ''; ?>
                                            class="w-5 h-5 text-city-blue border-gray-300 rounded focus:ring-city-blue"
                                        >
                                        <div>
                                            <span class="font-semibold text-gray-900">Featured Article</span>
                                            <p class="text-xs text-gray-500">Tampilkan di highlight homepage</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Club Selection -->
                            <div class="bg-white rounded-xl shadow-lg p-6">
                                <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                                    <span class="text-xl mr-2">⚽</span>
                                    Klub
                                </h3>
                                
                                <select 
                                    name="club_id" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                                >
                                    <option value="">⚪ Berita Umum</option>
                                    <?php while ($club = $clubs->fetch_assoc()): ?>
                                        <option value="<?php echo $club['id']; ?>" <?php echo ($article['club_id'] == $club['id']) ? 'selected' : ''; ?>>
                                            <?php echo $club['code'] === 'CITY' ? '🔵' : '🔴'; ?> <?php echo $club['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <p class="mt-2 text-xs text-gray-500">
                                    <?php if ($article['club_code']): ?>
                                        Saat ini: <strong><?php echo $article['club_name']; ?></strong>
                                    <?php else: ?>
                                        Saat ini: <strong>Berita Umum</strong>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <!-- Category -->
                            <div class="bg-white rounded-xl shadow-lg p-6">
                                <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                                    <span class="text-xl mr-2">🏷️</span>
                                    Kategori
                                </h3>
                                
                                <select 
                                    name="category" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                                >
                                    <option value="news" <?php echo $article['category'] === 'news' ? 'selected' : ''; ?>>📰 News</option>
                                    <option value="match" <?php echo $article['category'] === 'match' ? 'selected' : ''; ?>>⚽ Match Report</option>
                                    <option value="transfer" <?php echo $article['category'] === 'transfer' ? 'selected' : ''; ?>>💼 Transfer</option>
                                    <option value="interview" <?php echo $article['category'] === 'interview' ? 'selected' : ''; ?>>🎤 Interview</option>
                                    <option value="analysis" <?php echo $article['category'] === 'analysis' ? 'selected' : ''; ?>>📊 Analysis</option>
                                </select>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="bg-white rounded-xl shadow-lg p-6 space-y-3">
                                <button 
                                    type="submit"
                                    name="update_article"
                                    class="w-full py-3 bg-gradient-to-r from-city-blue to-united-red text-white font-bold rounded-lg hover:shadow-lg transition"
                                >
                                    💾 Update Berita
                                </button>
                                
                                <a 
                                    href="index.php"
                                    class="block w-full py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition text-center"
                                >
                                    ❌ Batal
                                </a>

                                <a 
                                    href="?delete=<?php echo $article['id']; ?>"
                                    onclick="return confirm('⚠️ PERHATIAN!\n\nAnda yakin ingin menghapus berita ini?\n\n- Judul: <?php echo addslashes($article['title']); ?>\n- Views: <?php echo $article['views']; ?>\n- Komentar: <?php echo $comment_count; ?>\n\nTindakan ini tidak dapat dibatalkan!')"
                                    class="block w-full py-3 bg-red-100 text-red-700 font-bold rounded-lg hover:bg-red-200 transition text-center"
                                >
                                    🗑️ Hapus Berita
                                </a>
                            </div>

                            <!-- Tips Card -->
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                                <p class="font-semibold mb-2">💡 Tips Edit Berita:</p>
                                <ul class="space-y-1 text-xs">
                                    <li>✅ Cek ejaan sebelum update</li>
                                    <li>✅ Preview sebelum publish</li>
                                    <li>✅ Slug otomatis berubah jika judul diubah</li>
                                    <li>✅ Excerpt akan di-generate otomatis jika kosong</li>
                                </ul>
                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </main>

    </div>

    <script>
        // Character counter for content
        const contentField = document.getElementById('content');
        const charCount = document.getElementById('charCount');
        
        function updateCharCount() {
            const count = contentField.value.length;
            charCount.textContent = count.toLocaleString('id-ID') + ' karakter';
        }
        
        contentField.addEventListener('input', updateCharCount);
        updateCharCount();

        // Auto-save warning
        let contentChanged = false;
        const form = document.querySelector('form');
        
        form.addEventListener('input', () => {
            contentChanged = true;
        });
        
        form.addEventListener('submit', () => {
            contentChanged = false;
        });
        
        window.addEventListener('beforeunload', (e) => {
            if (contentChanged) {
                e.preventDefault();
                e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin keluar?';
            }
        });
    </script>

</body>
</html>