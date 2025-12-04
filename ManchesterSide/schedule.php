<?php
/**
 * Manchester Side - Modern Match Schedule Page
 * Design inspired by Man City fixtures page
 */
require_once 'includes/config.php';

$db = getDB();

// Get filter and month
$filter = $_GET['filter'] ?? 'all'; // all, upcoming, results
$month = $_GET['month'] ?? date('Y-m');

// Build query based on filter
$where_conditions = ["(h.code = 'CITY' OR a.code = 'CITY' OR h.code = 'UNITED' OR a.code = 'UNITED')"];

if ($filter === 'upcoming') {
    $where_conditions[] = "m.match_date >= NOW()";
    $where_conditions[] = "m.status = 'scheduled'";
    $order_by = "m.match_date ASC";
} elseif ($filter === 'results') {
    $where_conditions[] = "m.status = 'finished'";
    $order_by = "m.match_date DESC";
} else {
    $order_by = "m.match_date DESC";
}

// Add month filter
$where_conditions[] = "DATE_FORMAT(m.match_date, '%Y-%m') = ?";

$where_clause = implode(" AND ", $where_conditions);

// Get matches
$query = "SELECT 
    m.*,
    h.name as home_team, h.code as home_code,
    a.name as away_team, a.code as away_code
FROM matches m
JOIN clubs h ON m.home_team_id = h.id
JOIN clubs a ON m.away_team_id = a.id
WHERE $where_clause
ORDER BY $order_by";

$stmt = $db->prepare($query);
$stmt->bind_param("s", $month);
$stmt->execute();
$matches_result = $stmt->get_result();

// Get available months
$months_query = "SELECT DISTINCT DATE_FORMAT(match_date, '%Y-%m') as month_key, 
                 DATE_FORMAT(match_date, '%M %Y') as month_name
                 FROM matches 
                 ORDER BY match_date DESC 
                 LIMIT 12";
$available_months = $db->query($months_query);

// Get statistics
$stats = [];
$stats['total'] = $db->query("SELECT COUNT(*) as c FROM matches")->fetch_assoc()['c'];
$stats['upcoming'] = $db->query("SELECT COUNT(*) as c FROM matches WHERE match_date >= NOW() AND status = 'scheduled'")->fetch_assoc()['c'];
$stats['finished'] = $db->query("SELECT COUNT(*) as c FROM matches WHERE status = 'finished'")->fetch_assoc()['c'];

$current_user = getCurrentUser();

// Month names in Indonesian
$month_names = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

