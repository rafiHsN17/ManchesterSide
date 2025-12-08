<?php
/**
 * Manchester Side - Fixtures, Results & Tables
 * Combined schedule and standings page
 */
require_once 'includes/config.php';

$db = getDB();

// Get current tab (fixtures, results, tables)
$tab = $_GET['tab'] ?? 'fixtures';

// Get matches based on tab - Only Manchester Derby matches
if ($tab === 'fixtures') {
    // Upcoming Manchester Derby matches
    $query = "SELECT 
        m.*,
        h.name as home_team, h.code as home_code,
        a.name as away_team, a.code as away_code
    FROM matches m
    JOIN clubs h ON m.home_team_id = h.id
    JOIN clubs a ON m.away_team_id = a.id
    WHERE ((h.code = 'CITY' AND a.code = 'UNITED') OR (h.code = 'UNITED' AND a.code = 'CITY'))
    AND m.match_date >= NOW()
    AND m.status = 'scheduled'
    ORDER BY m.match_date ASC";
} elseif ($tab === 'results') {
    // Past Manchester Derby matches
    $query = "SELECT 
        m.*,
        h.name as home_team, h.code as home_code,
        a.name as away_team, a.code as away_code
    FROM matches m
    JOIN clubs h ON m.home_team_id = h.id
    JOIN clubs a ON m.away_team_id = a.id
    WHERE ((h.code = 'CITY' AND a.code = 'UNITED') OR (h.code = 'UNITED' AND a.code = 'CITY'))
    AND m.status = 'finished'
    ORDER BY m.match_date DESC";
}

// Get matches for fixtures/results tabs
$matches = [];
if ($tab !== 'tables') {
    $result = $db->query($query);
    while ($row = $result->fetch_assoc()) {
        $matches[] = $row;
    }
}

