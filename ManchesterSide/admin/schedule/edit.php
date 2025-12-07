<?php
/**
 * Manchester Side - Admin Edit Schedule
 */
require_once '../../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../login.php');
}

$db = getDB();
$admin = getCurrentAdmin();
$errors = [];

// Get match ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    redirect('index.php');
}

// Get match data
$stmt = $db->prepare("SELECT * FROM matches WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$match = $stmt->get_result()->fetch_assoc();

if (!$match) {
    setFlashMessage('error', 'Jadwal tidak ditemukan');
    redirect('index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $home_team_id = (int)$_POST['home_team_id'];
    $away_team_id = (int)$_POST['away_team_id'];
    $competition = sanitize($_POST['competition']);
    $match_date = $_POST['match_date'];
    $venue = sanitize($_POST['venue']);
    $status = $_POST['status'];
    $home_score = isset($_POST['home_score']) ? (int)$_POST['home_score'] : null;
    $away_score = isset($_POST['away_score']) ? (int)$_POST['away_score'] : null;
    
    // Validation
    if (empty($home_team_id) || empty($away_team_id)) {
        $errors[] = 'Tim harus dipilih';
    }
    
    if ($home_team_id === $away_team_id) {
        $errors[] = 'Tim home dan away tidak boleh sama';
    }
    
    if (empty($competition)) {
        $errors[] = 'Kompetisi wajib diisi';
    }
    
    if (empty($match_date)) {
        $errors[] = 'Tanggal pertandingan wajib diisi';
    }
    
    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE matches SET home_team_id = ?, away_team_id = ?, competition = ?, match_date = ?, venue = ?, status = ?, home_score = ?, away_score = ? WHERE id = ?");
        $stmt->bind_param("iisssssii", $home_team_id, $away_team_id, $competition, $match_date, $venue, $status, $home_score, $away_score, $id);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Jadwal berhasil diupdate');
            redirect('index.php');
        } else {
            $errors[] = 'Gagal mengupdate jadwal';
        }
    }
}

// Get clubs
$clubs = $db->query("SELECT * FROM clubs ORDER BY name");

$page_title = "Edit Jadwal Pertandingan";
include '../includes/header.php';
?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <div class="mb-6">
        <a href="index.php" class="text-blue-600 hover:text-blue-800 font-semibold">← Kembali ke Daftar Jadwal</a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">✏️ Edit Jadwal Pertandingan</h1>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            
            <!-- Home Team -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Tim Home</label>
                <select name="home_team_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
                    <option value="">Pilih Tim Home</option>
                    <?php while ($club = $clubs->fetch_assoc()): ?>
                        <option value="<?php echo $club['id']; ?>" <?php echo ($match['home_team_id'] == $club['id']) ? 'selected' : ''; ?>>
                            <?php echo $club['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Away Team -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Tim Away</label>
                <select name="away_team_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
                    <option value="">Pilih Tim Away</option>
                    <?php 
                    $clubs->data_seek(0);
                    while ($club = $clubs->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $club['id']; ?>" <?php echo ($match['away_team_id'] == $club['id']) ? 'selected' : ''; ?>>
                            <?php echo $club['name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Competition -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Kompetisi</label>
                <select name="competition" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
                    <option value="">Pilih Kompetisi</option>
                    <option value="Premier League" <?php echo ($match['competition'] === 'Premier League') ? 'selected' : ''; ?>>Premier League</option>
                    <option value="FA Cup" <?php echo ($match['competition'] === 'FA Cup') ? 'selected' : ''; ?>>FA Cup</option>
                    <option value="Carabao Cup" <?php echo ($match['competition'] === 'Carabao Cup') ? 'selected' : ''; ?>>Carabao Cup</option>
                    <option value="Champions League" <?php echo ($match['competition'] === 'Champions League') ? 'selected' : ''; ?>>Champions League</option>
                    <option value="Europa League" <?php echo ($match['competition'] === 'Europa League') ? 'selected' : ''; ?>>Europa League</option>
                    <option value="Community Shield" <?php echo ($match['competition'] === 'Community Shield') ? 'selected' : ''; ?>>Community Shield</option>
                </select>
            </div>

            <!-- Match Date -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal & Waktu Pertandingan</label>
                <input type="datetime-local" name="match_date" required value="<?php echo date('Y-m-d\TH:i', strtotime($match['match_date'])); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
            </div>

            <!-- Venue -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Venue (Opsional)</label>
                <input type="text" name="venue" value="<?php echo $match['venue']; ?>" placeholder="Contoh: Old Trafford, Etihad Stadium" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Status</label>
                <select name="status" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
                    <option value="scheduled" <?php echo ($match['status'] === 'scheduled') ? 'selected' : ''; ?>>Terjadwal</option>
                    <option value="live" <?php echo ($match['status'] === 'live') ? 'selected' : ''; ?>>Live</option>
                    <option value="finished" <?php echo ($match['status'] === 'finished') ? 'selected' : ''; ?>>Selesai</option>
                    <option value="postponed" <?php echo ($match['status'] === 'postponed') ? 'selected' : ''; ?>>Ditunda</option>
                </select>
            </div>

            <!-- Score (only if finished or live) -->
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Skor Home</label>
                    <input type="number" name="home_score" min="0" value="<?php echo $match['home_score']; ?>" placeholder="0" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika belum dimainkan</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Skor Away</label>
                    <input type="number" name="away_score" min="0" value="<?php echo $match['away_score']; ?>" placeholder="0" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika belum dimainkan</p>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex gap-3">
                <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-city-blue to-united-red text-white font-bold rounded-lg hover:shadow-lg transition">
                    💾 Update Jadwal
                </button>
                <a href="index.php" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
            </div>

        </form>
    </div>

</main>

<?php include '../includes/footer.php'; ?>
