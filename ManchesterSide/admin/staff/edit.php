<?php
/**
 * Manchester Side - Edit Staff
 */
require_once '../../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

$db = getDB();
$admin = getCurrentAdmin();
$errors = [];

// Get staff ID
$staff_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($staff_id === 0) {
    setFlashMessage('error', 'ID staff tidak valid');
    redirect('index.php');
}

// Handle delete action
if (isset($_GET['delete']) && $_GET['delete'] == $staff_id) {
    $delete_stmt = $db->prepare("DELETE FROM staff WHERE id = ?");
    $delete_stmt->bind_param("i", $staff_id);
    
    if ($delete_stmt->execute()) {
        setFlashMessage('success', 'Staff berhasil dihapus');
        redirect('index.php');
    } else {
        setFlashMessage('error', 'Gagal menghapus staff');
    }
}

// Get staff data
$stmt = $db->prepare("SELECT s.*, c.name as club_name, c.code as club_code FROM staff s JOIN clubs c ON s.club_id = c.id WHERE s.id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();

if (!$staff) {
    setFlashMessage('error', 'Staff tidak ditemukan');
    redirect('index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_staff'])) {
    $name = sanitize($_POST['name'] ?? '');
    $club_id = (int)($_POST['club_id'] ?? 0);
    $role = sanitize($_POST['role'] ?? '');
    $nationality = sanitize($_POST['nationality'] ?? '');
    $join_date = $_POST['join_date'] ?? null;
    $biography = sanitize($_POST['biography'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Nama staff wajib diisi';
    }
    
    if ($club_id === 0) {
        $errors[] = 'Klub wajib dipilih';
    }
    
    if (empty($role)) {
        $errors[] = 'Role wajib diisi';
    }
    
    if (empty($nationality)) {
        $errors[] = 'Kebangsaan wajib diisi';
    }
    
    if (empty($errors)) {
        $update_stmt = $db->prepare("UPDATE staff SET name = ?, club_id = ?, role = ?, nationality = ?, join_date = ?, biography = ?, is_active = ? WHERE id = ?");
        
        $update_stmt->bind_param("sissssii", $name, $club_id, $role, $nationality, $join_date, $biography, $is_active, $staff_id);
        
        if ($update_stmt->execute()) {
            setFlashMessage('success', 'Data staff berhasil diperbarui!');
            redirect('edit.php?id=' . $staff_id);
        } else {
            $errors[] = 'Gagal memperbarui data staff: ' . $db->error;
        }
    }
    
    // Reload staff data
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT s.*, c.name as club_name, c.code as club_code FROM staff s JOIN clubs c ON s.club_id = c.id WHERE s.id = ?");
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $staff = $stmt->get_result()->fetch_assoc();
    }
}

