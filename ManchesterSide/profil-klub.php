<?php
/**
 * Manchester Side - Profil Klub Terpadu (Informasi, Pemain, Staff)
 */
require_once 'includes/config.php';

$db = getDB();

// Get team parameter (city or united)
$team = $_GET['team'] ?? 'city';
$team = strtoupper($team);

if (!in_array($team, ['CITY', 'UNITED'])) {
    $team = 'CITY';
}

// Get club data
$stmt = $db->prepare("SELECT * FROM clubs WHERE code = ?");
$stmt->bind_param("s", $team);
$stmt->execute();
$club = $stmt->get_result()->fetch_assoc();

if (!$club) {
    redirect('index.php');
}

// Get players grouped by position
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

// Get statistics
$stats = [];
$stats['total_players'] = $db->query("SELECT COUNT(*) as c FROM players WHERE club_id = {$club['id']} AND is_active = 1")->fetch_assoc()['c'];
$stats['total_staff'] = $db->query("SELECT COUNT(*) as c FROM staff WHERE club_id = {$club['id']} AND is_active = 1")->fetch_assoc()['c'];

$current_user = getCurrentUser();

// Social media untuk klub
$social_media = getClubSocialMedia($team);
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
            <!-- Stadium Info -->
            <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="text-4xl mr-3">🏟️</span>
                    Stadion
                </h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-600 mb-2">Nama Stadion</p>
                        <p class="text-2xl font-bold text-gray-900 mb-4"><?php echo $club['stadium_name']; ?></p>
                        
                        <p class="text-gray-600 mb-2">Lokasi</p>
                        <p class="text-xl font-semibold text-gray-700 mb-4"><?php echo $club['stadium_location']; ?></p>
                        
                        <p class="text-gray-600 mb-2">Kapasitas</p>
                        <p class="text-xl font-semibold text-gray-700"><?php echo number_format($club['stadium_capacity']); ?> penonton</p>
                    </div>
                    <div class="bg-gray-100 rounded-xl p-6 flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-6xl mb-4">🏟️</div>
                            <p class="text-gray-600 font-semibold">Foto Stadion</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History -->
            <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="text-4xl mr-3">📖</span>
                    Sejarah Klub
                </h2>
                <div class="prose prose-lg max-w-none text-gray-700">
                    <?php echo nl2br($club['history']); ?>
                </div>
            </div>

            <!-- Achievements -->
            <div class="bg-gradient-to-br from-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $team === 'CITY' ? 'city-navy' : 'red'; ?>-900 rounded-2xl shadow-xl p-8 text-white mb-8">
                <h2 class="text-3xl font-bold mb-6 flex items-center">
                    <span class="text-4xl mr-3">🏆</span>
                    Prestasi & Trofi
                </h2>
                <div class="prose prose-lg max-w-none text-white/90">
                    <?php echo nl2br($club['achievements']); ?>
                </div>
            </div>

            <!-- Social Media -->
            <?php if (!empty($social_media)): ?>
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="text-4xl mr-3">📱</span>
                    Media Sosial Resmi
                </h2>
                <div class="grid md:grid-cols-4 gap-4">
                    <a href="<?php echo $social_media['facebook']; ?>" target="_blank" class="flex items-center justify-center gap-3 px-6 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <span class="text-2xl">📘</span>
                        <span class="font-bold">Facebook</span>
                    </a>
                    <a href="<?php echo $social_media['twitter']; ?>" target="_blank" class="flex items-center justify-center gap-3 px-6 py-4 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition">
                        <span class="text-2xl">🐦</span>
                        <span class="font-bold">Twitter</span>
                    </a>
                    <a href="<?php echo $social_media['instagram']; ?>" target="_blank" class="flex items-center justify-center gap-3 px-6 py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:from-purple-600 hover:to-pink-600 transition">
                        <span class="text-2xl">📷</span>
                        <span class="font-bold">Instagram</span>
                    </a>
                    <a href="<?php echo $social_media['youtube']; ?>" target="_blank" class="flex items-center justify-center gap-3 px-6 py-4 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <span class="text-2xl">📺</span>
                        <span class="font-bold">YouTube</span>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tab Content: Players -->
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
                                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition group">
                                    <div class="h-32 bg-gradient-to-br from-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $team === 'CITY' ? 'city-navy' : 'red'; ?>-900 flex items-center justify-center relative">
                                        <div class="text-7xl font-black text-white group-hover:scale-110 transition">
                                            <?php echo $player['jersey_number']; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="p-5">
                                        <h3 class="font-bold text-gray-900 text-lg mb-2 group-hover:text-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> transition">
                                            <?php echo $player['name']; ?>
                                        </h3>
                                        
                                        <div class="text-sm text-gray-600 space-y-2">
                                            <p class="flex items-center">
                                                <span class="mr-2">🌍</span>
                                                <?php echo $player['nationality']; ?>
                                            </p>
                                            <?php if ($player['height']): ?>
                                                <p class="flex items-center">
                                                    <span class="mr-2">📏</span>
                                                    <?php echo $player['height']; ?> cm
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($player['birth_date']): ?>
                                                <p class="flex items-center">
                                                    <span class="mr-2">🎂</span>
                                                    <?php echo date('Y') - date('Y', strtotime($player['birth_date'])); ?> tahun
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Tab Content: Staff -->
        <div id="tab-staff" class="tab-content">
            <?php if ($staff_result->num_rows > 0): ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php 
                    $staff_result->data_seek(0);
                    while ($staff = $staff_result->fetch_assoc()): 
                    ?>
                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition group">
                            <div class="h-24 bg-gradient-to-br from-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $team === 'CITY' ? 'city-navy' : 'red'; ?>-900 flex items-center justify-center">
                                <h3 class="text-white font-bold text-lg text-center px-4">
                                    <?php echo $staff['role']; ?>
                                </h3>
                            </div>

                            <div class="p-6">
                                <div class="flex justify-center -mt-16 mb-4">
                                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center text-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> font-black text-4xl shadow-xl border-4 border-white group-hover:scale-110 transition">
                                        <?php echo strtoupper(substr($staff['name'], 0, 1)); ?>
                                    </div>
                                </div>

                                <h2 class="text-2xl font-bold text-gray-900 text-center mb-4 group-hover:text-<?php echo $team === 'CITY' ? 'city-blue' : 'united-red'; ?> transition">
                                    <?php echo $staff['name']; ?>
                                </h2>

                                <div class="space-y-3 mb-4">
                                    <div class="flex items-center justify-center text-gray-600">
                                        <span class="text-xl mr-2">🌍</span>
                                        <span><?php echo $staff['nationality']; ?></span>
                                    </div>
                                    
                                    <?php if ($staff['join_date']): ?>
                                        <div class="flex items-center justify-center text-gray-600 text-sm">
                                            <span class="mr-2">📅</span>
                                            <span>Sejak <?php echo formatDateIndo($staff['join_date']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($staff['biography']): ?>
                                    <div class="pt-4 border-t border-gray-200">
                                        <p class="text-sm text-gray-600 text-center leading-relaxed">
                                            <?php echo $staff['biography']; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-20">
                    <div class="text-8xl mb-6">🎯</div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">
                        Belum Ada Data Staff
                    </h2>
                    <p class="text-gray-600">
                        Informasi tim pelatih dan staff akan segera ditambahkan
                    </p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById('tab-' + tabName).classList.add('active');
            
            // Add active class to clicked button
            document.querySelector('[data-tab="' + tabName + '"]').classList.add('active');
        }
    </script>

</body>
</html>