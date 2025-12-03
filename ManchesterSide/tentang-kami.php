<?php
/**
 * Manchester Side - Tentang Kami
 */
require_once 'includes/config.php';

$current_user = getCurrentUser();

// Admin profiles
$admins = [
    [
        'name' => 'Admin 1',
        'role' => 'Founder & Lead Developer',
        'photo' => 'https://ui-avatars.com/api/?name=Admin+1&size=300&background=6CABDD&color=fff&bold=true',
        'bio' => 'Penggemar berat Manchester City sejak 2010. Bertanggung jawab atas pengembangan dan pemeliharaan website.',
        'favorite_team' => 'CITY',
        'expertise' => ['Web Development', 'Database Management', 'Content Strategy'],
        'social' => [
            'email' => 'admin1@manchesterside.com',
            'twitter' => '#',
            'linkedin' => '#'
        ]
    ],
    [
        'name' => 'Admin 2',
        'role' => 'Content Manager & Designer',
        'photo' => 'https://ui-avatars.com/api/?name=Admin+2&size=300&background=DA291C&color=fff&bold=true',
        'bio' => 'Supporter fanatik Manchester United. Mengelola konten berita dan desain visual website.',
        'favorite_team' => 'UNITED',
        'expertise' => ['Content Writing', 'UI/UX Design', 'Social Media'],
        'social' => [
            'email' => 'admin2@manchesterside.com',
            'twitter' => '#',
            'linkedin' => '#'
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Manchester Side</title>
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

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-city-blue via-purple-600 to-united-red text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl md:text-6xl font-black mb-6">
                Tentang Manchester Side
            </h1>
            <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto leading-relaxed">
                Platform berita terpercaya untuk fans Manchester City dan Manchester United di Indonesia
            </p>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Mission & Vision -->
        <div class="mb-16">
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        🎯 Visi & Misi Kami
                    </h2>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Vision -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-8">
                        <div class="text-4xl mb-4">🌟</div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Visi</h3>
                        <p class="text-gray-700 leading-relaxed">
                            Menjadi platform berita sepak bola terdepan di Indonesia yang menyajikan informasi 
                            akurat, objektif, dan terkini tentang Manchester City dan Manchester United, 
                            serta menjembatani kedua kubu fans dengan konten berkualitas.
                        </p>
                    </div>

                    <!-- Mission -->
                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-8">
                        <div class="text-4xl mb-4">🎯</div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Misi</h3>
                        <ul class="space-y-3 text-gray-700">
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <span>Menyediakan berita sepak bola yang faktual dan terpercaya</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <span>Menghormati kedua kubu fans tanpa bias</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <span>Membangun komunitas pecinta sepak bola yang positif</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <span>Update cepat untuk setiap berita dan pertandingan</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Why This Website -->
                <div class="mt-12 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                        💡 Mengapa Manchester Side?
                    </h3>
                    <div class="prose prose-lg max-w-none text-gray-700">
                        <p class="mb-4">
                            <strong>Manchester Side</strong> lahir dari kecintaan mendalam terhadap sepak bola, 
                            khususnya rivalitas klasik antara Manchester City dan Manchester United. Kami menyadari 
                            bahwa fans Indonesia membutuhkan platform yang menyajikan berita kedua klub secara 
                            <strong>seimbang, objektif, dan berkualitas</strong>.
                        </p>
                        <p class="mb-4">
                            Di tengah banyaknya informasi yang tersebar di media sosial, kami hadir untuk memberikan 
                            <strong>sumber berita terpercaya</strong> dengan liputan mendalam tentang:
                        </p>
                        <ul class="list-disc list-inside space-y-2 mb-4">
                            <li>Hasil pertandingan dan analisis taktik</li>
                            <li>Transfer pemain dan rumor pasar</li>
                            <li>Profil pemain dan staff pelatih</li>
                            <li>Sejarah dan rivalitas Manchester Derby</li>
                            <li>Opini dan diskusi konstruktif</li>
                        </ul>
                        <p>
                            Kami percaya bahwa <strong>rivalitas adalah keindahan sepak bola</strong>, dan melalui 
                            Manchester Side, kami ingin memfasilitasi diskusi yang sehat dan sportif antara fans 
                            kedua kubu. <em>Two Sides, One City, Endless Rivalry</em> - itulah semangat kami.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Team -->
        <div class="mb-16">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    👥 Tim Kami
                </h2>
                <p class="text-xl text-gray-600">
                    Dikelola oleh fans sejati dari kedua kubu
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <?php foreach ($admins as $admin): ?>
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition">
                        <!-- Header -->
                        <div class="h-32 bg-gradient-to-br from-<?php echo $admin['favorite_team'] === 'CITY' ? 'city-blue' : 'united-red'; ?> to-<?php echo $admin['favorite_team'] === 'CITY' ? 'city-navy' : 'red'; ?>-900"></div>
                        
                        <!-- Content -->
                        <div class="p-8">
                            <!-- Avatar -->
                            <div class="flex justify-center -mt-20 mb-6">
                                <img 
                                    src="<?php echo $admin['photo']; ?>" 
                                    alt="<?php echo $admin['name']; ?>"
                                    class="w-32 h-32 rounded-full border-4 border-white shadow-xl object-cover"
                                >
                            </div>

                            <!-- Info -->
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">
                                    <?php echo $admin['name']; ?>
                                </h3>
                                <p class="text-<?php echo $admin['favorite_team'] === 'CITY' ? 'city-blue' : 'united-red'; ?> font-semibold mb-4">
                                    <?php echo $admin['role']; ?>
                                </p>
                                <div class="inline-block px-4 py-2 bg-<?php echo $admin['favorite_team'] === 'CITY' ? 'city-blue' : 'united-red'; ?>/10 rounded-full">
                                    <span class="text-sm font-bold">
                                        <?php echo getClubEmoji($admin['favorite_team']); ?>
                                        <?php echo $admin['favorite_team'] === 'CITY' ? 'Man City Fan' : 'Man United Fan'; ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Bio -->
                            <p class="text-gray-600 text-center mb-6 leading-relaxed">
                                <?php echo $admin['bio']; ?>
                            </p>

                            <!-- Expertise -->
                            <div class="mb-6">
                                <p class="text-sm font-semibold text-gray-700 mb-3 text-center">Keahlian:</p>
                                <div class="flex flex-wrap justify-center gap-2">
                                    <?php foreach ($admin['expertise'] as $skill): ?>
                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">
                                            <?php echo $skill; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Social Media -->
                            <div class="flex justify-center gap-3 pt-6 border-t border-gray-200">
                                <a href="mailto:<?php echo $admin['social']['email']; ?>" class="w-10 h-10 bg-gray-100 hover:bg-blue-500 hover:text-white rounded-full flex items-center justify-center transition">
                                    <span>📧</span>
                                </a>
                                <a href="<?php echo $admin['social']['twitter']; ?>" class="w-10 h-10 bg-gray-100 hover:bg-sky-500 hover:text-white rounded-full flex items-center justify-center transition">
                                    <span>🐦</span>
                                </a>
                                <a href="<?php echo $admin['social']['linkedin']; ?>" class="w-10 h-10 bg-gray-100 hover:bg-blue-700 hover:text-white rounded-full flex items-center justify-center transition">
                                    <span>💼</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Features -->
        <div class="mb-16">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    ✨ Fitur Unggulan
                </h2>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-lg p-6 text-center hover:shadow-xl transition">
                    <div class="text-5xl mb-4">📰</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Berita Terkini</h3>
                    <p class="text-gray-600">Update berita real-time dari kedua klub dengan sumber terpercaya</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 text-center hover:shadow-xl transition">
                    <div class="text-5xl mb-4">📊</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Statistik Lengkap</h3>
                    <p class="text-gray-600">Data klasemen, jadwal, dan head-to-head Manchester Derby</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 text-center hover:shadow-xl transition">
                    <div class="text-5xl mb-4">👥</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Profil Pemain</h3>
                    <p class="text-gray-600">Informasi lengkap tentang skuad dan staff kedua tim</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 text-center hover:shadow-xl transition">
                    <div class="text-5xl mb-4">❤️</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Fitur Favorit</h3>
                    <p class="text-gray-600">Simpan dan kelola berita favorit Anda dengan mudah</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 text-center hover:shadow-xl transition">
                    <div class="text-5xl mb-4">👍</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Sistem Reaksi</h3>
                    <p class="text-gray-600">Berikan reaksi pada berita dan lihat opini komunitas</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 text-center hover:shadow-xl transition">
                    <div class="text-5xl mb-4">📱</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Mobile Friendly</h3>
                    <p class="text-gray-600">Akses dari perangkat apapun dengan tampilan responsif</p>
                </div>
            </div>
        </div>

        <!-- Contact CTA -->
        <div class="bg-gradient-to-r from-city-blue to-united-red rounded-2xl shadow-2xl p-12 text-white text-center">
            <h2 class="text-3xl font-bold mb-4">
                Punya Saran atau Masukan?
            </h2>
            <p class="text-xl mb-8">
                Kami selalu terbuka untuk feedback dari komunitas
            </p>
            <div class="flex justify-center gap-4">
                <a href="mailto:info@manchesterside.com" class="px-8 py-4 bg-white text-gray-900 font-bold rounded-lg hover:bg-gray-100 transition">
                    📧 Hubungi Kami
                </a>
                <a href="index.php" class="px-8 py-4 bg-white/20 hover:bg-white/30 font-bold rounded-lg transition backdrop-blur-sm">
                    🏠 Kembali ke Beranda
                </a>
            </div>
        </div>

    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>