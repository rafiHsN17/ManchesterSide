<?php
/**
 * Manchester Side - Create New Staff with Photo Upload
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
    $name = sanitize($_POST['name'] ?? '');
    $role = sanitize($_POST['role'] ?? '');
    $club_id = (int)$_POST['club_id'];
    $nationality = sanitize($_POST['nationality'] ?? '');
    $birth_date = $_POST['birth_date'] ?? null;
    $join_date = $_POST['join_date'] ?? null;
    $previous_club = sanitize($_POST['previous_club'] ?? '');
    $achievements = sanitize($_POST['achievements'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Nama staff wajib diisi';
    }
    
    if (empty($role)) {
        $errors[] = 'Role wajib diisi';
    }
    
    if (empty($club_id)) {
        $errors[] = 'Klub wajib dipilih';
    }
    
    // Handle photo upload
    $photo_url = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadImage($_FILES['photo'], 'staff');
        if ($upload_result['success']) {
            $photo_url = 'uploads/staff/' . $upload_result['filename'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO staff (name, role, club_id, nationality, birth_date, join_date, previous_club, achievements, photo_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissssssi", $name, $role, $club_id, $nationality, $birth_date, $join_date, $previous_club, $achievements, $photo_url, $is_active);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Staff berhasil ditambahkan!');
            redirect('index.php');
        } else {
            $errors[] = 'Gagal menyimpan staff';
        }
    }
}

// Get clubs
$clubs = $db->query("SELECT id, name, code FROM clubs ORDER BY name");

$page_title = 'Tambah Staff Baru';
include '../includes/header.php';
?>

<div class="p-8">
    
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">➕ Tambah Staff Baru</h1>
                <p class="text-gray-600 mt-1">Tambahkan staff atau pelatih baru</p>
            </div>
            <a href="index.php" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition">
                ← Kembali
            </a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg p-8">
        
        <div class="grid md:grid-cols-2 gap-6">
            
            <!-- Name -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap *</label>
                <input 
                    type="text" 
                    name="name" 
                    value="<?php echo $_POST['name'] ?? ''; ?>"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue"
                    placeholder="Contoh: Pep Guardiola"
                >
            </div>

            <!-- Role -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Role *</label>
                <input 
                    type="text" 
                    name="role" 
                    value="<?php echo $_POST['role'] ?? ''; ?>"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue"
                    placeholder="Contoh: Head Coach, Assistant Coach"
                >
            </div>

            <!-- Club -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Klub *</label>
                <select name="club_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue">
                    <option value="">Pilih Klub</option>
                    <?php while ($club = $clubs->fetch_assoc()): ?>
                        <option value="<?php echo $club['id']; ?>" <?php echo (isset($_POST['club_id']) && $_POST['club_id'] == $club['id']) ? 'selected' : ''; ?>>
                            <?php echo $club['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Nationality -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Kebangsaan</label>
                <input 
                    type="text" 
                    name="nationality" 
                    value="<?php echo $_POST['nationality'] ?? ''; ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue"
                    placeholder="Contoh: Spain"
                >
            </div>

            <!-- Birth Date -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
                <input 
                    type="date" 
                    name="birth_date" 
                    value="<?php echo $_POST['birth_date'] ?? ''; ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue"
                >
            </div>

            <!-- Join Date -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Bergabung</label>
                <input 
                    type="date" 
                    name="join_date" 
                    value="<?php echo $_POST['join_date'] ?? ''; ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue"
                >
            </div>

            <!-- Previous Club -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Klub Sebelumnya</label>
                <input 
                    type="text" 
                    name="previous_club" 
                    value="<?php echo $_POST['previous_club'] ?? ''; ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue"
                    placeholder="Contoh: Barcelona"
                >
            </div>

            <!-- Photo Upload -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Staff</label>
                <input 
                    type="file" 
                    name="photo" 
                    accept="image/*"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue"
                    onchange="previewImage(this)"
                >
                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, WEBP. Max 5MB</p>
                
                <!-- Image Preview -->
                <div id="imagePreview" class="mt-4 hidden">
                    <img id="preview" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border-2 border-gray-300">
                </div>
            </div>

            <!-- Achievements -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Prestasi</label>
                <textarea 
                    name="achievements" 
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue resize-none"
                    placeholder="Tuliskan prestasi atau penghargaan yang pernah diraih..."
                ><?php echo $_POST['achievements'] ?? ''; ?></textarea>
            </div>

            <!-- Is Active -->
            <div class="md:col-span-2">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1"
                        <?php echo (isset($_POST['is_active']) || !isset($_POST['name'])) ? 'checked' : ''; ?>
                        class="w-5 h-5 text-city-blue border-gray-300 rounded focus:ring-city-blue"
                    >
                    <span class="ml-3 text-sm font-semibold text-gray-700">Staff Aktif</span>
                </label>
            </div>

        </div>

        <!-- Submit Buttons -->
        <div class="flex gap-3 mt-8 pt-6 border-t border-gray-200">
            <button 
                type="submit"
                class="flex-1 py-3 bg-gradient-to-r from-city-blue to-united-red text-white font-bold rounded-lg hover:shadow-lg transition"
            >
                💾 Simpan Staff
            </button>
            <a 
                href="index.php"
                class="px-8 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition"
            >
                ❌ Batal
            </a>
        </div>

    </form>

</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.classList.remove('hidden');
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
