<?php
/**
 * Manchester Side - Match Schedule Page
 */
require_once 'includes/config.php';

$db = getDB();

// Get filter
$filter = $_GET['filter'] ?? 'all'; // all, upcoming, finished

// Build query
$where_conditions = ["(h.code = 'CITY' OR a.code = 'CITY' OR h.code = 'UNITED' OR a.code = 'UNITED')"];

if ($filter === 'upcoming') {
    $where_conditions[] = "m.match_date > NOW()";
    $order_by = "m.match_date ASC";
} elseif ($filter === 'finished') {
    $where_conditions[] = "m.status = 'finished'";
    $order_by = "m.match_date DESC";
} else {
    $order_by = "m.match_date DESC";
}

$where_clause = implode(" AND ", $where_conditions);

// Get matches
$query = "SELECT 
    m.*,
    h.name as home_team, h.code as home_code, h.color_primary as home_color,
    a.name as away_team, a.code as away_code, a.color_primary as away_color
FROM matches m
JOIN clubs h ON m.home_team_id = h.id
JOIN clubs a ON m.away_team_id = a.id
WHERE $where_clause
ORDER BY $order_by
LIMIT 50";

$matches_result = $db->query($query);

// Get derby matches count
$derby_query = "SELECT COUNT(*) as total FROM matches m
JOIN clubs h ON m.home_team_id = h.id
JOIN clubs a ON m.away_team_id = a.id
WHERE ((h.code = 'CITY' AND a.code = 'UNITED') OR (h.code = 'UNITED' AND a.code = 'CITY'))
AND m.status = 'finished'";
$derby_count = $db->query($derby_query)->fetch_assoc()['total'];

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pertandingan - Manchester Side</title>
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
    </style>
