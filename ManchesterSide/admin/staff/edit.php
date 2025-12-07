<?php
/**
 * Manchester Side - Edit Staff with Photo Upload
 */
require_once '../../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

$db = getDB();
$admin = getCurrentAdmin();
$errors = [];

// Get staff ID
$staff_id = (int)($_GET['id'] ?? 0);

// Get staff data
$stmt = $db->prepare("SELECT * FROM staff WHERE id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();

if (!$staff) {
    setFlashMessage('error', 'Staff tidak ditemukan');
    redirect('index.php');
}

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
    $photo_url = $staff['photo_url'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        // Delete old photo
        if ($photo_url && file_exists('../../' . $photo_url)) {
            unlink('../../' . $photo_url);
        }
        
        $upload_result = uploadImage($_FILES['photo'], 'staff');
        if ($upload_result['success']) {
            $photo_url = 'uploads/staff/' . $upload_result['filename'];
        } else {
            $errors[] = $upload_result['message'];
        }
    }
    
    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE staff SET name = ?, role = ?, club_id = ?, nationality = ?, birth_date = ?, join_date = ?, previous_club = ?, achievements = ?, photo_url = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("ssissssssii", $name, $role, $club_id, $nationality, $birth_date, $join_date, $previous_club, $achievements, $photo_url, $is_active, $staff_id);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Staff berhasil diupdate!');
            redirect('index.php');
        } else {
            $errors[] = 'Gagal mengupdate staff';
        }
    }
}

// Get clubs
$clubs = $db->query("SELECT id, name, code FROM clubs ORDER BY name");

$flash = getFlashMessage();
$page_title = 'Edit Staff';
include '../includes/header.php';
?>

<div class="p-8">
    
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">✏️ Edit Staff</h1>
                <p class="text-gray-600 mt-1">Update data staff atau pelatih</p>
            </div>
            <a href="index.php" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition">
                ← Kembali
            </a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="mb-6 bg-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-50 border border-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-200 text-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-800 px-4 py-3 rounded-lg">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

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
                    value="<?php echo htmlspecialchars($staff['name']); ?>"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue"
                >
            </div>

            <!-- Role -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Role *</label>
                <input 
                    type="text" 
                    name="role" 
                    value="<?php echo htmlspecialchars($staff['role']); ?>"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue"
                >
            </div>

            <!-- Club -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Klub *</label>
                <select name="club_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue">
                    <option value="">Pilih Klub</option>
                    <?php while ($club = $clubs->fetch_assoc()): ?>
                        <option value="<?php echo $club['id']; ?>" <?php echo ($staff['club_id'] == $club['id']) ? 'selected' : ''; ?>>
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
                    value="<?php echo htmlspecialchars($staff['nationality']); ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue"
                >
            </div>

            <!-- Birth Date -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
                <input 
                    type="date" 
                    name="birth_date" 
                    value="<?php echo $staff['birth_date']; ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue"
                >
            </div>

            <!-- Join Date -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Bergabung</label>
                <input 
                    type="date" 
                    name="join_date" 
                    value="<?php echo $staff['join_date']; ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue"
                >
            </div>

            <!-- Previous Club -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Klub Sebelumnya</label>
                <input 
                    type="text" 
                    name="previous_club" 
                    value="<?php echo htmlspecialchars($staff['previous_club']); ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue"
                >
            </div>

            <!-- Current Photo -->
            <?php if ($staff['photo_url']): ?>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Saat Ini</label>
                    <img src="../../<?php echo $staff['photo_url']; ?>" alt="<?php echo $staff['name']; ?>" class="w-32 h-32 object-cover rounded-lg border-2 border-gray-300">
                </div>
            <?php endif; ?>

            <!-- Photo Upload -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Foto Baru</label>
                <input 
                    type="file" 
                    name="photo" 
                    accept="image/*"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue"
                    onchange="previewImage(this)"
                >
                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, WEBP. Max 5MB. Kosongkan jika tidak ingin mengubah foto.</p>
                
                <!-- Image Preview -->
                <div id="imagePreview" class="mt-4 hidden">
                    <p class="text-sm font-semibold text-gray-700 mb-2">Preview Foto Baru:</p>
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
                ><?php echo htmlspecialchars($staff['achievements']); ?></textarea>
            </div>

            <!-- Is Active -->
            <div class="md:col-span-2">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1"
                        <?php echo $staff['is_active'] ? 'checked' : ''; ?>
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
                💾 Update Staff
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
