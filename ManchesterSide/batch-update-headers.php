<?php
/**
 * Batch Update Script - Replace Headers and Footers
 * Mengupdate semua file untuk menggunakan header dan footer yang konsisten
 */

echo "===========================================\n";
echo "Manchester Side - Batch Header & Footer Update\n";
echo "===========================================\n\n";

// Daftar file yang akan diupdate (kecuali login.php dan register.php)
$files_to_update = [
    'index.php',
    'news.php',
    'club.php',
    'club-full.php',
    'profile.php',
    'standings.php',
    'tentang-kami.php',
    'profil-klub.php',
];

$success_count = 0;
$skip_count = 0;
$error_count = 0;

foreach ($files_to_update as $file) {
    if (!file_exists($file)) {
        echo "⚠️  File tidak ditemukan: $file\n";
        $error_count++;
        continue;
    }

    echo "📝 Memproses: $file ... ";

    $content = file_get_contents($file);
    $original_content = $content;
    $changed = false;

    // Pattern untuk navbar - lebih fleksibel
    $nav_patterns = [
        // Pattern 1: Full nav dengan Navigation comment
        '/<\!-- Navigation -->\s*<nav\s+class="[^"]*"[^>]*>.*?<\/nav>/s',
        // Pattern 2: Nav tanpa comment
        '/<nav\s+class="bg-white shadow-lg[^"]*"[^>]*>.*?<\/nav>/s',
    ];

    foreach ($nav_patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, '<?php include \'includes/header.php\'; ?>', $content, 1);
            $changed = true;
            break;
        }
    }

    // Pattern untuk footer - lebih fleksibel
    $footer_patterns = [
        // Pattern 1: Full footer dengan Footer comment
        '/<\!-- Footer -->\s*<footer\s+class="[^"]*"[^>]*>.*?<\/footer>/s',
        // Pattern 2: Footer tanpa comment
        '/<footer\s+class="bg-gray-900[^"]*"[^>]*>.*?<\/footer>/s',
    ];

    foreach ($footer_patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, '<?php include \'includes/footer.php\'; ?>', $content, 1);
            $changed = true;
            break;
        }
    }

    // Check if content changed
    if ($changed && $content !== $original_content) {
        // Backup original file
        $backup_file = $file . '.backup';
        file_put_contents($backup_file, $original_content);

        // Write updated content
        file_put_contents($file, $content);
        
        echo "✅ Berhasil (backup: $backup_file)\n";
        $success_count++;
    } else {
        echo "⏭️  Sudah menggunakan include atau tidak ada perubahan\n";
        $skip_count++;
    }
}

echo "\n===========================================\n";
echo "Update selesai!\n";
echo "✅ Berhasil diupdate: $success_count file\n";
echo "⏭️  Dilewati: $skip_count file\n";
echo "❌ Error: $error_count file\n";
echo "===========================================\n\n";

echo "📋 Status File:\n";
foreach ($files_to_update as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $has_header = strpos($content, "include 'includes/header.php'") !== false;
        $has_footer = strpos($content, "include 'includes/footer.php'") !== false;
        
        $status = '';
        if ($has_header && $has_footer) {
            $status = '✅ Header & Footer OK';
        } elseif ($has_header) {
            $status = '⚠️  Header OK, Footer belum';
        } elseif ($has_footer) {
            $status = '⚠️  Footer OK, Header belum';
        } else {
            $status = '❌ Belum menggunakan include';
        }
        
        echo "  $file: $status\n";
    }
}

echo "\n💡 Catatan:\n";
echo "- File backup disimpan dengan ekstensi .backup\n";
echo "- Login.php dan register.php TIDAK diupdate (sesuai permintaan)\n";
echo "- Silakan test semua halaman di browser\n";
echo "- Hapus file .backup setelah yakin update berhasil\n\n";
?>
