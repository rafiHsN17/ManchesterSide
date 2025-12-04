<?php
/**
 * Manchester Side - Admin Staff Management
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
    $stmt = $db->prepare("DELETE FROM staff WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        setFlashMessage('success', 'Staff berhasil dihapus');
    } else {
        setFlashMessage('error', 'Gagal menghapus staff');
    }
    redirect('index.php');
}

// Handle toggle active status
if (isset($_GET['toggle_active'])) {
    $id = (int)$_GET['toggle_active'];
    $stmt = $db->prepare("UPDATE staff SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        setFlashMessage('success', 'Status staff berhasil diubah');
    }
    redirect('index.php');
}

// Get filters
$club_filter = $_GET['club'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT s.*, c.name as club_name, c.code as club_code 
          FROM staff s 
          JOIN clubs c ON s.club_id = c.id 
          WHERE 1=1";
$params = [];
$types = "";

if ($club_filter === 'city') {
    $query .= " AND c.code = 'CITY'";
} elseif ($club_filter === 'united') {
    $query .= " AND c.code = 'UNITED'";
}

if ($status_filter === 'active') {
    $query .= " AND s.is_active = 1";
} elseif ($status_filter === 'inactive') {
    $query .= " AND s.is_active = 0";
}

if (!empty($search)) {
    $query .= " AND (s.name LIKE ? OR s.role LIKE ? OR s.nationality LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

$query .= " ORDER BY s.created_at DESC";

$stmt = $db->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$staff_result = $stmt->get_result();

// Get statistics
$stats = [];
$stats['total_staff'] = $db->query("SELECT COUNT(*) as c FROM staff")->fetch_assoc()['c'];
$stats['active_staff'] = $db->query("SELECT COUNT(*) as c FROM staff WHERE is_active = 1")->fetch_assoc()['c'];
$stats['city_staff'] = $db->query("SELECT COUNT(*) as c FROM staff WHERE club_id = 1")->fetch_assoc()['c'];
$stats['united_staff'] = $db->query("SELECT COUNT(*) as c FROM staff WHERE club_id = 2")->fetch_assoc()['c'];

$flash = getFlashMessage();
$page_title = 'Manage Staff';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin Panel</title>
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
                <a href="../article/index.php" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition">
                    <span class="text-xl">📰</span>
                    <span>Berita</span>
                </a>
                <a href="../players/index.php" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition">
                    <span class="text-xl">👥</span>
                    <span>Pemain</span>
                </a>
                <a href="index.php" class="flex items-center space-x-3 px-4 py-3 bg-city-blue rounded-lg text-white font-semibold">
                    <span class="text-xl">🎯</span>
                    <span>Staff</span>
                </a>
                <a href="../schedule/index.php" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition">
                    <span class="text-xl">📅</span>
                    <span>Jadwal</span>
                </a>
                <a href="../users/index.php" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition">
                    <span class="text-xl">👤</span>
                    <span>Users</span>
                </a>
                <a href="../settings.php" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-800 rounded-lg transition">
                    <span class="text-xl">⚙️</span>
                    <span>Settings</span>
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
                        <h1 class="text-3xl font-bold text-gray-900">Manage Staff</h1>
                        <p class="text-gray-600 mt-1">Kelola staff dan pelatih Manchester Side</p>
                    </div>
                    <a href="create.php" class="px-6 py-3 bg-gradient-to-r from-city-blue to-united-red text-white font-bold rounded-lg hover:shadow-lg transition">
                        ➕ Tambah Staff
                    </a>
                </div>
            </header>

            <div class="p-6">

                <?php if ($flash): ?>
                    <div class="mb-6 bg-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-50 border border-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-200 text-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-800 px-4 py-3 rounded-lg">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="grid md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-3xl">🎯</span>
                            <span class="text-3xl font-bold text-gray-900"><?php echo $stats['total_staff']; ?></span>
                        </div>
                        <p class="text-gray-600 font-semibold">Total Staff</p>
                    </div>

                    <div class="bg-gradient-to-br from-green-500 to-green-700 text-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-3xl">✅</span>
                            <span class="text-3xl font-bold"><?php echo $stats['active_staff']; ?></span>
                        </div>
                        <p class="font-semibold">Active Staff</p>
                    </div>

                    <div class="bg-gradient-to-br from-city-blue to-city-navy text-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-3xl">🔵</span>
                            <span class="text-3xl font-bold"><?php echo $stats['city_staff']; ?></span>
                        </div>
                        <p class="font-semibold">City Staff</p>
                    </div>

                    <div class="bg-gradient-to-br from-united-red to-red-900 text-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-3xl">🔴</span>
                            <span class="text-3xl font-bold"><?php echo $stats['united_staff']; ?></span>
                        </div>
                        <p class="font-semibold">United Staff</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <form method="GET" action="" class="grid md:grid-cols-4 gap-4">
                        
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">🔍 Cari Staff</label>
                            <input 
                                type="text" 
                                name="search" 
                                value="<?php echo htmlspecialchars($search); ?>"
                                placeholder="Nama, role, atau nationality..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent"
                            >
                        </div>

                        <!-- Club Filter -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">⚽ Klub</label>
                            <select name="club" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
                                <option value="all" <?php echo $club_filter === 'all' ? 'selected' : ''; ?>>Semua</option>
                                <option value="city" <?php echo $club_filter === 'city' ? 'selected' : ''; ?>>🔵 Man City</option>
                                <option value="united" <?php echo $club_filter === 'united' ? 'selected' : ''; ?>>🔴 Man United</option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">📊 Status</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Semua</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>✅ Active</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>❌ Inactive</option>
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

                <!-- Staff Table -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Nama</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Role</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Klub</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Nationality</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Join Date</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if ($staff_result->num_rows > 0): ?>
                                <?php while ($staff = $staff_result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-gradient-to-r from-<?php echo $staff['club_code'] === 'CITY' ? 'city-blue' : 'united-red'; ?> to-gray-900 rounded-full flex items-center justify-center text-white font-bold">
                                                    <?php echo strtoupper(substr($staff['name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-900"><?php echo $staff['name']; ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-bold">
                                                <?php echo $staff['role']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 bg-<?php echo $staff['club_code'] === 'CITY' ? 'city-blue' : 'united-red'; ?> text-white rounded-full text-xs font-bold">
                                                <?php echo $staff['club_code'] === 'CITY' ? '🔵 CITY' : '🔴 UNITED'; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <?php echo $staff['nationality']; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <?php echo $staff['join_date'] ? formatDateIndo($staff['join_date']) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if ($staff['is_active']): ?>
                                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">
                                                    ✅ Active
                                                </span>
                                            <?php else: ?>
                                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">
                                                    ❌ Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end space-x-2">
                                                <a href="edit.php?id=<?php echo $staff['id']; ?>" class="px-3 py-1 bg-blue-100 text-blue-800 rounded-lg text-xs font-semibold hover:bg-blue-200 transition" title="Edit">
                                                    ✏️
                                                </a>
                                                <a href="?toggle_active=<?php echo $staff['id']; ?>" onclick="return confirm('Ubah status staff?')" class="px-3 py-1 bg-purple-100 text-purple-800 rounded-lg text-xs font-semibold hover:bg-purple-200 transition" title="Toggle Status">
                                                    <?php echo $staff['is_active'] ? '🔴' : '✅'; ?>
                                                </a>
                                                <a href="?delete=<?php echo $staff['id']; ?>" onclick="return confirm('Yakin ingin menghapus staff ini?')" class="px-3 py-1 bg-red-100 text-red-800 rounded-lg text-xs font-semibold hover:bg-red-200 transition" title="Delete">
                                                    🗑️
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <div class="text-6xl mb-4">🎯</div>
                                        <p class="text-lg font-semibold">Tidak ada staff ditemukan</p>
                                        <p class="text-sm mt-2">Coba ubah filter pencarian atau <a href="create.php" class="text-city-blue font-semibold hover:underline">tambah staff baru</a></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </main>

    </div>

</body>
</html>
