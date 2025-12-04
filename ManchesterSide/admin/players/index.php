<?php
/**
 * Manchester Side - Admin Players Management
 */
require_once '../../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

$db = getDB();
$admin = getCurrentAdmin();

// Handle delete action
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM players WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        setFlashMessage('success', 'Pemain berhasil dihapus');
    } else {
        setFlashMessage('error', 'Gagal menghapus pemain');
    }
    redirect('index.php');
}

// Get filters
$club_filter = $_GET['club'] ?? 'all';
$position_filter = $_GET['position'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT 
    p.*, c.name as club_name, c.code as club_code
FROM players p
JOIN clubs c ON p.club_id = c.id
WHERE 1=1";

$params = [];
$types = "";

if ($club_filter === 'city') {
    $query .= " AND c.code = 'CITY'";
} elseif ($club_filter === 'united') {
    $query .= " AND c.code = 'UNITED'";
}

if ($position_filter !== 'all') {
    $query .= " AND p.position = ?";
    $params[] = $position_filter;
    $types .= "s";
}

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.nationality LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$query .= " ORDER BY c.id, p.position, p.jersey_number";

$stmt = $db->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$players_result = $stmt->get_result();

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pemain - Admin Panel</title>
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
                <a href="../staff/" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition">
                    <span class="text-xl">🎯</span>
                    <span>Staff</span>
                </a>
                <a href="../schedule/" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition">
                    <span class="text-xl">📅</span>
                    <span>Jadwal</span>
                </a>
                <a href="../users/" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition">
                    <span class="text-xl">👤</span>
                    <span>Users</span>
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
                        <h1 class="text-3xl font-bold text-gray-900">Manage Pemain</h1>
                        <p class="text-gray-600 mt-1">Kelola data pemain Manchester Side</p>
                    </div>
                    <a href="create.php" class="px-6 py-3 bg-gradient-to-r from-city-blue to-united-red text-white font-bold rounded-lg hover:shadow-lg transition">
                        ➕ Tambah Pemain
                    </a>
                </div>
            </header>

            <div class="p-6">

                <?php if ($flash): ?>
                    <div class="mb-6 bg-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-50 border border-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-200 text-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-800 px-4 py-3 rounded-lg">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <form method="GET" action="" class="grid md:grid-cols-4 gap-4">
                        
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">🔍 Cari Pemain</label>
                            <input 
                                type="text" 
                                name="search" 
                                value="<?php echo htmlspecialchars($search); ?>"
                                placeholder="Nama atau kebangsaan..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                            >
                        </div>

                        <!-- Club Filter -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">⚽ Klub</label>
                            <select name="club" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
                                <option value="all" <?php echo $club_filter === 'all' ? 'selected' : ''; ?>>Semua Klub</option>
                                <option value="city" <?php echo $club_filter === 'city' ? 'selected' : ''; ?>>🔵 Man City</option>
                                <option value="united" <?php echo $club_filter === 'united' ? 'selected' : ''; ?>>🔴 Man United</option>
                            </select>
                        </div>

                        <!-- Position Filter -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">📍 Posisi</label>
                            <select name="position" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
                                <option value="all" <?php echo $position_filter === 'all' ? 'selected' : ''; ?>>Semua Posisi</option>
                                <option value="Goalkeeper" <?php echo $position_filter === 'Goalkeeper' ? 'selected' : ''; ?>>🧤 Goalkeeper</option>
                                <option value="Defender" <?php echo $position_filter === 'Defender' ? 'selected' : ''; ?>>🛡️ Defender</option>
                                <option value="Midfielder" <?php echo $position_filter === 'Midfielder' ? 'selected' : ''; ?>>⚙️ Midfielder</option>
                                <option value="Forward" <?php echo $position_filter === 'Forward' ? 'selected' : ''; ?>>⚽ Forward</option>
                            </select>
                        </div>

                        <div class="md:col-span-4 flex gap-2">
                            <button type="submit" class="px-6 py-2 bg-city-blue text-white font-semibold rounded-lg hover:bg-city-navy transition">
                                Filter
                            </button>
                            <a href="index.php" class="px-6 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                                Reset
                            </a>
                        </div>

                    </form>
                </div>

                <!-- Players Grid -->
                <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php if ($players_result->num_rows > 0): ?>
                        <?php while ($player = $players_result->fetch_assoc()): ?>
                            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition">
                                <div class="h-32 bg-gradient-to-br from-<?php echo $player['club_code'] === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $player['club_code'] === 'CITY' ? 'city-navy' : 'red'; ?>-900 flex items-center justify-center">
                                    <div class="text-6xl font-black text-white">
                                        <?php echo $player['jersey_number']; ?>
                                    </div>
                                </div>
                                
                                <div class="p-5">
                                    <div class="mb-3">
                                        <span class="px-3 py-1 bg-<?php echo $player['club_code'] === 'CITY' ? 'city-blue' : 'united-red'; ?> text-white rounded-full text-xs font-bold">
                                            <?php echo $player['club_code']; ?>
                                        </span>
                                    </div>
                                    
                                    <h4 class="font-bold text-gray-900 text-lg mb-1"><?php echo $player['name']; ?></h4>
                                    <p class="text-sm text-gray-600 mb-3"><?php echo $player['position']; ?></p>
                                    
                                    <div class="text-xs text-gray-600 space-y-1 mb-4">
                                        <p>🌍 <?php echo $player['nationality']; ?></p>
                                        <?php if ($player['height']): ?>
                                            <p>📏 <?php echo $player['height']; ?> cm</p>
                                        <?php endif; ?>
                                        <?php if ($player['birth_date']): ?>
                                            <p>🎂 <?php echo date('Y') - date('Y', strtotime($player['birth_date'])); ?> tahun</p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex gap-2">
                                        <a href="edit.php?id=<?php echo $player['id']; ?>" class="flex-1 px-3 py-2 bg-yellow-100 text-yellow-800 rounded-lg text-xs font-semibold hover:bg-yellow-200 transition text-center">
                                            ✏️ Edit
                                        </a>
                                        <a href="?delete=<?php echo $player['id']; ?>" onclick="return confirm('Yakin ingin menghapus pemain ini?')" class="flex-1 px-3 py-2 bg-red-100 text-red-800 rounded-lg text-xs font-semibold hover:bg-red-200 transition text-center">
                                            🗑️ Hapus
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-20">
                            <div class="text-6xl mb-4">👥</div>
                            <p class="text-lg font-semibold text-gray-900 mb-2">Tidak ada pemain ditemukan</p>
                            <p class="text-sm text-gray-600 mb-4">Coba ubah filter atau tambah pemain baru</p>
                            <a href="create.php" class="inline-block px-6 py-2 bg-city-blue text-white font-semibold rounded-lg hover:bg-city-navy transition">
                                ➕ Tambah Pemain
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </main>

    </div>

</body>
</html>