// Get H2H statistics for tables tab
if ($tab === 'tables') {
    $city = $db->query("SELECT * FROM clubs WHERE code = 'CITY'")->fetch_assoc();
    $united = $db->query("SELECT * FROM clubs WHERE code = 'UNITED'")->fetch_assoc();
    
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
    
    $stats = [
        'total_matches' => 0,
        'city_wins' => 0,
        'united_wins' => 0,
        'draws' => 0,
        'city_goals' => 0,
        'united_goals' => 0,
        'city_home_wins' => 0,
        'city_home_draws' => 0,
        'city_home_losses' => 0,
        'united_home_wins' => 0,
        'united_home_draws' => 0,
        'united_home_losses' => 0,
        'city_clean_sheets' => 0,
        'united_clean_sheets' => 0,
        'high_scoring' => 0, // 3+ goals
        'current_streak' => ['team' => null, 'count' => 0, 'type' => null],
        'unbeaten_streak' => ['team' => null, 'count' => 0],
    ];
    
    $derby_history = [];
    $recent_5 = [];
    $last_winner = null;
    $streak_count = 0;
    $unbeaten_city = 0;
    $unbeaten_united = 0;
    
    while ($match = $derby_matches->fetch_assoc()) {
        $derby_history[] = $match;
        $stats['total_matches']++;
        
        if ($match['home_code'] === 'CITY') {
            $city_score = $match['home_score'];
            $united_score = $match['away_score'];
            $city_home = true;
        } else {
            $city_score = $match['away_score'];
            $united_score = $match['home_score'];
            $city_home = false;
        }
        
        $stats['city_goals'] += $city_score;
        $stats['united_goals'] += $united_score;
        
        // Total goals check
        if (($city_score + $united_score) >= 3) {
            $stats['high_scoring']++;
        }
        
        // Clean sheets
        if ($city_score > 0 && $united_score == 0) $stats['city_clean_sheets']++;
        if ($united_score > 0 && $city_score == 0) $stats['united_clean_sheets']++;
        
        // Determine winner
        $winner = null;
        if ($city_score > $united_score) {
            $stats['city_wins']++;
            $winner = 'CITY';
            if ($city_home) {
                $stats['city_home_wins']++;
            }
        } elseif ($united_score > $city_score) {
            $stats['united_wins']++;
            $winner = 'UNITED';
            if (!$city_home) {
                $stats['united_home_wins']++;
            }
        } else {
            $stats['draws']++;
            if ($city_home) {
                $stats['city_home_draws']++;
            } else {
                $stats['united_home_draws']++;
            }
        }
        
        // Home/Away stats
        if ($city_home) {
            if ($winner === 'UNITED') $stats['city_home_losses']++;
        } else {
            if ($winner === 'CITY') $stats['united_home_losses']++;
        }
        
        // Recent 5 matches
        if (count($recent_5) < 5) {
            $recent_5[] = $match;
        }
        
        // Calculate winning streak (most recent matches)
        if ($last_winner === null) {
            $last_winner = $winner;
            $streak_count = ($winner !== null) ? 1 : 0;
        } elseif ($winner === $last_winner && $winner !== null) {
            $streak_count++;
        } else {
            if ($streak_count > $stats['current_streak']['count']) {
                $stats['current_streak'] = [
                    'team' => $last_winner,
                    'count' => $streak_count,
                    'type' => 'win'
                ];
            }
            $last_winner = $winner;
            $streak_count = ($winner !== null) ? 1 : 0;
        }
        
        // Unbeaten streak
        if ($winner !== 'UNITED') {
            $unbeaten_city++;
            $unbeaten_united = 0;
        } else {
            $unbeaten_united++;
            $unbeaten_city = 0;
        }
        
        if ($winner !== 'CITY') {
            $unbeaten_united++;
            $unbeaten_city = 0;
        } else {
            $unbeaten_city++;
            $unbeaten_united = 0;
        }
    }
    
    // Final streak check
    if ($streak_count > $stats['current_streak']['count']) {
        $stats['current_streak'] = [
            'team' => $last_winner,
            'count' => $streak_count,
            'type' => 'win'
        ];
    }
    
    // Set unbeaten streak
    if ($unbeaten_city > $unbeaten_united) {
        $stats['unbeaten_streak'] = ['team' => 'CITY', 'count' => $unbeaten_city];
    } else {
        $stats['unbeaten_streak'] = ['team' => 'UNITED', 'count' => $unbeaten_united];
    }
    
    // Calculate averages
    $stats['avg_goals_per_match'] = $stats['total_matches'] > 0 ? 
        round(($stats['city_goals'] + $stats['united_goals']) / $stats['total_matches'], 2) : 0;
    $stats['city_avg_goals'] = $stats['total_matches'] > 0 ? 
        round($stats['city_goals'] / $stats['total_matches'], 2) : 0;
    $stats['united_avg_goals'] = $stats['total_matches'] > 0 ? 
        round($stats['united_goals'] / $stats['total_matches'], 2) : 0;
}

// Get available months
$months_query = "SELECT DISTINCT DATE_FORMAT(match_date, '%Y-%m') as month_key
                 FROM matches 
                 ORDER BY match_date DESC 
                 LIMIT 12";
