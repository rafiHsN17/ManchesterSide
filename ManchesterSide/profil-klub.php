<?php
/**
 * Manchester Side - Profil Klub dengan Flip Card System
 */
require_once 'includes/config.php';

$db = getDB();
$team = strtoupper($_GET['team'] ?? 'CITY');
if (!in_array($team, ['CITY', 'UNITED'])) $team = 'CITY';

// Get club data
$stmt = $db->prepare("SELECT * FROM clubs WHERE code = ?");
$stmt->bind_param("s", $team);
$stmt->execute();
$club = $stmt->get_result()->fetch_assoc();

if (!$club) redirect('index.php');

// Get players
$players_query = $db->prepare("SELECT * FROM players WHERE club_id = ? AND is_active = 1 ORDER BY position, jersey_number");
$players_query->bind_param("i", $club['id']);
$players_query->execute();
$players_result = $players_query->get_result();

$players_by_position = [
    'Goalkeeper' => [],
    'Defender' => [],
    'Midfielder' => [],
    'Forward' => []
];

while ($player = $players_result->fetch_assoc()) {
    $players_by_position[$player['position']][] = $player;
}

// Get staff
$staff_query = $db->prepare("SELECT * FROM staff WHERE club_id = ? AND is_active = 1 ORDER BY role");
$staff_query->bind_param("i", $club['id']);
$staff_query->execute();
$staff_result = $staff_query->get_result();

$stats = [];
$stats['total_players'] = $db->query("SELECT COUNT(*) as c FROM players WHERE club_id = {$club['id']} AND is_active = 1")->fetch_assoc()['c'];
$stats['total_staff'] = $db->query("SELECT COUNT(*) as c FROM staff WHERE club_id = {$club['id']} AND is_active = 1")->fetch_assoc()['c'];

$current_user = getCurrentUser();
$social_media = getClubSocialMedia($team);

function getPlayerPhoto($photo_url, $name, $team) {
    if (!empty($photo_url) && file_exists($photo_url)) {
        return $photo_url;
    }
    $bg = $team === 'CITY' ? '6CABDD' : 'DA291C';
    return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&size=400&background={$bg}&color=fff&bold=true&font-size=0.4";
}