$current_month_name = $month_names[date('m', strtotime($month . '-01'))] . ' ' . date('Y', strtotime($month . '-01'));
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
        <div class="mb-8">
            <h1 class="text-5xl font-bold text-gray-900 mb-2">
                Jadwal & Hasil
            </h1>
            <p class="text-xl text-gray-600">
                Pertandingan Manchester City & Manchester United
            </p>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-xl shadow-md mb-6 p-2 flex gap-2">
            <a href="?filter=all&month=<?php echo $month; ?>" 
               class="flex-1 py-3 px-6 text-center font-bold rounded-lg transition <?php echo $filter === 'all' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100'; ?>">
                Semua
                <span class="ml-2 text-sm">(<?php echo $stats['total']; ?>)</span>
            </a>
            <a href="?filter=upcoming&month=<?php echo $month; ?>" 
               class="flex-1 py-3 px-6 text-center font-bold rounded-lg transition <?php echo $filter === 'upcoming' ? 'bg-green-600 text-white' : 'text-gray-600 hover:bg-gray-100'; ?>">
                Akan Datang
                <span class="ml-2 text-sm">(<?php echo $stats['upcoming']; ?>)</span>
            </a>
            <a href="?filter=results&month=<?php echo $month; ?>" 
               class="flex-1 py-3 px-6 text-center font-bold rounded-lg transition <?php echo $filter === 'results' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'; ?>">
                Hasil
                <span class="ml-2 text-sm">(<?php echo $stats['finished']; ?>)</span>
            </a>
        </div>

        <div class="grid lg:grid-cols-4 gap-6">
            
            <!-- Sidebar - Month Selector -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md p-6 sticky top-24">
                    <h3 class="font-bold text-gray-900 mb-4 text-lg">Pilih Bulan</h3>
                    <div class="space-y-2">
                        <?php while ($m = $available_months->fetch_assoc()): ?>
                            <a href="?filter=<?php echo $filter; ?>&month=<?php echo $m['month_key']; ?>" 
                               class="block px-4 py-3 rounded-lg text-sm font-semibold transition <?php echo $month === $m['month_key'] ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                                <?php 
                                $month_display = $month_names[date('m', strtotime($m['month_key'] . '-01'))] . ' ' . date('Y', strtotime($m['month_key'] . '-01'));
                                echo $month_display;
                                ?>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content - Matches List -->
            <div class="lg:col-span-3">
                
                <div class="bg-white rounded-xl shadow-md p-6 mb-4">
                    <h2 class="text-2xl font-bold text-gray-900">
                        <?php echo $current_month_name; ?>
                    </h2>
                </div>

                <?php if ($matches_result->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php 
                        $current_date = null;
                        while ($match = $matches_result->fetch_assoc()): 
                            $match_date = date('Y-m-d', strtotime($match['match_date']));
                            $is_derby = (($match['home_code'] === 'CITY' && $match['away_code'] === 'UNITED') || 
                                         ($match['home_code'] === 'UNITED' && $match['away_code'] === 'CITY'));
                            $is_finished = $match['status'] === 'finished';
                            
                            // Show date header if new date
                            if ($match_date !== $current_date):
                                $current_date = $match_date;
                        ?>
                                <div class="text-sm font-bold text-gray-500 uppercase mt-8 mb-3">
                                    <?php echo formatDateIndo($match['match_date']); ?>
                                </div>
                        <?php endif; ?>

                        <!-- Match Card -->
                        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition overflow-hidden <?php echo $is_derby ? 'ring-2 ring-purple-500' : ''; ?>">
                            
                            <?php if ($is_derby): ?>
                                <div class="bg-purple-600 text-white text-center py-1 text-xs font-bold">
                                    ⚔️ MANCHESTER DERBY
                                </div>
                            <?php endif; ?>

                            <div class="p-6">
                                <div class="grid grid-cols-3 gap-4 items-center">
                                    
                                    <!-- Home Team -->
                                    <div class="text-center">
                                        <div class="text-4xl mb-2">
                                            <?php echo $match['home_code'] === 'CITY' ? '🔵' : '🔴'; ?>
                                        </div>
                                        <div class="font-bold text-gray-900 text-lg">
                                            <?php echo $match['home_code']; ?>
                                        </div>
                                    </div>

                                    <!-- Match Info -->
                                    <div class="text-center">
                                        <?php if ($is_finished): ?>
                                            <div class="text-4xl font-black text-gray-900 mb-2">
                                                <?php echo $match['home_score']; ?> - <?php echo $match['away_score']; ?>
                                            </div>
                                            <div class="text-xs text-green-600 font-bold">FULL TIME</div>
                                        <?php else: ?>
                                            <div class="text-2xl font-bold text-gray-400 mb-2">VS</div>
                                            <div class="text-sm font-semibold text-gray-700">
                                                <?php echo date('H:i', strtotime($match['match_date'])); ?> WIB
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="mt-3">
                                            <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">
                                                <?php echo $match['competition']; ?>
                                            </span>
                                        </div>
                                        
                                        <?php if ($match['venue']): ?>
                                            <div class="text-xs text-gray-500 mt-2">
                                                📍 <?php echo $match['venue']; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Away Team -->
                                    <div class="text-center">
                                        <div class="text-4xl mb-2">
                                            <?php echo $match['away_code'] === 'CITY' ? '🔵' : '🔴'; ?>
                                        </div>
                                        <div class="font-bold text-gray-900 text-lg">
                                            <?php echo $match['away_code']; ?>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="bg-white rounded-xl shadow-md p-12 text-center">
                        <div class="text-6xl mb-4">📅</div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">
                            Tidak Ada Pertandingan
                        </h3>
                        <p class="text-gray-600 mb-6">
                            Tidak ada jadwal pertandingan untuk bulan ini
                        </p>
                        <a href="?filter=all&month=<?php echo date('Y-m'); ?>" 
                           class="inline-block px-6 py-3 bg-gray-900 text-white font-bold rounded-lg hover:bg-gray-800 transition">
                            Lihat Bulan Ini
                        </a>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>