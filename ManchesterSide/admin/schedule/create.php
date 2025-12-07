<?php
/**
 * Manchester Side - Admin Create Schedule
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
    $home_team_id = (int)$_POST['home_team_id'];
    $away_team_id = (int)$_POST['away_team_id'];
    $competition = sanitize($_POST['competition']);
    $match_date = $_POST['match_date'];
    $venue = sanitize($_POST['venue']);
    $status = $_POST['status'];
    
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
        $stmt = $db->prepare("INSERT INTO matches (home_team_id, away_team_id, competition, match_date, venue, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $home_team_id, $away_team_id, $competition, $match_date, $venue, $status);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Jadwal berhasil ditambahkan');
            redirect('index.php');
        } else {
            $errors[] = 'Gagal menambahkan jadwal';
        }
    }
}

// Get clubs
$clubs = $db->query("SELECT * FROM clubs ORDER BY name");

$page_title = "Tambah Jadwal Pertandingan";
include '../includes/header.php';
?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <div class="mb-6">
        <a href="index.php" class="text-blue-600 hover:text-blue-800 font-semibold">← Kembali ke Daftar Jadwal</a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">📅 Tambah Jadwal Pertandingan</h1>

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
                        <option value="<?php echo $club['id']; ?>" <?php echo (isset($_POST['home_team_id']) && $_POST['home_team_id'] == $club['id']) ? 'selected' : ''; ?>>
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
                        <option value="<?php echo $club['id']; ?>" <?php echo (isset($_POST['away_team_id']) && $_POST['away_team_id'] == $club['id']) ? 'selected' : ''; ?>>
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
                    <option value="Premier League" <?php echo (isset($_POST['competition']) && $_POST['competition'] === 'Premier League') ? 'selected' : ''; ?>>Premier League</option>
                    <option value="FA Cup" <?php echo (isset($_POST['competition']) && $_POST['competition'] === 'FA Cup') ? 'selected' : ''; ?>>FA Cup</option>
                    <option value="Carabao Cup" <?php echo (isset($_POST['competition']) && $_POST['competition'] === 'Carabao Cup') ? 'selected' : ''; ?>>Carabao Cup</option>
                    <option value="Champions League" <?php echo (isset($_POST['competition']) && $_POST['competition'] === 'Champions League') ? 'selected' : ''; ?>>Champions League</option>
                    <option value="Europa League" <?php echo (isset($_POST['competition']) && $_POST['competition'] === 'Europa League') ? 'selected' : ''; ?>>Europa League</option>
                    <option value="Community Shield" <?php echo (isset($_POST['competition']) && $_POST['competition'] === 'Community Shield') ? 'selected' : ''; ?>>Community Shield</option>
                </select>
            </div>

            <!-- Match Date -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal & Waktu Pertandingan</label>
                <input type="datetime-local" name="match_date" required value="<?php echo $_POST['match_date'] ?? ''; ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
            </div>

            <!-- Venue -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Venue (Opsional)</label>
                <input type="text" name="venue" value="<?php echo $_POST['venue'] ?? ''; ?>" placeholder="Contoh: Old Trafford, Etihad Stadium" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Status</label>
                <select name="status" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
                    <option value="scheduled" <?php echo (isset($_POST['status']) && $_POST['status'] === 'scheduled') ? 'selected' : ''; ?>>Terjadwal</option>
                    <option value="live" <?php echo (isset($_POST['status']) && $_POST['status'] === 'live') ? 'selected' : ''; ?>>Live</option>
                    <option value="finished" <?php echo (isset($_POST['status']) && $_POST['status'] === 'finished') ? 'selected' : ''; ?>>Selesai</option>
                    <option value="postponed" <?php echo (isset($_POST['status']) && $_POST['status'] === 'postponed') ? 'selected' : ''; ?>>Ditunda</option>
                </select>
            </div>

            <!-- Submit -->
            <div class="flex gap-3">
                <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-city-blue to-united-red text-white font-bold rounded-lg hover:shadow-lg transition">
                    💾 Simpan Jadwal
                </button>
                <a href="index.php" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
            </div>

        </form>
    </div>

</main>

<?php include '../includes/footer.php'; ?>