// Get clubs
$clubs = $db->query("SELECT id, name, code FROM clubs ORDER BY name");

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff - Admin Panel</title>
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
                <a href="../articles/" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition">
                    <span class="text-xl">📰</span>
                    <span>Berita</span>
                </a>
                <a href="../players/" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition">
                    <span class="text-xl">👥</span>
                    <span>Pemain</span>
                </a>
                <a href="index.php" class="flex items-center space-x-3 px-4 py-3 bg-city-blue rounded-lg text-white font-semibold">
                    <span class="text-xl">🎯</span>
                    <span>Staff</span>
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
                        <h1 class="text-3xl font-bold text-gray-900">Edit Staff</h1>
                        <p class="text-gray-600 mt-1">Perbarui data: <span class="font-semibold"><?php echo $staff['name']; ?></span></p>
                    </div>
                    <a href="index.php" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition">
                        ← Kembali
                    </a>
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

                <!-- Staff Info Card -->
                <div class="bg-gradient-to-r from-<?php echo $staff['club_code'] === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $staff['club_code'] === 'CITY' ? 'city-navy' : 'red'; ?>-900 text-white rounded-xl shadow-xl p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-6">
                            <div class="text-6xl">
                                <?php 
                                echo match(true) {
                                    stripos($staff['role'], 'manager') !== false => '👔',
                                    stripos($staff['role'], 'assistant') !== false => '📋',
                                    stripos($staff['role'], 'coach') !== false => '🎯',
                                    default => '👤'
                                };
                                ?>
                            </div>
                            <div>
                                <h2 class="text-3xl font-bold mb-1"><?php echo $staff['name']; ?></h2>
                                <p class="text-lg"><?php echo $staff['role']; ?> • <?php echo $staff['club_name']; ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm mb-1">ID Staff</p>
                            <p class="text-3xl font-bold">#<?php echo $staff['id']; ?></p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="" class="space-y-6">
                    
                    <div class="grid lg:grid-cols-3 gap-6">
                        
                        <!-- Main Info -->
                        <div class="lg:col-span-2 space-y-6">
                            
                            <!-- Basic Info Card -->
                            <div class="bg-white rounded-xl shadow-lg p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                                    <span class="text-2xl mr-2">👤</span>
                                    Informasi Dasar
                                </h3>
                                
                                <div class="grid md:grid-cols-2 gap-4">
                                    <!-- Name -->
                                    <div class="md:col-span-2">
                                        <label for="name" class="block text-sm font-bold text-gray-700 mb-2">
                                            Nama Lengkap <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="text" 
                                            id="name" 
                                            name="name" 
                                            value="<?php echo htmlspecialchars($staff['name']); ?>"
                                            required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                                        >
                                    </div>

                                    <!-- Role -->
                                    <div>
                                        <label for="role" class="block text-sm font-bold text-gray-700 mb-2">
                                            Role/Jabatan <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="text" 
                                            id="role" 
                                            name="role" 
                                            value="<?php echo htmlspecialchars($staff['role']); ?>"
                                            required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                                        >
                                    </div>

                                    <!-- Nationality -->
                                    <div>
                                        <label for="nationality" class="block text-sm font-bold text-gray-700 mb-2">
                                            Kebangsaan <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="text" 
                                            id="nationality" 
                                            name="nationality" 
                                            value="<?php echo htmlspecialchars($staff['nationality']); ?>"
                                            required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                                        >
                                    </div>

                                    <!-- Join Date -->
                                    <div class="md:col-span-2">
                                        <label for="join_date" class="block text-sm font-bold text-gray-700 mb-2">
                                            Tanggal Bergabung
                                        </label>
                                        <input 
                                            type="date" 
                                            id="join_date" 
                                            name="join_date" 
                                            value="<?php echo $staff['join_date']; ?>"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Biography -->
                            <div class="bg-white rounded-xl shadow-lg p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                                    <span class="text-2xl mr-2">📝</span>
                                    Biografi
                                </h3>
                                
                                <textarea 
                                    id="biography" 
                                    name="biography" 
                                    rows="6"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent resize-none"
                                ><?php echo htmlspecialchars($staff['biography']); ?></textarea>
                            </div>

                        </div>

                        <!-- Sidebar Settings -->
                        <div class="space-y-6">
                            
                            <!-- Club Selection -->
                            <div class="bg-white rounded-xl shadow-lg p-6">
                                <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                                    <span class="text-xl mr-2">⚽</span>
                                    Klub <span class="text-red-500 ml-1">*</span>
                                </h3>
                                
                                <select 
                                    name="club_id" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                                >
                                    <?php while ($club = $clubs->fetch_assoc()): ?>
                                        <option value="<?php echo $club['id']; ?>" <?php echo ($staff['club_id'] == $club['id']) ? 'selected' : ''; ?>>
                                            <?php echo $club['code'] === 'CITY' ? '🔵' : '🔴'; ?> <?php echo $club['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="bg-white rounded-xl shadow-lg p-6">
                                <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                                    <span class="text-xl mr-2">✅</span>
                                    Status
                                </h3>
                                
                                <label class="flex items-center space-x-3 cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        name="is_active" 
                                        value="1"
                                        <?php echo $staff['is_active'] ? 'checked' : ''; ?>
                                        class="w-5 h-5 text-city-blue border-gray-300 rounded focus:ring-city-blue"
                                    >
                                    <div>
                                        <span class="font-semibold text-gray-900">Staff Aktif</span>
                                        <p class="text-xs text-gray-500">Tampilkan di website</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="bg-white rounded-xl shadow-lg p-6 space-y-3">
                                <button 
                                    type="submit"
                                    name="update_staff"
                                    class="w-full py-3 bg-gradient-to-r from-city-blue to-united-red text-white font-bold rounded-lg hover:shadow-lg transition"
                                >
                                    💾 Update Staff
                                </button>
                                
                                <a 
                                    href="index.php"
                                    class="block w-full py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition text-center"
                                >
                                    ❌ Batal
                                </a>

                                <a 
                                    href="?delete=<?php echo $staff['id']; ?>"
                                    onclick="return confirm('⚠️ PERHATIAN!\n\nAnda yakin ingin menghapus staff ini?\n\n- Nama: <?php echo addslashes($staff['name']); ?>\n- Role: <?php echo addslashes($staff['role']); ?>\n\nTindakan ini tidak dapat dibatalkan!')"
                                    class="block w-full py-3 bg-red-100 text-red-700 font-bold rounded-lg hover:bg-red-200 transition text-center"
                                >
                                    🗑️ Hapus Staff
                                </a>
                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </main>

    </div>

</body>
</html>