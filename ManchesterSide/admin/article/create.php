<?php
/**
 * Manchester Side - Create New Article
 */
require_once '../../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

$db = getDB();
$admin = getCurrentAdmin();
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    
    // Generate slug
    $slug = generateSlug($title);
    
    // Check if slug already exists
    $check_slug = $db->prepare("SELECT id FROM articles WHERE slug = ?");
    $check_slug->bind_param("s", $slug);
    $check_slug->execute();
    if ($check_slug->get_result()->num_rows > 0) {
        $slug = $slug . '-' . time();
    }
    
    // Auto-generate excerpt if empty
    if (empty($excerpt)) {
        $excerpt = truncateText(strip_tags($content), 200);
    }
    
    if (empty($errors)) {
        // Insert article
        $stmt = $db->prepare("INSERT INTO articles (title, slug, content, excerpt, club_id, author_id, category, is_published, is_featured, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $published_at = $is_published ? date('Y-m-d H:i:s') : null;
        $author_id = $admin['id'];
        
        $stmt->bind_param("ssssissiis", $title, $slug, $content, $excerpt, $club_id, $author_id, $category, $is_published, $is_featured, $published_at);
        
        if ($stmt->execute()) {
            $article_id = $db->insert_id;
            setFlashMessage('success', 'Berita berhasil dibuat!');
            redirect('edit.php?id=' . $article_id);
        } else {
            $errors[] = 'Gagal menyimpan berita. Silakan coba lagi.';
        }
    }
}

// Get clubs for dropdown
$clubs = $db->query("SELECT id, name, code FROM clubs ORDER BY name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Berita Baru - Admin Panel</title>
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
        
        <!-- Sidebar (Same as other admin pages) -->
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
            </nav>

            <div class="p-4 border-t border-gray-800">
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
                        <h1 class="text-3xl font-bold text-gray-900">Buat Berita Baru</h1>
                        <p class="text-gray-600 mt-1">Tulis dan publikasikan berita terbaru</p>
                    </div>
                    <a href="index.php" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition">
                        ← Kembali
                    </a>
                </div>
            </header>

            <div class="p-6">

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
                                    value="<?php echo $_POST['title'] ?? ''; ?>"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent text-lg"
                                    placeholder="Contoh: Haaland Cetak Hat-trick Lagi!"
                                    autofocus
                                >
                                <p class="mt-2 text-xs text-gray-500">
                                    💡 Tip: Gunakan judul yang menarik dan informatif
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
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent resize-none"
                                    placeholder="Tulis konten berita lengkap di sini..."
                                ><?php echo $_POST['content'] ?? ''; ?></textarea>
                                <p class="mt-2 text-xs text-gray-500">
                                    📝 Tip: Pisahkan paragraf dengan enter untuk memudahkan pembacaan
                                </p>
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
                                ><?php echo $_POST['excerpt'] ?? ''; ?></textarea>
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
                                    <!-- Publish Status -->
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            name="is_published" 
                                            value="1"
                                            <?php echo (isset($_POST['is_published']) || !isset($_POST['title'])) ? 'checked' : ''; ?>
                                            class="w-5 h-5 text-city-blue border-gray-300 rounded focus:ring-city-blue"
                                        >
                                        <div>
                                            <span class="font-semibold text-gray-900">Publish Sekarang</span>
                                            <p class="text-xs text-gray-500">Berita langsung tayang di website</p>
                                        </div>
                                    </label>

                                    <!-- Featured -->
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            name="is_featured" 
                                            value="1"
                                            <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>
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
                                        <option value="<?php echo $club['id']; ?>" <?php echo (isset($_POST['club_id']) && $_POST['club_id'] == $club['id']) ? 'selected' : ''; ?>>
                                            <?php echo $club['code'] === 'CITY' ? '🔵' : '🔴'; ?> <?php echo $club['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <p class="mt-2 text-xs text-gray-500">
                                    ℹ️ Pilih klub terkait atau biarkan umum untuk berita derby
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
                                    <option value="news" <?php echo (($_POST['category'] ?? 'news') === 'news') ? 'selected' : ''; ?>>📰 News</option>
                                    <option value="match" <?php echo (($_POST['category'] ?? '') === 'match') ? 'selected' : ''; ?>>⚽ Match Report</option>
                                    <option value="transfer" <?php echo (($_POST['category'] ?? '') === 'transfer') ? 'selected' : ''; ?>>💼 Transfer</option>
                                    <option value="interview" <?php echo (($_POST['category'] ?? '') === 'interview') ? 'selected' : ''; ?>>🎤 Interview</option>
                                    <option value="analysis" <?php echo (($_POST['category'] ?? '') === 'analysis') ? 'selected' : ''; ?>>📊 Analysis</option>
                                </select>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="bg-white rounded-xl shadow-lg p-6 space-y-3">
                                <button 
                                    type="submit"
                                    class="w-full py-3 bg-gradient-to-r from-city-blue to-united-red text-white font-bold rounded-lg hover:shadow-lg transition"
                                >
                                    💾 Simpan Berita
                                </button>
                                
                                <a 
                                    href="index.php"
                                    class="block w-full py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition text-center"
                                >
                                    ❌ Batal
                                </a>
                            </div>

                            <!-- Tips -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
                                <p class="font-semibold mb-2">💡 Tips Menulis Berita:</p>
                                <ul class="space-y-1 text-xs">
                                    <li>✅ Gunakan judul yang menarik</li>
                                    <li>✅ Tulis paragraf pendek (3-4 kalimat)</li>
                                    <li>✅ Sertakan fakta dan data</li>
                                    <li>✅ Cek ejaan sebelum publish</li>
                                    <li>✅ Pilih klub yang tepat</li>
                                </ul>
                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </main>

    </div>

    <script>
        // Auto-generate slug preview (optional)
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');
            // You could display this slug somewhere if needed
        });

        // Character counter for excerpt
        const excerptField = document.getElementById('excerpt');
        const maxChars = 200;
        
        if (excerptField) {
            excerptField.addEventListener('input', function() {
                const remaining = maxChars - this.value.length;
                if (remaining < 0) {
                    this.value = this.value.substring(0, maxChars);
                }
            });
        }
    </script>

</body>
</html>