$available_months = $db->query($months_query);

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal & Hasil - Manchester Side</title>
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

    <!-- Navigation Tabs -->
    <div class="bg-city-navy text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8">
                <a href="?tab=fixtures" 
                   class="py-4 px-2 font-bold border-b-4 transition <?php echo $tab === 'fixtures' ? 'border-white' : 'border-transparent hover:border-white/50'; ?>">
                    Jadwal
                </a>
                <a href="?tab=results" 
                   class="py-4 px-2 font-bold border-b-4 transition <?php echo $tab === 'results' ? 'border-white' : 'border-transparent hover:border-white/50'; ?>">
                    Hasil
                </a>
                <a href="?tab=tables" 
                   class="py-4 px-2 font-bold border-b-4 transition <?php echo $tab === 'tables' ? 'border-white' : 'border-transparent hover:border-white/50'; ?>">
                    Statistik
                </a>
            </div>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <?php if ($tab === 'fixtures' || $tab === 'results'): ?>
            
            <!-- Fixtures/Results Section -->
            <div class="mb-8">
                <h1 class="text-5xl font-bold text-city-blue mb-2">
                    <?php echo $tab === 'fixtures' ? 'JADWAL' : 'HASIL'; ?> MANCHESTER DERBY
                </h1>
                <p class="text-lg text-gray-600">Musim 2025/2026</p>
            </div>

            <!-- Matches List -->
            <?php if (count($matches) > 0): ?>
                <div class="space-y-6">
                    <?php 
                    $current_date = null;
                    foreach ($matches as $match): 
                        $match_date = date('Y-m-d', strtotime($match['match_date']));
                        $is_derby = (($match['home_code'] === 'CITY' && $match['away_code'] === 'UNITED') || 
                                     ($match['home_code'] === 'UNITED' && $match['away_code'] === 'CITY'));
                        
                        // Show date header
                        if ($match_date !== $current_date):
                            $current_date = $match_date;
                            $day_name = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][date('w', strtotime($match['match_date']))];
                            $day_num = date('d', strtotime($match['match_date']));
                            $month_short = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][date('n', strtotime($match['match_date'])) - 1];
                    ?>
                            <div class="flex items-center space-x-3 mt-8 mb-4">
                                <div class="text-sm font-bold text-gray-500">
                                    <?php echo $day_name . ' ' . $day_num . ' ' . $month_short; ?>
                                </div>
                            </div>
                    <?php endif; ?>

                    <!-- Match Card -->
                    <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-6 <?php echo $is_derby ? 'ring-2 ring-purple-500' : ''; ?>">
                        <div class="flex items-center justify-between">
                            
                            <!-- Left: Home Team -->
                            <div class="flex items-center space-x-4 flex-1">
                                <img src="<?php echo $match['home_code'] === 'CITY' ? 'https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg' : 'https://upload.wikimedia.org/wikipedia/en/7/7a/Manchester_United_FC_crest.svg'; ?>" 
                                     alt="<?php echo $match['home_team']; ?>" 
                                     class="w-12 h-12 object-contain">
                                <span class="font-bold text-gray-900 text-lg"><?php echo $match['home_team']; ?></span>
                            </div>

                            <!-- Center: Score/Time -->
                            <div class="text-center px-8">
                                <?php if ($tab === 'results'): ?>
                                    <div class="text-3xl font-black text-gray-900">
                                        <?php echo $match['home_score']; ?> - <?php echo $match['away_score']; ?>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">FT</div>
                                <?php else: ?>
                                    <div class="text-xl font-bold text-gray-600">
                                        <?php echo date('H:i', strtotime($match['match_date'])); ?>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">WIB</div>
                                <?php endif; ?>
                            </div>

                            <!-- Right: Away Team -->
                            <div class="flex items-center space-x-4 flex-1 justify-end">
                                <span class="font-bold text-gray-900 text-lg"><?php echo $match['away_team']; ?></span>
                                <img src="<?php echo $match['away_code'] === 'CITY' ? 'https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg' : 'https://upload.wikimedia.org/wikipedia/en/7/7a/Manchester_United_FC_crest.svg'; ?>" 
                                     alt="<?php echo $match['away_team']; ?>" 
                                     class="w-12 h-12 object-contain">
                            </div>

                        </div>

                        <!-- Match Info -->
                        <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between text-sm text-gray-600">
                            <span class="font-semibold"><?php echo $match['competition']; ?></span>
                            <?php if ($match['venue']): ?>
                                <span>📍 <?php echo $match['venue']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="bg-white rounded-xl shadow p-12 text-center">
                    <div class="text-6xl mb-4">📅</div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Tidak Ada Pertandingan</h3>
                    <p class="text-gray-600">Tidak ada jadwal pertandingan untuk bulan ini</p>
                </div>
            <?php endif; ?>

        <?php else: ?>
            
            <!-- Tables Section (Head to Head) -->
            <div class="mb-8">
                <h1 class="text-5xl font-bold text-city-blue mb-2">STATISTIK PERTEMUAN</h1>
                <p class="text-xl text-gray-600">Analisis Lengkap Manchester Derby</p>
            </div>

            <!-- Head to Head Dropdown Toggle -->
            <div class="mb-6">
                <button onclick="toggleH2H()" 
                        class="w-full bg-gradient-to-r from-city-blue to-united-red text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-xl transition flex items-center justify-between">
                    <span class="text-xl">⚔️ Statistik Inti Head-to-Head</span>
                    <svg id="h2h-arrow" class="w-6 h-6 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>

            <div id="h2h-content" class="space-y-6">

            <!-- H2H Main Card -->
            <div class="bg-gradient-to-br from-purple-600 to-purple-900 rounded-2xl shadow-2xl p-8 mb-8 text-white">
                <div class="grid md:grid-cols-3 gap-8 items-center">
                    
                    <div class="text-center">
                        <img src="https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg" 
                             alt="Manchester City" class="w-24 h-24 object-contain mx-auto mb-4">
                        <h2 class="text-2xl font-bold mb-2">Manchester City</h2>
                        <div class="text-5xl font-black"><?php echo $stats['city_wins']; ?></div>
                        <p class="text-sm mt-2">Kemenangan</p>
                    </div>

                    <div class="text-center border-x border-white/30">
                        <div class="mb-4">
                            <p class="text-sm mb-1">Total Pertemuan</p>
                            <p class="text-4xl font-black"><?php echo $stats['total_matches']; ?></p>
                        </div>
                        <div>
                            <p class="text-sm mb-1">Imbang</p>
                            <p class="text-3xl font-black"><?php echo $stats['draws']; ?></p>
                        </div>
                    </div>

                    <div class="text-center">
                        <img src="https://upload.wikimedia.org/wikipedia/en/7/7a/Manchester_United_FC_crest.svg" 
                             alt="Manchester United" class="w-24 h-24 object-contain mx-auto mb-4">
                        <h2 class="text-2xl font-bold mb-2">Manchester United</h2>
                        <div class="text-5xl font-black"><?php echo $stats['united_wins']; ?></div>
                        <p class="text-sm mt-2">Kemenangan</p>
                    </div>

                </div>
            </div>

            <!-- Trend dan Dominasi -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="text-2xl mr-2">📈</span>
                    Trend & Dominasi
                </h3>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Winning Streak -->
                    <?php if ($stats['current_streak']['count'] > 0): ?>
                    <div class="p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg border-l-4 border-purple-500">
                        <p class="text-sm text-gray-600 mb-1">🔥 Trend Kemenangan Beruntun</p>
                        <p class="text-lg font-bold text-gray-900">
                            <?php 
                            $streak_team = $stats['current_streak']['team'] === 'CITY' ? 'Manchester City' : 'Manchester United';
                            echo $streak_team . ' menang ' . $stats['current_streak']['count'] . ' laga beruntun';
                            ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Unbeaten Streak -->
                    <?php if ($stats['unbeaten_streak']['count'] > 1): ?>
                    <div class="p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg border-l-4 border-green-500">
                        <p class="text-sm text-gray-600 mb-1">🛡️ Pertemuan Tanpa Kalah</p>
                        <p class="text-lg font-bold text-gray-900">
                            <?php 
                            $unbeaten_team = $stats['unbeaten_streak']['team'] === 'CITY' ? 'Manchester City' : 'Manchester United';
                            echo $unbeaten_team . ' tak terkalahkan dalam ' . $stats['unbeaten_streak']['count'] . ' laga H2H terakhir';
                            ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistik Berdasarkan Venue -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="text-2xl mr-2">🏟️</span>
                    Statistik Berdasarkan Venue
                </h3>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- City Home -->
                    <div class="p-4 bg-city-blue/5 rounded-lg border border-city-blue/20">
                        <div class="flex items-center mb-3">
                            <img src="https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg" alt="City" class="w-8 h-8 mr-2">
                            <h4 class="font-bold text-gray-900">Di Kandang Man City</h4>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2 bg-green-100 rounded">
                                <p class="text-2xl font-bold text-green-700"><?php echo $stats['city_home_wins']; ?></p>
                                <p class="text-xs text-gray-600">Menang</p>
                            </div>
                            <div class="p-2 bg-gray-100 rounded">
                                <p class="text-2xl font-bold text-gray-700"><?php echo $stats['city_home_draws']; ?></p>
                                <p class="text-xs text-gray-600">Seri</p>
                            </div>
                            <div class="p-2 bg-red-100 rounded">
                                <p class="text-2xl font-bold text-red-700"><?php echo $stats['city_home_losses']; ?></p>
                                <p class="text-xs text-gray-600">Kalah</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- United Home -->
                    <div class="p-4 bg-united-red/5 rounded-lg border border-united-red/20">
                        <div class="flex items-center mb-3">
                            <img src="https://upload.wikimedia.org/wikipedia/en/7/7a/Manchester_United_FC_crest.svg" alt="United" class="w-8 h-8 mr-2">
                            <h4 class="font-bold text-gray-900">Di Kandang Man United</h4>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2 bg-green-100 rounded">
                                <p class="text-2xl font-bold text-green-700"><?php echo $stats['united_home_wins']; ?></p>
                                <p class="text-xs text-gray-600">Menang</p>
                            </div>
                            <div class="p-2 bg-gray-100 rounded">
                                <p class="text-2xl font-bold text-gray-700"><?php echo $stats['united_home_draws']; ?></p>
                                <p class="text-xs text-gray-600">Seri</p>
                            </div>
                            <div class="p-2 bg-red-100 rounded">
                                <p class="text-2xl font-bold text-red-700"><?php echo $stats['united_home_losses']; ?></p>
                                <p class="text-xs text-gray-600">Kalah</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik Mendalam -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="text-2xl mr-2">🔬</span>
                    Statistik Mendalam
                </h3>
                
                <div class="grid md:grid-cols-4 gap-4">
                    <!-- Avg Goals -->
                    <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg">
                        <p class="text-3xl font-black text-blue-600"><?php echo $stats['avg_goals_per_match']; ?></p>
                        <p class="text-sm text-gray-700 font-semibold mt-1">Rata-rata Gol/Pertandingan</p>
                    </div>
                    
                    <!-- High Scoring -->
                    <div class="text-center p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg">
                        <p class="text-3xl font-black text-orange-600">
                            <?php echo $stats['total_matches'] > 0 ? round(($stats['high_scoring'] / $stats['total_matches']) * 100) : 0; ?>%
                        </p>
                        <p class="text-sm text-gray-700 font-semibold mt-1">Pertandingan 3+ Gol</p>
                    </div>
                    
                    <!-- City Clean Sheet -->
                    <div class="text-center p-4 bg-gradient-to-br from-sky-50 to-sky-100 rounded-lg">
                        <p class="text-3xl font-black text-sky-600"><?php echo $stats['city_clean_sheets']; ?></p>
                        <p class="text-sm text-gray-700 font-semibold mt-1">Clean Sheet City</p>
                    </div>
                    
                    <!-- United Clean Sheet -->
                    <div class="text-center p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-lg">
                        <p class="text-3xl font-black text-red-600"><?php echo $stats['united_clean_sheets']; ?></p>
                        <p class="text-sm text-gray-700 font-semibold mt-1">Clean Sheet United</p>
                    </div>
                </div>
                
                <!-- Average Goals per Team -->
                <div class="mt-6 grid md:grid-cols-2 gap-4">
                    <div class="p-4 bg-city-blue/10 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Rata-rata Gol Man City</p>
                        <p class="text-3xl font-bold text-city-blue"><?php echo $stats['city_avg_goals']; ?> <span class="text-sm">gol/pertandingan</span></p>
                    </div>
                    <div class="p-4 bg-united-red/10 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Rata-rata Gol Man United</p>
                        <p class="text-3xl font-bold text-united-red"><?php echo $stats['united_avg_goals']; ?> <span class="text-sm">gol/pertandingan</span></p>
                    </div>
                </div>
            </div>

            <!-- Goals Statistics -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="font-bold text-gray-900 mb-4">⚽ Total Gol Tercipta</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-city-blue/10 rounded-lg">
                            <span class="font-semibold text-city-blue">Manchester City</span>
                            <span class="text-2xl font-bold"><?php echo $stats['city_goals']; ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-united-red/10 rounded-lg">
                            <span class="font-semibold text-united-red">Manchester United</span>
                            <span class="text-2xl font-bold"><?php echo $stats['united_goals']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="font-bold text-gray-900 mb-4">📊 Persentase Kemenangan</h3>
                    <div class="space-y-3">
                        <?php 
                        $city_pct = $stats['total_matches'] > 0 ? round(($stats['city_wins'] / $stats['total_matches']) * 100, 1) : 0;
                        $united_pct = $stats['total_matches'] > 0 ? round(($stats['united_wins'] / $stats['total_matches']) * 100, 1) : 0;
                        $draw_pct = $stats['total_matches'] > 0 ? round(($stats['draws'] / $stats['total_matches']) * 100, 1) : 0;
                        ?>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-city-blue">Man City</span>
                            <span class="text-xl font-bold"><?php echo $city_pct; ?>%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-600">Imbang</span>
                            <span class="text-xl font-bold"><?php echo $draw_pct; ?>%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-united-red">Man United</span>
                            <span class="text-xl font-bold"><?php echo $united_pct; ?>%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pertemuan Terakhir (5-10 Laga) -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <span class="text-2xl mr-2">📋</span>
                    Pertemuan Terakhir (10 Laga)
                </h3>
                
                <?php if (count($derby_history) > 0): ?>
                    <div class="space-y-3">
                        <?php 
                        $recent_10 = array_slice($derby_history, 0, 10);
                        foreach ($recent_10 as $index => $match): 
                            $city_score = $match['home_code'] === 'CITY' ? $match['home_score'] : $match['away_score'];
                            $united_score = $match['home_code'] === 'CITY' ? $match['away_score'] : $match['home_score'];
                            
                            $bg_class = 'bg-gray-50';
                            $result_text = 'Seri';
                            if ($city_score > $united_score) {
                                $bg_class = 'bg-city-blue/10 border-l-4 border-city-blue';
                                $result_text = 'City Menang';
                            } elseif ($united_score > $city_score) {
                                $bg_class = 'bg-united-red/10 border-l-4 border-united-red';
                                $result_text = 'United Menang';
                            }
                        ?>
                            
                            <div class="flex items-center justify-between p-4 <?php echo $bg_class; ?> rounded-lg hover:shadow-md transition">
                                <div class="flex items-center space-x-3 flex-1">
                                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-xs font-bold text-gray-600">
                                        <?php echo $index + 1; ?>
                                    </div>
                                    <img src="https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg" 
                                         alt="City" class="w-8 h-8">
                                    <span class="font-semibold">Man City</span>
                                </div>
                                
                                <div class="text-center px-6">
                                    <p class="text-2xl font-bold"><?php echo $city_score; ?> - <?php echo $united_score; ?></p>
                                    <p class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($match['match_date'])); ?></p>
                                    <p class="text-xs font-semibold mt-1 <?php echo $city_score > $united_score ? 'text-city-blue' : ($united_score > $city_score ? 'text-united-red' : 'text-gray-600'); ?>">
                                        <?php echo $result_text; ?>
                                    </p>
                                </div>
                                
                                <div class="flex items-center space-x-3 flex-1 justify-end">
                                    <span class="font-semibold">Man United</span>
                                    <img src="https://upload.wikimedia.org/wikipedia/en/7/7a/Manchester_United_FC_crest.svg" 
                                         alt="United" class="w-8 h-8">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-500 py-8">Belum ada pertandingan derby yang tercatat</p>
                <?php endif; ?>
            </div>

            <!-- Riwayat Derby Lengkap -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Riwayat Derby Lengkap</h3>
                
                <?php if (count($derby_history) > 0): ?>
                    <div class="space-y-3">
                        <?php foreach ($derby_history as $match): ?>
                            <?php
                            $city_score = $match['home_code'] === 'CITY' ? $match['home_score'] : $match['away_score'];
                            $united_score = $match['home_code'] === 'CITY' ? $match['away_score'] : $match['home_score'];
                            
                            $bg_class = 'bg-gray-50';
                            if ($city_score > $united_score) $bg_class = 'bg-city-blue/10';
                            elseif ($united_score > $city_score) $bg_class = 'bg-united-red/10';
                            ?>
                            
                            <div class="flex items-center justify-between p-4 <?php echo $bg_class; ?> rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <img src="https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg" 
                                         alt="City" class="w-8 h-8">
                                    <span class="font-semibold">Man City</span>
                                </div>
                                
                                <div class="text-center">
                                    <p class="text-2xl font-bold"><?php echo $city_score; ?> - <?php echo $united_score; ?></p>
                                    <p class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($match['match_date'])); ?></p>
                                </div>
                                
                                <div class="flex items-center space-x-3">
                                    <span class="font-semibold">Man United</span>
                                    <img src="https://upload.wikimedia.org/wikipedia/en/7/7a/Manchester_United_FC_crest.svg" 
                                         alt="United" class="w-8 h-8">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-500 py-8">Belum ada pertandingan derby yang tercatat</p>
                <?php endif; ?>
            </div>

            </div>

        <?php endif; ?>

    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        function toggleH2H() {
            const content = document.getElementById('h2h-content');
            const arrow = document.getElementById('h2h-arrow');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                arrow.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        }
    </script>

</body>
</html>
