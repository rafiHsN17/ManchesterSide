<?php
/**
 * Manchester Side - Head to Head Statistics
 */
require_once 'includes/config.php';

$db = getDB();

// Get clubs
$city = $db->query("SELECT * FROM clubs WHERE code = 'CITY'")->fetch_assoc();
$united = $db->query("SELECT * FROM clubs WHERE code = 'UNITED'")->fetch_assoc();

// Get all derby matches (finished only)
$derby_matches = $db->query("SELECT 
    m.*,
    h.name as home_team, h.code as home_code,
    a.name as away_team, a.code as away_code
FROM matches m
JOIN clubs h ON m.home_team_id = h.id
JOIN clubs a ON m.away_team_id = a.id
WHERE ((h.code = 'CITY' AND a.code = 'UNITED') OR (h.code = 'UNITED' AND a.code = 'CITY'))
AND m.status = 'finished'
ORDER BY m.match_date DESC");

// Calculate statistics
$stats = [
    'total_matches' => 0,
    'city_wins' => 0,
    'united_wins' => 0,
    'draws' => 0,
    'city_goals' => 0,
    'united_goals' => 0,
    'biggest_city_win' => ['score' => 0, 'match' => null],
    'biggest_united_win' => ['score' => 0, 'match' => null],
];

$matches_array = [];
while ($match = $derby_matches->fetch_assoc()) {
    $matches_array[] = $match;
    $stats['total_matches']++;
    
    // Determine scores for each team
    if ($match['home_code'] === 'CITY') {
        $city_score = $match['home_score'];
        $united_score = $match['away_score'];
    } else {
        $city_score = $match['away_score'];
        $united_score = $match['home_score'];
    }
    
    $stats['city_goals'] += $city_score;
    $stats['united_goals'] += $united_score;
    
    // Determine winner
    if ($city_score > $united_score) {
        $stats['city_wins']++;
        $goal_diff = $city_score - $united_score;
        if ($goal_diff > $stats['biggest_city_win']['score']) {
            $stats['biggest_city_win']['score'] = $goal_diff;
            $stats['biggest_city_win']['match'] = $match;
        }
    } elseif ($united_score > $city_score) {
        $stats['united_wins']++;
        $goal_diff = $united_score - $city_score;
        if ($goal_diff > $stats['biggest_united_win']['score']) {
            $stats['biggest_united_win']['score'] = $goal_diff;
            $stats['biggest_united_win']['match'] = $match;
        }
    } else {
        $stats['draws']++;
    }
}

// Calculate percentages
$city_win_percentage = $stats['total_matches'] > 0 ? ($stats['city_wins'] / $stats['total_matches']) * 100 : 0;
$united_win_percentage = $stats['total_matches'] > 0 ? ($stats['united_wins'] / $stats['total_matches']) * 100 : 0;
$draw_percentage = $stats['total_matches'] > 0 ? ($stats['draws'] / $stats['total_matches']) * 100 : 0;

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klasemen Head to Head - Manchester Side</title>
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
                ⚔️ Manchester Derby
            </h1>
            <p class="text-xl text-gray-600">
                Head to Head Statistics
            </p>
        </div>

        <!-- Main H2H Card -->
        <div class="bg-gradient-to-br from-purple-600 to-purple-900 rounded-3xl shadow-2xl p-8 mb-12 text-white">
            <div class="grid md:grid-cols-3 gap-8 items-center">
                
                <!-- Manchester City -->
                <div class="text-center">
                    <div class="text-8xl mb-4">🔵</div>
                    <h2 class="text-3xl font-black mb-2"><?php echo $city['name']; ?></h2>
                    <div class="text-6xl font-black mb-4"><?php echo $stats['city_wins']; ?></div>
                    <p class="text-xl font-semibold">Kemenangan</p>
                </div>

                <!-- Stats -->
                <div class="text-center border-x border-white/30 px-6">
                    <div class="mb-6">
                        <p class="text-lg mb-2">Total Pertemuan</p>
                        <p class="text-5xl font-black"><?php echo $stats['total_matches']; ?></p>
                    </div>
                    <div>
                        <p class="text-lg mb-2">Imbang</p>
                        <p class="text-4xl font-black"><?php echo $stats['draws']; ?></p>
                    </div>
                </div>

                <!-- Manchester United -->
                <div class="text-center">
                    <div class="text-8xl mb-4">🔴</div>
                    <h2 class="text-3xl font-black mb-2"><?php echo $united['name']; ?></h2>
                    <div class="text-6xl font-black mb-4"><?php echo $stats['united_wins']; ?></div>
                    <p class="text-xl font-semibold">Kemenangan</p>
                </div>

            </div>
        </div>

        <!-- Win Percentage Bar -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Persentase Kemenangan</h3>
            <div class="flex h-16 rounded-full overflow-hidden shadow-lg mb-6">
                <div class="bg-city-blue flex items-center justify-center text-white font-bold" style="width: <?php echo $city_win_percentage; ?>%">
                    <?php if ($city_win_percentage > 15): ?>
                        <?php echo round($city_win_percentage, 1); ?>%
                    <?php endif; ?>
                </div>
                <div class="bg-gray-400 flex items-center justify-center text-white font-bold" style="width: <?php echo $draw_percentage; ?>%">
                    <?php if ($draw_percentage > 10): ?>
                        <?php echo round($draw_percentage, 1); ?>%
                    <?php endif; ?>
                </div>
                <div class="bg-united-red flex items-center justify-center text-white font-bold" style="width: <?php echo $united_win_percentage; ?>%">
                    <?php if ($united_win_percentage > 15): ?>
                        <?php echo round($united_win_percentage, 1); ?>%
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-city-blue font-bold">🔵 Man City: <?php echo round($city_win_percentage, 1); ?>%</span>
                <span class="text-gray-600 font-bold">⚪ Draw: <?php echo round($draw_percentage, 1); ?>%</span>
                <span class="text-united-red font-bold">🔴 Man United: <?php echo round($united_win_percentage, 1); ?>%</span>
            </div>
        </div>

        <!-- Detailed Statistics -->
        <div class="grid md:grid-cols-3 gap-6 mb-12">
            
            <!-- Goals Scored -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h4 class="font-bold text-gray-900 mb-4 text-center">⚽ Total Gol</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-city-blue/10 rounded-lg">
                        <span class="font-semibold text-city-blue">🔵 Man City</span>
                        <span class="text-2xl font-bold text-gray-900"><?php echo $stats['city_goals']; ?></span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-united-red/10 rounded-lg">
                        <span class="font-semibold text-united-red">🔴 Man United</span>
                        <span class="text-2xl font-bold text-gray-900"><?php echo $stats['united_goals']; ?></span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-600">Total Gol Derby</p>
                    <p class="text-3xl font-bold text-gray-900"><?php echo $stats['city_goals'] + $stats['united_goals']; ?></p>
                </div>
            </div>

            <!-- Biggest Win City -->
            <?php if ($stats['biggest_city_win']['match']): ?>
            <div class="bg-gradient-to-br from-city-blue to-city-navy text-white rounded-xl shadow-lg p-6">
                <h4 class="font-bold mb-4 text-center">🏆 Kemenangan Terbesar City</h4>
                <?php 
                $match = $stats['biggest_city_win']['match'];
                $city_score = $match['home_code'] === 'CITY' ? $match['home_score'] : $match['away_score'];
                $united_score = $match['home_code'] === 'CITY' ? $match['away_score'] : $match['home_score'];
                ?>
                <div class="text-center mb-4">
                    <p class="text-5xl font-black mb-2">
                        <?php echo $city_score; ?> - <?php echo $united_score; ?>
                    </p>
                    <p class="text-sm">Selisih <?php echo $stats['biggest_city_win']['score']; ?> gol</p>
                </div>
                <div class="text-center text-sm">
                    <p><?php echo $match['competition']; ?></p>
                    <p><?php echo formatDateIndo($match['match_date']); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Biggest Win United -->
            <?php if ($stats['biggest_united_win']['match']): ?>
            <div class="bg-gradient-to-br from-united-red to-red-900 text-white rounded-xl shadow-lg p-6">
                <h4 class="font-bold mb-4 text-center">🏆 Kemenangan Terbesar United</h4>
                <?php 
                $match = $stats['biggest_united_win']['match'];
                $united_score = $match['home_code'] === 'UNITED' ? $match['home_score'] : $match['away_score'];
                $city_score = $match['home_code'] === 'UNITED' ? $match['away_score'] : $match['home_score'];
                ?>
                <div class="text-center mb-4">
                    <p class="text-5xl font-black mb-2">
                        <?php echo $united_score; ?> - <?php echo $city_score; ?>
                    </p>
                    <p class="text-sm">Selisih <?php echo $stats['biggest_united_win']['score']; ?> gol</p>
                </div>
                <div class="text-center text-sm">
                    <p><?php echo $match['competition']; ?></p>
                    <p><?php echo formatDateIndo($match['match_date']); ?></p>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Recent Matches History -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">📜 Riwayat Pertemuan Terakhir</h3>
            
            <?php if (count($matches_array) > 0): ?>
                <div class="space-y-4">
                    <?php foreach (array_slice($matches_array, 0, 10) as $match): ?>
                        <?php
                        $city_score = $match['home_code'] === 'CITY' ? $match['home_score'] : $match['away_score'];
                        $united_score = $match['home_code'] === 'CITY' ? $match['away_score'] : $match['home_score'];
                        
                        $winner = '';
                        $winner_bg = 'bg-gray-100';
                        if ($city_score > $united_score) {
                            $winner = 'City Win';
                            $winner_bg = 'bg-city-blue/20';
                        } elseif ($united_score > $city_score) {
                            $winner = 'United Win';
                            $winner_bg = 'bg-united-red/20';
                        } else {
                            $winner = 'Draw';
                            $winner_bg = 'bg-gray-200';
                        }
                        ?>
                        
                        <div class="flex items-center justify-between p-4 <?php echo $winner_bg; ?> rounded-lg hover:shadow-md transition">
                            <div class="flex items-center space-x-4">
                                <span class="text-2xl">🔵</span>
                                <span class="font-bold text-gray-900">Man City</span>
                            </div>
                            
                            <div class="text-center px-6">
                                <p class="text-3xl font-black text-gray-900">
                                    <?php echo $city_score; ?> - <?php echo $united_score; ?>
                                </p>
                                <p class="text-xs text-gray-600 mt-1">
                                    <?php echo formatDateIndo($match['match_date']); ?>
                                </p>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <span class="font-bold text-gray-900">Man United</span>
                                <span class="text-2xl">🔴</span>
                            </div>
                            
                            <div class="ml-6">
                                <span class="inline-block px-3 py-1 bg-white rounded-full text-xs font-bold">
                                    <?php echo $match['competition']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center mt-8">
                    <a href="schedule.php" class="inline-block px-8 py-4 bg-gradient-to-r from-city-blue to-united-red text-white font-bold rounded-lg hover:shadow-lg transition">
                        Lihat Semua Pertandingan →
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center py-12 text-gray-500">
                    <p class="text-6xl mb-4">📊</p>
                    <p class="text-lg">Belum ada data pertemuan yang tercatat</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>