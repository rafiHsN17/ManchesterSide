<?php
/**
 * Script Otomatis Update Header & Footer
 * Jalankan sekali untuk mengupdate semua file user dan admin
 */

echo "=== UPDATE HEADER & FOOTER - MANCHESTER SIDE ===\n\n";

// Fungsi untuk extract content antara <body> dan </body>
function extractBodyContent($content) {
    if (preg_match('/<body[^>]*>(.*?)<\/body>/s', $content, $matches)) {
        return trim($matches[1]);
    }
    return null;
}

// Fungsi untuk extract PHP logic di awal file
function extractPHPLogic($content) {
    // Ambil semua PHP code sebelum HTML
    if (preg_match('/^<\?php(.*?)(?=<!DOCTYPE|<html|\?>)/s', $content, $matches)) {
        return '<?php' . $matches[1];
    }
    return '';
}

// Fungsi untuk extract page title
function extractPageTitle($content) {
    if (preg_match('/<title>(.*?)<\/title>/', $content, $matches)) {
        $title = $matches[1];
        $title = str_replace(' - Manchester Side', '', $title);
        $title = str_replace('Manchester Side - ', '', $title);
        $title = str_replace(' - Admin - Manchester Side', '', $title);
        $title = str_replace('Admin - Manchester Side', '', $title);
        return trim($title);
    }
    return '';
}

// Fungsi untuk extract custom CSS
function extractCustomCSS($content) {
    if (preg_match('/<style>(.*?)<\/style>/s', $content, $matches)) {
        return trim($matches[1]);
    }
    return '';
}

// Fungsi untuk extract custom JavaScript
function extractCustomJS($content) {
    if (preg_match_all('/<script>(.*?)<\/script>/s', $content, $matches)) {
        $js = '';
        foreach ($matches[1] as $script) {
            // Skip Tailwind config
            if (strpos($script, 'tailwind.config') === false) {
                $js .= trim($script) . "\n\n";
            }
        }
        return trim($js);
    }
    return '';
}

// USER FILES
$user_files = [
    'index.php',
    'login.php',
    'register.php',
    'profile.php',
    'news.php',
    'club.php',
    'logout.php'
];

// ADMIN FILES
$admin_files = [
    'admin/dashboard.php',
    'admin/login.php',
    'admin/logout.php',
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

$updated = 0;
$skipped = 0;
$errors = 0;

echo "📝 UPDATING USER FILES...\n\n";

foreach ($user_files as $file) {
    if (!file_exists($file)) {
        echo "⚠️  SKIP: $file (tidak ditemukan)\n";
        $skipped++;
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Skip jika sudah menggunakan include
    if (strpos($content, "include 'includes/header.php'") !== false) {
        echo "✅ SKIP: $file (sudah menggunakan includes)\n";
        $skipped++;
        continue;
    }
    
    // Backup
    $backup = $file . '.backup_' . date('YmdHis');
    file_put_contents($backup, $content);
    
    echo "🔄 PROCESSING: $file\n";
    
    try {
        $php_logic = extractPHPLogic($content);
        $page_title = extractPageTitle($content);
        $body_content = extractBodyContent($content);
        $custom_css = extractCustomCSS($content);
        $custom_js = extractCustomJS($content);
        
        if (!$body_content) {
            echo "   ⚠️  Tidak bisa extract body content, skip\n";
            $skipped++;
            continue;
        }
        
        // Buat struktur baru
        $new_content = $php_logic . "\n\n";
        
        if ($page_title) {
            $new_content .= "\$page_title = \"$page_title\";\n";
        }
        
        $new_content .= "include 'includes/header.php';\n";
        $new_content .= "?>\n\n";
        
        if ($custom_css) {
            $new_content .= "<style>\n$custom_css\n</style>\n\n";
        }
        
        $new_content .= $body_content . "\n\n";
        
        if ($custom_js) {
            $new_content .= "<script>\n$custom_js\n</script>\n\n";
        }
        
        $new_content .= "<?php include 'includes/footer.php'; ?>\n";
        
        file_put_contents($file, $new_content);
        echo "   ✅ Berhasil diupdate! (Backup: $backup)\n";
        $updated++;
        
    } catch (Exception $e) {
        echo "   ❌ ERROR: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    echo "\n";
}

echo "\n📝 UPDATING ADMIN FILES...\n\n";

foreach ($admin_files as $file) {
    if (!file_exists($file)) {
        echo "⚠️  SKIP: $file (tidak ditemukan)\n";
        $skipped++;
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Skip jika sudah menggunakan include
    if (strpos($content, "include '../includes/header.php'") !== false ||
        strpos($content, "include 'includes/header.php'") !== false) {
        echo "✅ SKIP: $file (sudah menggunakan includes)\n";
        $skipped++;
        continue;
    }
    
    // Backup
    $backup = $file . '.backup_' . date('YmdHis');
    file_put_contents($backup, $content);
    
    echo "🔄 PROCESSING: $file\n";
    
    try {
        $php_logic = extractPHPLogic($content);
        $page_title = extractPageTitle($content);
        $body_content = extractBodyContent($content);
        $custom_css = extractCustomCSS($content);
        $custom_js = extractCustomJS($content);
        
        if (!$body_content) {
            echo "   ⚠️  Tidak bisa extract body content, skip\n";
            $skipped++;
            continue;
        }
        
        // Tentukan path ke includes berdasarkan lokasi file
        $depth = substr_count($file, '/');
        $include_path = str_repeat('../', $depth - 1) . 'includes/';
        
        // Buat struktur baru
        $new_content = $php_logic . "\n\n";
        
        if ($page_title) {
            $new_content .= "\$page_title = \"$page_title\";\n";
        }
        
        $new_content .= "include '{$include_path}header.php';\n";
        $new_content .= "?>\n\n";
        
        if ($custom_css) {
            $new_content .= "<style>\n$custom_css\n</style>\n\n";
        }
        
        $new_content .= $body_content . "\n\n";
        
        if ($custom_js) {
            $new_content .= "<script>\n$custom_js\n</script>\n\n";
        }
        
        $new_content .= "<?php include '{$include_path}footer.php'; ?>\n";
        
        file_put_contents($file, $new_content);
        echo "   ✅ Berhasil diupdate! (Backup: $backup)\n";
        $updated++;
        
    } catch (Exception $e) {
        echo "   ❌ ERROR: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    echo "\n";
}

echo "\n=== SUMMARY ===\n";
echo "✅ Updated: $updated files\n";
echo "⏭️  Skipped: $skipped files\n";
echo "❌ Errors: $errors files\n";
echo "\n";

if ($updated > 0) {
    echo "🎉 Update selesai!\n";
    echo "💡 File backup tersimpan dengan extension .backup_TIMESTAMP\n";
    echo "🗑️  Hapus file backup setelah yakin tidak ada masalah\n";
}

echo "\n";
?>