</head>
<body class="bg-gray-50">

    <?php include 'includes/header.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold text-gray-900 mb-4">
                📅 Jadwal Pertandingan
            </h1>
            <p class="text-xl text-gray-600">
                Manchester Derby & Pertandingan Penting
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-br from-city-blue to-city-navy text-white rounded-xl shadow-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-4xl">🔵</span>
                    <span class="text-3xl font-bold">
                        <?php 
                        $city_matches = $db->query("SELECT COUNT(*) as c FROM matches m JOIN clubs h ON m.home_team_id = h.id JOIN clubs a ON m.away_team_id = a.id WHERE (h.code = 'CITY' OR a.code = 'CITY')")->fetch_assoc()['c'];
                        echo $city_matches;
                        ?>
                    </span>
                </div>
                <p class="font-bold text-lg">Manchester City</p>
                <p class="text-sm text-blue-100">Total Pertandingan</p>
            </div>

            <div class="bg-gradient-to-br from-united-red to-red-900 text-white rounded-xl shadow-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-4xl">🔴</span>
                    <span class="text-3xl font-bold">
                        <?php 
                        $united_matches = $db->query("SELECT COUNT(*) as c FROM matches m JOIN clubs h ON m.home_team_id = h.id JOIN clubs a ON m.away_team_id = a.id WHERE (h.code = 'UNITED' OR a.code = 'UNITED')")->fetch_assoc()['c'];
                        echo $united_matches;
                        ?>
                    </span>
                </div>
                <p class="font-bold text-lg">Manchester United</p>
                <p class="text-sm text-red-100">Total Pertandingan</p>
            </div>

            <div class="bg-gradient-to-br from-purple-600 to-purple-900 text-white rounded-xl shadow-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-4xl">⚔️</span>
                    <span class="text-3xl font-bold"><?php echo $derby_count; ?></span>
                </div>
                <p class="font-bold text-lg">Manchester Derby</p>
                <p class="text-sm text-purple-100">Total Pertemuan</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex justify-center gap-3 mb-8">
            <a href="?filter=all" class="px-6 py-3 <?php echo $filter === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-bold shadow-md transition">
                Semua Pertandingan
            </a>
            <a href="?filter=upcoming" class="px-6 py-3 <?php echo $filter === 'upcoming' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-bold shadow-md transition">
                Akan Datang
            </a>
            <a href="?filter=finished" class="px-6 py-3 <?php echo $filter === 'finished' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-bold shadow-md transition">
                Selesai
            </a>
        </div>

        <!-- Matches List -->
        <?php if ($matches_result->num_rows > 0): ?>
            <div class="space-y-6">
                <?php while ($match = $matches_result->fetch_assoc()): ?>
                    <?php 
                    $is_derby = (($match['home_code'] === 'CITY' && $match['away_code'] === 'UNITED') || 
                                 ($match['home_code'] === 'UNITED' && $match['away_code'] === 'CITY'));
                    $is_finished = $match['status'] === 'finished';
                    $is_live = $match['status'] === 'live';
                    ?>
                    
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden <?php echo $is_derby ? 'ring-4 ring-purple-500' : ''; ?>">
                        
                        <?php if ($is_derby): ?>
                            <div class="bg-gradient-to-r from-purple-600 to-purple-900 text-white py-2 px-6 text-center font-bold">
                                ⚔️ MANCHESTER DERBY ⚔️
                            </div>
                        <?php endif; ?>

                        <div class="p-8">
                            <!-- Competition & Date -->
                            <div class="text-center mb-6">
                                <span class="inline-block px-4 py-2 bg-gray-800 text-white rounded-full text-sm font-bold mb-3">
                                    🏆 <?php echo $match['competition']; ?>
                                </span>
                                <p class="text-gray-600 font-semibold">
                                    <?php echo formatDateIndo($match['match_date']); ?> • 
                                    <?php echo date('H:i', strtotime($match['match_date'])); ?> WIB
                                </p>
                                <?php if ($match['venue']): ?>
                                    <p class="text-sm text-gray-500 mt-1">
                                        📍 <?php echo $match['venue']; ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <!-- Match Info -->
                            <div class="grid grid-cols-3 gap-6 items-center">
                                
                                <!-- Home Team -->
                                <div class="text-center">
                                    <div class="text-6xl mb-4">
                                        <?php echo $match['home_code'] === 'CITY' ? '🔵' : '🔴'; ?>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-900 mb-2">
                                        <?php echo $match['home_team']; ?>
                                    </h3>
                                    <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-semibold">
                                        HOME
                                    </span>
                                </div>

                                <!-- Score or VS -->
                                <div class="text-center">
                                    <?php if ($is_finished): ?>
                                        <div class="text-6xl font-black text-gray-900 mb-2">
                                            <?php echo $match['home_score']; ?> - <?php echo $match['away_score']; ?>
                                        </div>
                                        <span class="inline-block px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-bold">
                                            ✅ FULL TIME
                                        </span>
                                    <?php elseif ($is_live): ?>
                                        <div class="text-6xl font-black text-red-600 mb-2">
                                            <?php echo $match['home_score']; ?> - <?php echo $match['away_score']; ?>
                                        </div>
                                        <span class="inline-block px-4 py-2 bg-red-100 text-red-800 rounded-full text-sm font-bold animate-pulse">
                                            🔴 LIVE
                                        </span>
                                    <?php else: ?>
                                        <div class="text-6xl font-black text-gray-400 mb-4">
                                            VS
                                        </div>
                                        <?php
                                        $now = time();
                                        $match_time = strtotime($match['match_date']);
                                        $diff_days = floor(($match_time - $now) / 86400);
                                        ?>
                                        <?php if ($diff_days > 0): ?>
                                            <span class="inline-block px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm font-bold">
                                                ⏰ <?php echo $diff_days; ?> hari lagi
                                            </span>
                                        <?php elseif ($diff_days === 0): ?>
                                            <span class="inline-block px-4 py-2 bg-orange-100 text-orange-800 rounded-full text-sm font-bold">
                                                🔥 HARI INI
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Away Team -->
                                <div class="text-center">
                                    <div class="text-6xl mb-4">
                                        <?php echo $match['away_code'] === 'CITY' ? '🔵' : '🔴'; ?>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-900 mb-2">
                                        <?php echo $match['away_team']; ?>
                                    </h3>
                                    <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-semibold">
                                        AWAY
                                    </span>
                                </div>

                            </div>

                            <!-- Match Result Summary -->
                            <?php if ($is_finished): ?>
                                <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                                    <?php
                                    $winner = '';
                                    if ($match['home_score'] > $match['away_score']) {
                                        $winner = $match['home_team'] . ' Menang!';
                                        $winner_color = $match['home_code'] === 'CITY' ? 'text-city-blue' : 'text-united-red';
                                    } elseif ($match['away_score'] > $match['home_score']) {
                                        $winner = $match['away_team'] . ' Menang!';
                                        $winner_color = $match['away_code'] === 'CITY' ? 'text-city-blue' : 'text-united-red';
                                    } else {
                                        $winner = 'Pertandingan Berakhir Imbang';
                                        $winner_color = 'text-gray-600';
                                    }
                                    ?>
                                    <p class="text-xl font-bold <?php echo $winner_color; ?>">
                                        <?php echo $winner; ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20">
                <div class="inline-block p-8 bg-white rounded-full shadow-xl mb-6">
                    <span class="text-8xl">📅</span>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Tidak Ada Pertandingan
                </h2>
                <p class="text-gray-600 mb-8">
                    Belum ada jadwal pertandingan untuk filter ini
                </p>
                <a href="schedule.php" class="inline-block px-8 py-4 bg-gradient-to-r from-city-blue to-united-red text-white font-bold rounded-lg hover:shadow-lg transition">
                    Lihat Semua Jadwal
                </a>
            </div>
        <?php endif; ?>

    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>