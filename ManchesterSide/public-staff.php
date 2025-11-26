<?php
/**
 * Manchester Side - Public Staff Directory
 */
require_once 'includes/config.php';

$db = getDB();

// Get filter parameter
$club_filter = $_GET['club'] ?? 'all';

// Build query
$query = "SELECT s.*, c.name as club_name, c.code as club_code, c.color_primary
FROM staff s
JOIN clubs c ON s.club_id = c.id
WHERE s.is_active = 1";

if ($club_filter === 'city') {
    $query .= " AND c.code = 'CITY'";
} elseif ($club_filter === 'united') {
    $query .= " AND c.code = 'UNITED'";
}

$query .= " ORDER BY c.id, 
    CASE 
        WHEN s.role LIKE '%Manager%' THEN 1
        WHEN s.role LIKE '%Assistant%' THEN 2
        WHEN s.role LIKE '%Coach%' THEN 3
        ELSE 4
    END,
    s.name";

$staff_result = $db->query($query);

// Group by club
$staff_by_club = ['CITY' => [], 'UNITED' => []];
$staff_result->data_seek(0);
while ($staff = $staff_result->fetch_assoc()) {
    $staff_by_club[$staff['club_code']][] = $staff;
}

$current_user = getCurrentUser();

// Role priorities for sizing
$manager_roles = ['Manager', 'Head Coach'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff & Coaching Team - Manchester Side</title>
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
        
        .staff-photo-frame {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            transition: all 0.3s ease;
        }
        
        .staff-photo-frame:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .staff-photo-frame::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #6CABDD, #DA291C, #6CABDD);
            border-radius: 1rem;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .staff-photo-frame:hover::before {
            opacity: 1;
            animation: gradient-rotate 3s linear infinite;
        }
        
        @keyframes gradient-rotate {
            0% { filter: hue-rotate(0deg); }
            100% { filter: hue-rotate(360deg); }
        }
        
        .role-badge {
            position: absolute;
            bottom: 0.5rem;
            left: 0.5rem;
            right: 0.5rem;
            padding: 0.5rem;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(10px);
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: white;
            text-align: center;
            border: 2px solid rgba(255,255,255,0.3);
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="flex items-center space-x-3">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-city-blue rounded-full"></div>
                        <div class="w-8 h-8 bg-united-red rounded-full -ml-3"></div>
                    </div>
                    <span class="text-2xl font-bold bg-gradient-to-r from-city-blue via-gray-800 to-united-red bg-clip-text text-transparent">
                        Manchester Side
                    </span>
                </a>

                <div class="hidden md:flex items-center space-x-6">
                    <a href="index.php" class="text-gray-700 hover:text-city-blue font-semibold transition">Beranda</a>
                    <a href="news.php" class="text-gray-700 hover:text-city-blue font-semibold transition">Berita</a>
                    <a href="players-public.php" class="text-gray-700 hover:text-city-blue font-semibold transition">Pemain</a>
                    <a href="staff-public.php" class="text-city-blue font-bold">Staff</a>
                    <?php if ($current_user): ?>
                        <a href="profile.php" class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gradient-to-r from-city-blue to-united-red rounded-full flex items-center justify-center text-white font-bold">
                                <?php echo strtoupper(substr($current_user['username'], 0, 1)); ?>
                            </div>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="px-4 py-2 text-gray-700 hover:text-city-blue font-semibold transition">Masuk</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-city-blue via-gray-800 to-united-red text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl md:text-6xl font-black mb-4">
                👔 Coaching Staff & Management
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-white/90">
                Tim Pelatih & Staf Profesional Manchester
            </p>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Filter Buttons -->
        <div class="flex justify-center gap-4 mb-12">
            <a href="?club=all" class="px-8 py-3 <?php echo $club_filter === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'; ?> rounded-lg font-bold shadow-lg transition">
                Semua Staff
            </a>
            <a href="?club=city" class="px-8 py-3 <?php echo $club_filter === 'city' ? 'bg-city-blue text-white' : 'bg-white text-gray-700 hover:bg-city-blue hover:text-white'; ?> rounded-lg font-bold shadow-lg transition">
                🔵 Man City
            </a>
            <a href="?club=united" class="px-8 py-3 <?php echo $club_filter === 'united' ? 'bg-united-red text-white' : 'bg-white text-gray-700 hover:bg-united-red hover:text-white'; ?> rounded-lg font-bold shadow-lg transition">
                🔴 Man United
            </a>
        </div>

        <!-- Manchester City Staff -->
        <?php if ($club_filter === 'all' || $club_filter === 'city'): ?>
            <?php if (!empty($staff_by_club['CITY'])): ?>
                <section class="mb-16">
                    <div class="bg-gradient-to-r from-city-blue to-city-navy text-white rounded-2xl shadow-xl p-8 mb-8">
                        <div class="flex items-center justify-center space-x-4">
                            <span class="text-6xl">🔵</span>
                            <h2 class="text-4xl font-black">Manchester City Coaching Staff</h2>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php foreach ($staff_by_club['CITY'] as $staff): ?>
                            <?php 
                            $is_manager = in_array($staff['role'], $manager_roles) || 
                                         stripos($staff['role'], 'manager') !== false;
                            $card_class = $is_manager ? 'md:col-span-2 lg:col-span-2' : '';
                            $photo_height = $is_manager ? 'h-96' : 'h-64';
                            $photo_size = $is_manager ? 'text-8xl' : 'text-5xl';
                            ?>
                            <div class="<?php echo $card_class; ?> bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition">
                                <!-- Staff Photo with Frame -->
                                <div class="staff-photo-frame relative">
                                    <div class="<?php echo $photo_height; ?> bg-gradient-to-br from-city-blue to-city-navy flex items-center justify-center text-white <?php echo $photo_size; ?>">
                                        <?php 
                                        echo match(true) {
                                            stripos($staff['role'], 'manager') !== false => '👔',
                                            stripos($staff['role'], 'assistant') !== false => '📋',
                                            stripos($staff['role'], 'goalkeeper') !== false => '🧤',
                                            stripos($staff['role'], 'fitness') !== false => '💪',
                                            stripos($staff['role'], 'coach') !== false => '🎯',
                                            default => '👤'
                                        };
                                        ?>
                                    </div>
                                    <!-- Role Badge -->
                                    <div class="role-badge">
                                        <?php echo strtoupper($staff['role']); ?>
                                    </div>
                                </div>
                                
                                <!-- Staff Info -->
                                <div class="p-5">
                                    <h3 class="text-xl font-black text-gray-900 mb-2"><?php echo $staff['name']; ?></h3>
                                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                                        <div class="flex items-center">
                                            <span class="w-6">🌍</span>
                                            <span><?php echo $staff['nationality']; ?></span>
                                        </div>
                                        <?php if ($staff['join_date']): ?>
                                            <div class="flex items-center">
                                                <span class="w-6">📅</span>
                                                <span>Sejak <?php echo date('Y', strtotime($staff['join_date'])); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($staff['biography']): ?>
                                        <p class="text-sm text-gray-700 line-clamp-3">
                                            <?php echo $staff['biography']; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Manchester United Staff -->
        <?php if ($club_filter === 'all' || $club_filter === 'united'): ?>
            <?php if (!empty($staff_by_club['UNITED'])): ?>
                <section class="mb-16">
                    <div class="bg-gradient-to-r from-united-red to-red-900 text-white rounded-2xl shadow-xl p-8 mb-8">
                        <div class="flex items-center justify-center space-x-4">
                            <span class="text-6xl">🔴</span>
                            <h2 class="text-4xl font-black">Manchester United Coaching Staff</h2>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php foreach ($staff_by_club['UNITED'] as $staff): ?>
                            <?php 
                            $is_manager = in_array($staff['role'], $manager_roles) || 
                                         stripos($staff['role'], 'manager') !== false;
                            $card_class = $is_manager ? 'md:col-span-2 lg:col-span-2' : '';
                            $photo_height = $is_manager ? 'h-96' : 'h-64';
                            $photo_size = $is_manager ? 'text-8xl' : 'text-5xl';
                            ?>
                            <div class="<?php echo $card_class; ?> bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition">
                                <!-- Staff Photo with Frame -->
                                <div class="staff-photo-frame relative">
                                    <div class="<?php echo $photo_height; ?> bg-gradient-to-br from-united-red to-red-900 flex items-center justify-center text-white <?php echo $photo_size; ?>">
                                        <?php 
                                        echo match(true) {
                                            stripos($staff['role'], 'manager') !== false => '👔',
                                            stripos($staff['role'], 'assistant') !== false => '📋',
                                            stripos($staff['role'], 'goalkeeper') !== false => '🧤',
                                            stripos($staff['role'], 'fitness') !== false => '💪',
                                            stripos($staff['role'], 'coach') !== false => '🎯',
                                            default => '👤'
                                        };
                                        ?>
                                    </div>
                                    <!-- Role Badge -->
                                    <div class="role-badge">
                                        <?php echo strtoupper($staff['role']); ?>
                                    </div>
                                </div>
                                
                                <!-- Staff Info -->
                                <div class="p-5">
                                    <h3 class="text-xl font-black text-gray-900 mb-2"><?php echo $staff['name']; ?></h3>
                                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                                        <div class="flex items-center">
                                            <span class="w-6">🌍</span>
                                            <span><?php echo $staff['nationality']; ?></span>
                                        </div>
                                        <?php if ($staff['join_date']): ?>
                                            <div class="flex items-center">
                                                <span class="w-6">📅</span>
                                                <span>Sejak <?php echo date('Y', strtotime($staff['join_date'])); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($staff['biography']): ?>
                                        <p class="text-sm text-gray-700 line-clamp-3">
                                            <?php echo $staff['biography']; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Info Section -->
        <div class="mt-16 bg-gradient-to-r from-purple-600 to-purple-900 text-white rounded-2xl shadow-xl p-8 text-center">
            <h3 class="text-3xl font-bold mb-4">💼 Professional Coaching Staff</h3>
            <p class="text-lg mb-6">
                Tim pelatih dan staf profesional yang membawa kesuksesan bagi kedua klub Manchester
            </p>
            <div class="flex justify-center gap-6">
                <a href="players-public.php" class="px-8 py-3 bg-white text-purple-900 font-bold rounded-lg hover:bg-gray-100 transition">
                    👥 Lihat Pemain
                </a>
                <a href="index.php" class="px-8 py-3 bg-white/20 backdrop-blur text-white font-bold rounded-lg hover:bg-white/30 transition">
                    📰 Berita Terbaru
                </a>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex items-center justify-center space-x-2 mb-4">
                <div class="flex">
                    <div class="w-6 h-6 bg-city-blue rounded-full"></div>
                    <div class="w-6 h-6 bg-united-red rounded-full -ml-2"></div>
                </div>
                <span class="text-xl font-bold">Manchester Side</span>
            </div>
            <p class="text-gray-400 text-sm mb-4">Two Sides, One City, Endless Rivalry</p>
            <p class="text-gray-500 text-xs">&copy; 2025 Manchester Side. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>