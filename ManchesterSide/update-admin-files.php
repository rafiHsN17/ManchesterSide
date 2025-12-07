<?php
/**
 * Script untuk update semua file admin agar menggunakan header/footer includes
 */

echo "=== UPDATE ADMIN FILES ===\n\n";

$files = [
    'admin/dashboard.php',
    'admin/settings.php',
    'admin/article/index.php',
    'admin/article/create.php',
    'admin/article/edit.php',
    'admin/players/index.php',
    'admin/players/create.php',
    'admin/players/edit.php',
    'admin/staff/index.php',
    'admin/staff/create.php',
    'admin/staff/edit.php',
    'admin/users/index.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "⚠️  SKIP: $file (tidak ditemukan)\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Skip jika sudah menggunakan include header
    if (strpos($content, "include '../includes/header.php'") !== false ||
        strpos($content, "include 'includes/header.php'") !== false) {
        echo "✅ SKIP: $file (sudah menggunakan includes)\n";
        continue;
    }
    
    // Backup
    $backup = $file . '.backup';
    file_put_contents($backup, $content);
    
    echo "🔄 PROCESSING: $file\n";
    
    // Cek apakah file menggunakan sidebar lama
    if (strpos($content, '<!-- Sidebar') !== false || strpos($content, '<aside class="w-64') !== false) {
        echo "   📝 File menggunakan sidebar lama, perlu update manual\n";
        echo "   💡 Gunakan admin/includes/header.php dan admin/includes/footer.php\n";
    }
    
    echo "\n";
}

echo "\n✅ Scan selesai!\n";
echo "📋 File yang perlu diupdate manual:\n";
echo "   - File dengan sidebar lama\n";
echo "   - Ganti dengan: include '../includes/header.php' dan include '../includes/footer.php'\n";
?>
