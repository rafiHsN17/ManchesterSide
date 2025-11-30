<?php
/**
 * Manchester Side - Admin Create News
 */
require_once '../includes/config.php';

// Check if user is admin
if (!isAdmin()) {
    setFlashMessage('error', 'Anda tidak memiliki akses ke halaman ini');
    redirect('../index.php');
}

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    $content = $_POST['content'] ?? ''; // Don't sanitize HTML content
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $tags = sanitize($_POST['tags'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = sanitize($_POST['status'] ?? 'draft');
    
    // Validation
    if (empty($title)) {
        $errors[] = 'Judul berita wajib diisi';
    }
    
    if (empty($content)) {
        $errors[] = 'Konten berita wajib diisi';
    }
    
    if (empty($category)) {
        $errors[] = 'Kategori wajib dipilih';
    }
    
    // Generate slug if empty
    if (empty($slug)) {
        $slug = generateSlug($title);
    } else {
        $slug = generateSlug($slug);
    }
    
    // Handle image upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadImage($_FILES['image'], '../uploads/news/');
        if ($upload_result['success']) {
            $image_url = 'uploads/news/' . $upload_result['filename'];
        } else {
            $errors[] = $upload_result['error'];
        }
    }
    
    // Insert news if no errors
    if (empty($errors)) {
        $db = getDB();
        $author_id = $_SESSION['user_id'];
        
        $stmt = $db->prepare("INSERT INTO news (title, slug, content, excerpt, image_url, category, tags, author_id, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssiss", $title, $slug, $content, $excerpt, $image_url, $category, $tags, $author_id, $featured, $status);
        
        if ($stmt->execute()) {
            $success = true;
            setFlashMessage('success', 'Berita berhasil ditambahkan!');
            redirect('index.php');
        } else {
            $errors[] = 'Terjadi kesalahan saat menyimpan berita: ' . $db->error;
        }
    }
}

// Helper function to generate slug
function generateSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    
    // Add timestamp if slug exists
    $db = getDB();
    $check_slug = $string;
    $counter = 1;
    
    while (true) {
        $stmt = $db->prepare("SELECT id FROM news WHERE slug = ?");
        $stmt->bind_param("s", $check_slug);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            break;
        }
        
        $check_slug = $string . '-' . $counter;
        $counter++;
    }
    
    return $check_slug;
}

// Helper function to upload image
function uploadImage($file, $upload_dir) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Check file type
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'error' => 'Tipe file tidak diizinkan. Gunakan JPG, PNG, GIF, atau WebP'];
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'Ukuran file terlalu besar. Maksimal 5MB'];
    }
    
    // Create upload directory if not exists
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Gagal mengupload file'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Berita - Admin <?php echo SITE_NAME; ?></title>
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
                    <a href="index.php" class="text-gray-700 hover:text-city-blue font-semibold">Dashboard Admin</a>
                    <a href="../logout.php" class="text-united-red hover:text-red-700 font-semibold">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Tambah Berita Baru</h1>
            <p class="text-gray-600">Buat dan publikasikan berita terbaru</p>
        </div>

        <!-- Error Messages -->
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

        <!-- Form -->
        <form method="POST" action="" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg p-6 space-y-6">
            
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                    Judul Berita <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="<?php echo $_POST['title'] ?? ''; ?>"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                    placeholder="Masukkan judul berita"
                >
            </div>

            <!-- Slug -->
            <div>
                <label for="slug" class="block text-sm font-semibold text-gray-700 mb-2">
                    Slug (URL) <span class="text-gray-500 text-xs">(Kosongkan untuk generate otomatis)</span>
                </label>
                <input 
                    type="text" 
                    id="slug" 
                    name="slug" 
                    value="<?php echo $_POST['slug'] ?? ''; ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                    placeholder="judul-berita-url-friendly"
                >
            </div>

            <!-- Image Upload -->
            <div>
                <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
                    Gambar Utama <span class="text-gray-500 text-xs">(Max 5MB - JPG, PNG, GIF, WebP)</span>
                </label>
                <input 
                    type="file" 
                    id="image" 
                    name="image" 
                    accept="image/*"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                >
                <div id="imagePreview" class="mt-3 hidden">
                    <img src="" alt="Preview" class="max-w-xs rounded-lg border">
                </div>
            </div>

            <!-- Excerpt -->
            <div>
                <label for="excerpt" class="block text-sm font-semibold text-gray-700 mb-2">
                    Ringkasan
                </label>
                <textarea 
                    id="excerpt" 
                    name="excerpt" 
                    rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                    placeholder="Ringkasan singkat berita (opsional)"
                ><?php echo $_POST['excerpt'] ?? ''; ?></textarea>
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-sm font-semibold text-gray-700 mb-2">
                    Konten Berita <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="content" 
                    name="content" 
                    rows="15"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent font-mono text-sm"
                    placeholder="Tulis konten berita di sini..."
                ><?php echo $_POST['content'] ?? ''; ?></textarea>
                <p class="text-xs text-gray-500 mt-1">Gunakan HTML untuk formatting</p>
            </div>

            <!-- Category -->
            <div>
                <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <select 
                    id="category" 
                    name="category" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                >
                    <option value="">Pilih Kategori</option>
                    <option value="CITY" <?php echo (($_POST['category'] ?? '') === 'CITY') ? 'selected' : ''; ?>>Manchester City</option>
                    <option value="UNITED" <?php echo (($_POST['category'] ?? '') === 'UNITED') ? 'selected' : ''; ?>>Manchester United</option>
                    <option value="BOTH" <?php echo (($_POST['category'] ?? '') === 'BOTH') ? 'selected' : ''; ?>>Keduanya</option>
                    <option value="GENERAL" <?php echo (($_POST['category'] ?? '') === 'GENERAL') ? 'selected' : ''; ?>>Umum</option>
                </select>
            </div>

            <!-- Tags -->
            <div>
                <label for="tags" class="block text-sm font-semibold text-gray-700 mb-2">
                    Tags
                </label>
                <input 
                    type="text" 
                    id="tags" 
                    name="tags" 
                    value="<?php echo $_POST['tags'] ?? ''; ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                    placeholder="transfer, liga-inggris, pemain (pisahkan dengan koma)"
                >
            </div>

            <!-- Featured Checkbox -->
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    id="featured" 
                    name="featured" 
                    value="1"
                    <?php echo (isset($_POST['featured'])) ? 'checked' : ''; ?>
                    class="w-5 h-5 text-city-blue border-gray-300 rounded focus:ring-city-blue"
                >
                <label for="featured" class="ml-3 text-sm font-semibold text-gray-700">
                    Jadikan Berita Unggulan
                </label>
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                    Status
                </label>
                <select 
                    id="status" 
                    name="status" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                >
                    <option value="draft" <?php echo (($_POST['status'] ?? 'draft') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo (($_POST['status'] ?? '') === 'published') ? 'selected' : ''; ?>>Publish</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-4 pt-4">
                <button 
                    type="submit"
                    class="flex-1 bg-gradient-to-r from-city-blue to-city-navy text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transform hover:scale-[1.02] transition"
                >
                    💾 Simpan Berita
                </button>
                <a 
                    href="index.php"
                    class="px-6 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition"
                >
                    Batal
                </a>
            </div>

        </form>

    </div>

    <script>
        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    const img = preview.querySelector('img');
                    img.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function(e) {
            const slugInput = document.getElementById('slug');
            if (!slugInput.value || slugInput.value === '') {
                const title = e.target.value;
                const slug = title.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/[\s-]+/g, '-')
                    .trim();
                slugInput.value = slug;
            }
        });
    </script>

</body>
</html>