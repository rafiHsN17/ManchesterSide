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
    $home_score = isset($_POST['home_score']) && $_POST['home_score'] !== '' ? (int)$_POST['home_score'] : null;
    $away_score = isset($_POST['away_score']) && $_POST['away_score'] !== '' ? (int)$_POST['away_score'] : null;
    
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
    
    // Validate scores if status is finished
    if ($status === 'finished' && ($home_score === null || $away_score === null)) {
        $errors[] = 'Skor wajib diisi untuk pertandingan yang sudah selesai';
    }
    
    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO matches (home_team_id, away_team_id, competition, match_date, venue, status, home_score, away_score) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssii", $home_team_id, $away_team_id, $competition, $match_date, $venue, $status, $home_score, $away_score);
        
        if ($stmt->execute()) {
            $match_id = $db->insert_id;
            
            // Insert goal scorers if provided
            if (!empty($_POST['goal_scorers'])) {
                $goal_stmt = $db->prepare("INSERT INTO match_goals (match_id, player_id, team_id, minute, assist_player_id) VALUES (?, ?, ?, ?, ?)");
                
                foreach ($_POST['goal_scorers'] as $goal) {
                    if (!empty($goal['player_id'])) {
                        $player_id = (int)$goal['player_id'];
                        $team_id = (int)$goal['team_id'];
                        $minute = !empty($goal['minute']) ? (int)$goal['minute'] : null;
                        $assist_player_id = !empty($goal['assist_player_id']) ? (int)$goal['assist_player_id'] : null;
                        
                        $goal_stmt->bind_param("iiiii", $match_id, $player_id, $team_id, $minute, $assist_player_id);
                        $goal_stmt->execute();
                    }
                }
            }
            
            setFlashMessage('success', 'Jadwal berhasil ditambahkan');
            redirect('index.php');
        } else {
            $errors[] = 'Gagal menambahkan jadwal';
        }
    }
}

// Get clubs
$clubs = $db->query("SELECT * FROM clubs ORDER BY name");

// Get all players for goal scorers dropdown
$players = $db->query("SELECT p.id, p.name, p.club_id, c.name as club_name, c.code as club_code 
                       FROM players p 
                       JOIN clubs c ON p.club_id = c.id 
                       ORDER BY c.name, p.name");

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
                <select name="status" id="status" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
                    <option value="scheduled" <?php echo (isset($_POST['status']) && $_POST['status'] === 'scheduled') ? 'selected' : ''; ?>>Terjadwal</option>
                    <option value="live" <?php echo (isset($_POST['status']) && $_POST['status'] === 'live') ? 'selected' : ''; ?>>Live</option>
                    <option value="finished" <?php echo (isset($_POST['status']) && $_POST['status'] === 'finished') ? 'selected' : ''; ?>>Selesai</option>
                    <option value="postponed" <?php echo (isset($_POST['status']) && $_POST['status'] === 'postponed') ? 'selected' : ''; ?>>Ditunda</option>
                </select>
            </div>

            <!-- Score Section (shown when status is finished or live) -->
            <div id="scoreSection" style="display: none;" class="border-t pt-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">⚽ Skor Pertandingan</h3>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Skor Home</label>
                        <input type="number" name="home_score" id="home_score" min="0" value="<?php echo $_POST['home_score'] ?? ''; ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Skor Away</label>
                        <input type="number" name="away_score" id="away_score" min="0" value="<?php echo $_POST['away_score'] ?? ''; ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-city-blue focus:border-transparent">
                    </div>
                </div>

                <!-- Goal Scorers -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-lg font-bold text-gray-900">🎯 Pencetak Gol & Assist</h4>
                        <button type="button" onclick="addGoalScorer()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-semibold">
                            + Tambah Gol
                        </button>
                    </div>
                    
                    <div id="goalScorersList" class="space-y-3">
                        <!-- Goal scorers will be added here dynamically -->
                    </div>
                </div>
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

<script>
// Players data for dropdown
const playersData = <?php 
$players->data_seek(0);
$players_array = [];
while ($p = $players->fetch_assoc()) {
    $players_array[] = $p;
}
echo json_encode($players_array);
?>;

let goalCounter = 0;

// Show/hide score section based on status
document.getElementById('status').addEventListener('change', function() {
    const scoreSection = document.getElementById('scoreSection');
    if (this.value === 'finished' || this.value === 'live') {
        scoreSection.style.display = 'block';
    } else {
        scoreSection.style.display = 'none';
    }
});

// Trigger on page load
document.addEventListener('DOMContentLoaded', function() {
    const status = document.getElementById('status').value;
    if (status === 'finished' || status === 'live') {
        document.getElementById('scoreSection').style.display = 'block';
    }
});

function addGoalScorer() {
    goalCounter++;
    const homeTeamId = document.querySelector('select[name="home_team_id"]').value;
    const awayTeamId = document.querySelector('select[name="away_team_id"]').value;
    
    if (!homeTeamId || !awayTeamId) {
        alert('Pilih tim home dan away terlebih dahulu!');
        return;
    }
    
    const goalDiv = document.createElement('div');
    goalDiv.className = 'bg-gray-50 p-4 rounded-lg border border-gray-200';
    goalDiv.id = 'goal-' + goalCounter;
    
    // Filter players by team
    const homePlayers = playersData.filter(p => p.club_id == homeTeamId);
    const awayPlayers = playersData.filter(p => p.club_id == awayTeamId);
    const allPlayers = [...homePlayers, ...awayPlayers];
    
    goalDiv.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tim</label>
                    <select name="goal_scorers[${goalCounter}][team_id]" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm" onchange="filterGoalPlayers(${goalCounter}, this.value)">
                        <option value="">Pilih Tim</option>
                        <option value="${homeTeamId}">Home</option>
                        <option value="${awayTeamId}">Away</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Pencetak Gol</label>
                    <select name="goal_scorers[${goalCounter}][player_id]" id="goal_player_${goalCounter}" required class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                        <option value="">Pilih Pemain</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Assist (Opsional)</label>
                    <select name="goal_scorers[${goalCounter}][assist_player_id]" id="assist_player_${goalCounter}" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                        <option value="">Tidak ada</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Menit</label>
                    <input type="number" name="goal_scorers[${goalCounter}][minute]" min="1" max="120" placeholder="45" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                </div>
            </div>
            <button type="button" onclick="removeGoalScorer(${goalCounter})" class="mt-6 px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition text-sm">
                🗑️
            </button>
        </div>
    `;
    
    document.getElementById('goalScorersList').appendChild(goalDiv);
}

function filterGoalPlayers(goalId, teamId) {
    const goalPlayerSelect = document.getElementById('goal_player_' + goalId);
    const assistPlayerSelect = document.getElementById('assist_player_' + goalId);
    
    // Clear existing options
    goalPlayerSelect.innerHTML = '<option value="">Pilih Pemain</option>';
    assistPlayerSelect.innerHTML = '<option value="">Tidak ada</option>';
    
    // Filter and add players from selected team
    const teamPlayers = playersData.filter(p => p.club_id == teamId);
    
    teamPlayers.forEach(player => {
        const option1 = document.createElement('option');
        option1.value = player.id;
        option1.textContent = player.name;
        goalPlayerSelect.appendChild(option1);
        
        const option2 = document.createElement('option');
        option2.value = player.id;
        option2.textContent = player.name;
        assistPlayerSelect.appendChild(option2);
    });
}

function removeGoalScorer(goalId) {
    const goalDiv = document.getElementById('goal-' + goalId);
    if (goalDiv) {
        goalDiv.remove();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
