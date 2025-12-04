<?php
/**
 * Manchester Side - Admin Schedule Management
 */
require_once '../../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

$db = getDB();
$admin = getCurrentAdmin();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM matches WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        setFlashMessage('success', 'Jadwal berhasil dihapus');
    } else {
        setFlashMessage('error', 'Gagal menghapus jadwal');
    }
    redirect('index.php');
}

// Get filter
$filter = $_GET['filter'] ?? 'all';

// Build query
$where = "1=1";
if ($filter === 'upcoming') {
    $where .= " AND m.match_date > NOW()";
} elseif ($filter === 'finished') {
    $where .= " AND m.status = 'finished'";
} elseif ($filter === 'live') {
    $where .= " AND m.status = 'live'";
}

// Get matches
$query = "SELECT 
    m.*,
    h.name as home_team, h.code as home_code,
    a.name as away_team, a.code as away_code
FROM matches m
JOIN clubs h ON m.home_team_id = h.id
JOIN clubs a ON m.away_team_id = a.id
WHERE $where
ORDER BY m.match_date DESC";

$matches_result = $db->query($query);

// Get statistics
$stats = [];
$stats['total'] = $db->query("SELECT COUNT(*) as c FROM matches")->fetch_assoc()['c'];
$stats['upcoming'] = $db->query("SELECT COUNT(*) as c FROM matches WHERE match_date > NOW()")->fetch_assoc()['c'];
$stats['finished'] = $db->query("SELECT COUNT(*) as c FROM matches WHERE status = 'finished'")->fetch_assoc()['c'];
$stats['live'] = $db->query("SELECT COUNT(*) as c FROM matches WHERE status = 'live'")->fetch_assoc()['c'];

$flash = getFlashMessage();
$page_title = "Kelola Jadwal Pertandingan";
include '../includes/header.php';
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📅 Kelola Jadwal Pertandingan</h1>
            <p class="text-gray-600 mt-1">Manage jadwal pertandingan Manchester Derby</p>
        </div>
        <a href="create.php" class="px-6 py-3 bg-gradient-to-r from-city-blue to-united-red text-white font-bold rounded-lg hover:shadow-lg transition">
            ➕ Tambah Jadwal
        </a>
    </div>

    <?php if ($flash): ?>
        <div class="mb-6 bg-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-50 border border-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-200 text-<?php echo $flash['type'] === 'success' ? 'green' : 'red'; ?>-800 px-4 py-3 rounded-lg">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="grid md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-gray-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Jadwal</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total']; ?></p>
                </div>
                <div class="text-4xl">📅</div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Akan Datang</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo $stats['upcoming']; ?></p>
                </div>
                <div class="text-4xl">⏰</div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Live</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo $stats['live']; ?></p>
                </div>
                <div class="text-4xl">🔴</div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Selesai</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo $stats['finished']; ?></p>
                </div>
                <div class="text-4xl">✅</div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center gap-3">
            <span class="font-semibold text-gray-700">Filter:</span>
            <a href="?filter=all" class="px-4 py-2 <?php echo $filter === 'all' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> rounded-lg font-semibold transition text-sm">
                Semua
            </a>
            <a href="?filter=upcoming" class="px-4 py-2 <?php echo $filter === 'upcoming' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> rounded-lg font-semibold transition text-sm">
                Akan Datang
            </a>
            <a href="?filter=live" class="px-4 py-2 <?php echo $filter === 'live' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> rounded-lg font-semibold transition text-sm">
                Live
            </a>
            <a href="?filter=finished" class="px-4 py-2 <?php echo $filter === 'finished' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> rounded-lg font-semibold transition text-sm">
                Selesai
            </a>
        </div>
    </div>

    <!-- Matches List -->
    <?php if ($matches_result->num_rows > 0): ?>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pertandingan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kompetisi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skor</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while ($match = $matches_result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <span class="font-semibold"><?php echo $match['home_team']; ?></span>
                                    <span class="text-gray-400">vs</span>
                                    <span class="font-semibold"><?php echo $match['away_team']; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs font-semibold">
                                    <?php echo $match['competition']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo formatDateIndo($match['match_date']); ?>
                                <br>
                                <span class="text-xs text-gray-500"><?php echo date('H:i', strtotime($match['match_date'])); ?> WIB</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $status_colors = [
                                    'scheduled' => 'bg-yellow-100 text-yellow-800',
                                    'live' => 'bg-red-100 text-red-800',
                                    'finished' => 'bg-green-100 text-green-800',
                                    'postponed' => 'bg-gray-100 text-gray-800'
                                ];
                                $status_labels = [
                                    'scheduled' => 'Terjadwal',
                                    'live' => 'Live',
                                    'finished' => 'Selesai',
                                    'postponed' => 'Ditunda'
                                ];
                                ?>
                                <span class="px-2 py-1 <?php echo $status_colors[$match['status']]; ?> rounded text-xs font-semibold">
                                    <?php echo $status_labels[$match['status']]; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($match['status'] === 'finished' || $match['status'] === 'live'): ?>
                                    <span class="font-bold text-lg">
                                        <?php echo $match['home_score']; ?> - <?php echo $match['away_score']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="edit.php?id=<?php echo $match['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <a href="?delete=<?php echo $match['id']; ?>" onclick="return confirm('Yakin ingin menghapus jadwal ini?')" class="text-red-600 hover:text-red-900">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <div class="text-6xl mb-4">📅</div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Jadwal</h3>
            <p class="text-gray-600 mb-6">Mulai tambahkan jadwal pertandingan</p>
            <a href="create.php" class="inline-block px-6 py-3 bg-gradient-to-r from-city-blue to-united-red text-white font-bold rounded-lg hover:shadow-lg transition">
                ➕ Tambah Jadwal Pertama
            </a>
        </div>
    <?php endif; ?>

</main>

<?php include '../includes/footer.php'; ?>
