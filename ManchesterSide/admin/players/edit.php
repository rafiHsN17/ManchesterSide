<?php
/**
 * Manchester Side - Edit Player
 */
require_once '../../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

$db = getDB();
$admin = getCurrentAdmin();
$errors = [];

// Get player ID
$player_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($player_id === 0) {
    setFlashMessage('error', 'ID pemain tidak valid');
    redirect('index.php');
}

// Handle delete action
if (isset($_GET['delete']) && $_GET['delete'] == $player_id) {
    $delete_stmt = $db->prepare("DELETE FROM players WHERE id = ?");
    $delete_stmt->bind_param("i", $player_id);
    
    if ($delete_stmt->execute()) {
        setFlashMessage('success', 'Pemain berhasil dihapus');
        redirect('index.php');
    } else {
        setFlashMessage('error', 'Gagal menghapus pemain');
    }
}

// Get player data
$stmt = $db->prepare("SELECT p.*, c.name as club_name, c.code as club_code FROM players p JOIN clubs c ON p.club_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$player = $stmt->get_result()->fetch_assoc();

if (!$player) {
    setFlashMessage('error', 'Pemain tidak ditemukan');
    redirect('index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_player'])) {
    $name = sanitize($_POST['name'] ?? '');
    $club_id = (int)($_POST['club_id'] ?? 0);
    $position = sanitize($_POST['position'] ?? '');
    $jersey_number = (int)($_POST['jersey_number'] ?? 0);
    $nationality = sanitize($_POST['nationality'] ?? '');
    $birth_date = $_POST['birth_date'] ?? null;
    $height = !empty($_POST['height']) ? (int)$_POST['height'] : null;
    $weight = !empty($_POST['weight']) ? (int)$_POST['weight'] : null;
    $biography = sanitize($_POST['biography'] ?? '');
    $joined_date = $_POST['joined_date'] ?? null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Nama pemain wajib diisi';
    }
    
    if ($club_id === 0) {
        $errors[] = 'Klub wajib dipilih';
    }
    
    if (empty($position)) {
        $errors[] = 'Posisi wajib dipilih';
    }
    
    if ($jersey_number === 0) {
        $errors[] = 'Nomor punggung wajib diisi';
    }
    
    if (empty($nationality)) {
        $errors[] = 'Kebangsaan wajib diisi';
    }
    
    // Check if jersey number already exists (excluding current player)
    if (empty($errors)) {
        $check_jersey = $db->prepare("SELECT id FROM players WHERE club_id = ? AND jersey_number = ? AND id != ?");
        $check_jersey->bind_param("iii", $club_id, $jersey_number, $player_id);
        $check_jersey->execute();
        if ($check_jersey->get_result()->num_rows > 0) {
            $errors[] = 'Nomor punggung sudah digunakan di klub ini';
        }
    }
    
    if (empty($errors)) {
        $update_stmt = $db->prepare("UPDATE players SET name = ?, club_id = ?, position = ?, jersey_number = ?, nationality = ?, birth_date = ?, height = ?, weight = ?, biography = ?, joined_date = ?, is_active = ? WHERE id = ?");
        
        $update_stmt->bind_param("sssississsii", $name, $club_id, $position, $jersey_number, $nationality, $birth_date, $height, $weight, $biography, $joined_date, $is_active, $player_id);
        
        if ($update_stmt->execute()) {
            setFlashMessage('success', 'Data pemain berhasil diperbarui!');
            redirect('edit.php?id=' . $player_id);
        } else {
            $errors[] = 'Gagal memperbarui data pemain: ' . $db->error;
        }
    }
    
    // Reload player data
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT p.*, c.name as club_name, c.code as club_code FROM players p JOIN clubs c ON p.club_id = c.id WHERE p.id = ?");
        $stmt->bind_param("i", $player_id);
        $stmt->execute();
        $player = $stmt->get_result()->fetch_assoc();
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
    <title>Edit Pemain - Admin Panel</title>
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
                <a href="index.php" class="flex items-center space-x-3 px-4 py-3 bg-city-blue rounded-lg text-white font-semibold">
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
                        <h1 class="text-3xl font-bold text-gray-900">Edit Pemain</h1>
                        <p class="text-gray-600 mt-1">Perbarui data: <span class="font-semibold"><?php echo $player['name']; ?></span></p>
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

                <!-- Player Info Card -->
                <div class="bg-gradient-to-r from-<?php echo $player['club_code'] === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $player['club_code'] === 'CITY' ? 'city-navy' : 'red'; ?>-900 text-white rounded-xl shadow-xl p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-6">
                            <div class="text-6xl font-black">
                                <?php echo $player['jersey_number']; ?>
                            </div>
                            <div>
                                <h2 class="text-3xl font-bold mb-1"><?php echo $player['name']; ?></h2>
                                <p class="text-lg"><?php echo $player['position']; ?> • <?php echo $player['club_name']; ?></p>
                                <p class="text-sm opacity-90 mt-1">🌍 <?php echo $player['nationality']; ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm mb-1">ID Pemain</p>
                            <p class="text-3xl font-bold">#<?php echo $player['id']; ?></p>
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
                                            value="<?php echo htmlspecialchars($player['name']); ?>"
                                            required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                                        >
                                    </div>

                                    <!-- Jersey Number -->
                                    <div>
                                        <label for="jersey_number" class="block text-sm font-bold text-gray-700 mb-2">
                                            Nomor Punggung <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="number" 
                                            id="jersey_number" 
                                            name="jersey_number" 
                                            value="<?php echo $player['jersey_number']; ?>"
                                            required
                                            min="1"
                                            max="99"
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
                                            value="<?php echo htmlspecialchars($player['nationality']); ?>"
                                            required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                                        >
                                    </div>

                                    <!-- Birth Date -->
                                    <div>
                                        <label for="birth_date" class="block text-sm font-bold text-gray-700 mb-2">
                                            Tanggal Lahir
                                        </label>
                                        <input 
                                            type="date" 
                                            id="birth_date" 
                                            name="birth_date" 
                                            value="<?php echo $player['birth_date']; ?>"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                                        >
                                    </div>

                                    <!-- Joined Date -->
                                    <div>
                                        <label for="joined_date" class="block text-sm font-bold text-gray-700 mb-2">
                                            Tanggal Bergabung
                                        </label>
                                        <input 
                                            type="date" 
                                            id="joined_date" 
                                            name="joined_date" 
                                            value="<?php echo $player['joined_date']; ?>"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Physical Stats -->
                            <div class="bg-white rounded-xl shadow-lg p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                                    <span class="text-2xl mr-2">📏</span>
                                    Data Fisik
                                </h3>
                                
                                <div class="grid md:grid-cols-2 gap-4">
                                    <!-- Height -->
                                    <div>
                                        <label for="height" class="block text-sm font-bold text-gray-700 mb-2">
                                            Tinggi Badan (cm)
                                        </label>
                                        <input 
                                            type="number" 
                                            id="height" 
                                            name="height" 
                                            value="<?php echo $player['height']; ?>"
                                            min="150"
                                            max="220"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                                        >
                                    </div>

                                    <!-- Weight -->
                                    <div>
                                        <label for="weight" class="block text-sm font-bold text-gray-700 mb-2">
                                            Berat Badan (kg)
                                        </label>
                                        <input 
                                            type="number" 
                                            id="weight" 
                                            name="weight" 
                                            value="<?php echo $player['weight']; ?>"
                                            min="50"
                                            max="120"
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
                                ><?php echo htmlspecialchars($player['biography']); ?></textarea>
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
                                        <option value="<?php echo $club['id']; ?>" <?php echo ($player['club_id'] == $club['id']) ? 'selected' : ''; ?>>
                                            <?php echo $club['code'] === 'CITY' ? '🔵' : '🔴'; ?> <?php echo $club['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Position -->
                            <div class="bg-white rounded-xl shadow-lg p-6">
                                <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                                    <span class="text-xl mr-2">📍</span>
                                    Posisi <span class="text-red-500 ml-1">*</span>
                                </h3>
                                
                                <select 
                                    name="position" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                                >
                                    <option value="Goalkeeper" <?php echo $player['position'] === 'Goalkeeper' ? 'selected' : ''; ?>>🧤 Goalkeeper</option>
                                    <option value="Defender" <?php echo $player['position'] === 'Defender' ? 'selected' : ''; ?>>🛡️ Defender</option>
                                    <option value="Midfielder" <?php echo $player['position'] === 'Midfielder' ? 'selected' : ''; ?>>⚙️ Midfielder</option>
                                    <option value="Forward" <?php echo $player['position'] === 'Forward' ? 'selected' : ''; ?>>⚽ Forward</option>
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
                                        <?php echo $player['is_active'] ? 'checked' : ''; ?>
                                        class="w-5 h-5 text-city-blue border-gray-300 rounded focus:ring-city-blue"
                                    >
                                    <div>
                                        <span class="font-semibold text-gray-900">Pemain Aktif</span>
                                        <p class="text-xs text-gray-500">Tampilkan di website</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="bg-white rounded-xl shadow-lg p-6 space-y-3">
                                <button 
                                    type="submit"
                                    name="update_player"
                                    class="w-full py-3 bg-gradient-to-r from-city-blue to-united-red text-white font-bold rounded-lg hover:shadow-lg transition"
                                >
                                    💾 Update Pemain
                                </button>
                                
                                <a 
                                    href="index.php"
                                    class="block w-full py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition text-center"
                                >
                                    ❌ Batal
                                </a>

                                <a 
                                    href="?delete=<?php echo $player['id']; ?>"
                                    onclick="return confirm('⚠️ PERHATIAN!\n\nAnda yakin ingin menghapus pemain ini?\n\n- Nama: <?php echo addslashes($player['name']); ?>\n- Klub: <?php echo addslashes($player['club_name']); ?>\n\nTindakan ini tidak dapat dibatalkan!')"
                                    class="block w-full py-3 bg-red-100 text-red-700 font-bold rounded-lg hover:bg-red-200 transition text-center"
                                >
                                    🗑️ Hapus Pemain
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