function calculateAge($birth_date) {
    if (!$birth_date) return null;
    $birth = new DateTime($birth_date);
    $today = new DateTime();
    return $today->diff($birth)->y;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil <?php echo $club['name']; ?> - Manchester Side</title>
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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        .flip-card-container {
            perspective: 1000px;
            height: 420px;
        }
        
        .flip-card {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.7s;
            transform-style: preserve-3d;
            cursor: pointer;
        }
        
        .flip-card.flipped {
            transform: rotateY(180deg);
        }
        
        .flip-card-front, .flip-card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
        }
        
        .flip-card-back {
            transform: rotateY(180deg);
        }
        
        .flip-card-container:hover .flip-card:not(.flipped) {
            transform: scale(1.02);
        }
        
        .tab-button.active {
            background: linear-gradient(135deg, 
                <?php echo $team === 'CITY' ? '#6CABDD' : '#DA291C'; ?> 0%, 
                <?php echo $team === 'CITY' ? '#1C2C5B' : '#8B0000'; ?> 100%);
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50">

    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $team === 'CITY' ? 'city-navy' : 'red'; ?>-900 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-6">
                <img src="<?php echo getClubLogo($team); ?>" alt="<?php echo $club['name']; ?>" class="w-32 h-32 mx-auto object-contain filter drop-shadow-2xl">
            </div>
            <h1 class="text-5xl md:text-6xl font-black mb-4">
                <?php echo $club['full_name']; ?>
            </h1>
            <p class="text-2xl mb-6 text-white/90">
                Founded <?php echo $club['founded_year']; ?>
            </p>
            <div class="flex justify-center gap-6 text-lg">
                <div>
                    <span class="font-bold"><?php echo $stats['total_players']; ?></span>
                    <span class="text-white/80 ml-2">Pemain</span>
                </div>
                <div class="text-white/50">•</div>
                <div>
                    <span class="font-bold"><?php echo $stats['total_staff']; ?></span>
                    <span class="text-white/80 ml-2">Staff</span>
                </div>
            </div>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Switch Club Button -->
        <div class="text-center mb-8">
            <a href="?team=<?php echo $team === 'CITY' ? 'united' : 'city'; ?>" class="inline-block px-8 py-4 bg-gradient-to-r from-<?php echo $team === 'CITY' ? 'united-red' : 'city-blue'; ?> to-<?php echo $team === 'CITY' ? 'red' : 'city-navy'; ?>-900 text-white font-bold rounded-lg hover:shadow-lg transition text-lg">
                <?php echo $team === 'CITY' ? '🔴 Manchester United' : '🔵 Manchester City'; ?>
            </a>
        </div>

        <!-- Tabs -->
        <div class="mb-8">
            <div class="flex justify-center gap-2 mb-8">
                <button onclick="switchTab('info')" class="tab-button active px-6 py-3 bg-white rounded-lg font-bold transition shadow-md" data-tab="info">
                    ℹ️ Informasi Klub
                </button>
                <button onclick="switchTab('players')" class="tab-button px-6 py-3 bg-white rounded-lg font-bold transition shadow-md" data-tab="players">
                    👥 Skuad Pemain (<?php echo $stats['total_players']; ?>)
                </button>
                <button onclick="switchTab('staff')" class="tab-button px-6 py-3 bg-white rounded-lg font-bold transition shadow-md" data-tab="staff">
                    🎯 Tim Pelatih (<?php echo $stats['total_staff']; ?>)
                </button>
            </div>
        </div>

        <!-- Tab Content: Info -->
        <div id="tab-info" class="tab-content active">
            <!-- Stadium, History, Achievements - Keep existing code -->
            <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">🏟️ Stadion</h2>
                <p class="text-2xl font-bold"><?php echo $club['stadium_name']; ?></p>
                <p class="text-gray-600">Kapasitas: <?php echo number_format($club['stadium_capacity']); ?></p>
            </div>
        </div>

        <!-- Tab Content: Players with FLIP CARDS -->
        <div id="tab-players" class="tab-content">
            <?php foreach ($players_by_position as $position => $players): ?>
                <?php if (!empty($players)): ?>
                    <div class="mb-12">
                        <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                            <span class="text-4xl mr-3">
                                <?php 
                                echo match($position) {
                                    'Goalkeeper' => '🧤',
                                    'Defender' => '🛡️',
                                    'Midfielder' => '⚙️',
                                    'Forward' => '⚽',
                                    default => '👤'
                                };
                                ?>
                            </span>
                            <?php 
                            echo match($position) {
                                'Goalkeeper' => 'Penjaga Gawang',
                                'Defender' => 'Bek',
                                'Midfielder' => 'Gelandang',
                                'Forward' => 'Penyerang',
                                default => $position
                            };
                            ?>
                            <span class="ml-3 text-lg text-gray-500">(<?php echo count($players); ?>)</span>
                        </h2>
                        
                        <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6">
                            <?php foreach ($players as $player): ?>
                                <?php
                                $player_photo = getPlayerPhoto($player['photo_url'], $player['name'], $team);
                                $age = calculateAge($player['birth_date']);
                                $border_color = $team === 'CITY' ? 'border-sky-400' : 'border-red-500';
                                $accent_color = $team === 'CITY' ? 'sky' : 'red';
                                ?>
                                
                                <div class="flip-card-container">
                                    <div class="flip-card" onclick="this.classList.toggle('flipped')">
                                        
                                        <!-- FRONT SIDE -->
                                        <div class="flip-card-front">
                                            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border-4 <?php echo $border_color; ?> h-full hover:shadow-3xl transition-shadow">
                                                <!-- Header -->
                                                <div class="h-20 bg-gradient-to-br from-<?php echo $team === 'CITY' ? 'sky-400' : 'red-500'; ?> to-<?php echo $team === 'CITY' ? 'blue-800' : 'red-900'; ?> flex items-center justify-center relative">
                                                    <div class="text-6xl font-black text-white/30 absolute">
                                                        <?php echo $player['jersey_number']; ?>
                                                    </div>
                                                    <div class="text-4xl font-black text-white relative z-10">
                                                        #<?php echo $player['jersey_number']; ?>
                                                    </div>
                                                </div>
                                                
                                                <!-- Photo -->
                                                <div class="flex justify-center -mt-12 mb-4 px-4">
                                                    <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-2xl bg-gray-100">
                                                        <img 
                                                            src="<?php echo $player_photo; ?>" 
                                                            alt="<?php echo $player['name']; ?>"
                                                            class="w-full h-full object-cover"
                                                            onerror="this.src='<?php echo getPlayerPhoto('', $player['name'], $team); ?>'"
                                                        >
                                                    </div>
                                                </div>
                                                
                                                <!-- Name -->
                                                <div class="px-6 text-center">
                                                    <h3 class="text-xl font-black text-gray-900 mb-2 line-clamp-2 min-h-[56px] flex items-center justify-center">
                                                        <?php echo $player['name']; ?>
                                                    </h3>
                                                    <p class="text-<?php echo $accent_color; ?>-600 font-bold text-sm mb-4">
                                                        <?php echo $position; ?>
                                                    </p>
                                                </div>
                                                
                                                <!-- Quick Info -->
                                                <div class="px-6 pb-6 space-y-2">
                                                    <div class="flex items-center justify-center text-gray-600 text-sm">
                                                        <span class="mr-2">🌍</span>
                                                        <span class="font-semibold"><?php echo $player['nationality']; ?></span>
                                                    </div>
                                                    <?php if ($age): ?>
                                                        <div class="flex items-center justify-center text-gray-600 text-sm">
                                                            <span class="mr-2">🎂</span>
                                                            <span class="font-semibold"><?php echo $age; ?> tahun</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Flip Hint -->
                                                <div class="absolute bottom-4 left-0 right-0 text-center">
                                                    <p class="text-xs text-gray-400 animate-pulse">
                                                        👆 Klik untuk info lengkap
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- BACK SIDE -->
                                        <div class="flip-card-back">
                                            <div class="bg-gradient-to-br from-gray-900 to-gray-800 text-white rounded-2xl shadow-2xl h-full p-6 overflow-y-auto border-4 <?php echo $border_color; ?>">
                                                <!-- Header -->
                                                <div class="text-center mb-6 pb-4 border-b border-white/20">
                                                    <div class="text-5xl font-black mb-2 text-<?php echo $accent_color; ?>-400">
                                                        #<?php echo $player['jersey_number']; ?>
                                                    </div>
                                                    <h3 class="text-xl font-bold mb-1"><?php echo $player['name']; ?></h3>
                                                    <p class="text-sm text-gray-300"><?php echo $position; ?></p>
                                                </div>
                                                
                                                <!-- Detailed Info -->
                                                <div class="space-y-3 text-sm">
                                                    <div class="flex items-start">
                                                        <span class="text-xl mr-3">🌍</span>
                                                        <div>
                                                            <p class="text-gray-400 text-xs">Kebangsaan</p>
                                                            <p class="font-semibold"><?php echo $player['nationality']; ?></p>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if ($player['birth_date']): ?>
                                                        <div class="flex items-start">
                                                            <span class="text-xl mr-3">📅</span>
                                                            <div>
                                                                <p class="text-gray-400 text-xs">Tanggal Lahir</p>
                                                                <p class="font-semibold">
                                                                    <?php echo date('d M Y', strtotime($player['birth_date'])); ?>
                                                                    <?php if ($age): ?>
                                                                        <span class="text-gray-400">(<?php echo $age; ?> tahun)</span>
                                                                    <?php endif; ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($player['height'] || $player['weight']): ?>
                                                        <div class="flex items-start">
                                                            <span class="text-xl mr-3">📏</span>
                                                            <div>
                                                                <p class="text-gray-400 text-xs">Fisik</p>
                                                                <p class="font-semibold">
                                                                    <?php if ($player['height']): echo $player['height'] . ' cm'; endif; ?>
                                                                    <?php if ($player['height'] && $player['weight']): echo ' • '; endif; ?>
                                                                    <?php if ($player['weight']): echo $player['weight'] . ' kg'; endif; ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($player['joined_date']): ?>
                                                        <div class="flex items-start">
                                                            <span class="text-xl mr-3">🔄</span>
                                                            <div>
                                                                <p class="text-gray-400 text-xs">Bergabung</p>
                                                                <p class="font-semibold"><?php echo date('Y', strtotime($player['joined_date'])); ?></p>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($player['biography']): ?>
                                                        <div class="flex items-start">
                                                            <span class="text-xl mr-3">ℹ️</span>
                                                            <div>
                                                                <p class="text-gray-400 text-xs mb-1">Info</p>
                                                                <p class="text-gray-300 text-xs leading-relaxed">
                                                                    <?php echo substr($player['biography'], 0, 150); ?><?php echo strlen($player['biography']) > 150 ? '...' : ''; ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Flip Back Hint -->
                                                <div class="mt-6 pt-4 border-t border-white/20 text-center">
                                                    <p class="text-xs text-gray-400 animate-pulse">
                                                        👆 Klik untuk kembali
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Tab Content: Staff (keep existing) -->
        <div id="tab-staff" class="tab-content">
            <!-- Existing staff code -->
        </div>

    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            document.getElementById('tab-' + tabName).classList.add('active');
            document.querySelector('[data-tab="' + tabName + '"]').classList.add('active');
        }
    </script>

</body>
</html>