<?php
require_once 'includes/config.php';

$db = getDB();

echo "<h2>Fixing Database...</h2>";

// Read and execute SQL
$sql_file = __DIR__ . '/quick_fix_reactions.sql';

if (file_exists($sql_file)) {
    $sql = file_get_contents($sql_file);
    
    // Remove comments and split queries
    $sql = preg_replace('/--.*\n/', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    if ($db->multi_query($sql)) {
        echo "<p style='color:green'>✅ Database updated successfully!</p>";
        
        // Process results
        do {
            if ($result = $db->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    echo "<p>" . print_r($row, true) . "</p>";
                }
                $result->free();
            }
        } while ($db->more_results() && $db->next_result());
    } else {
        echo "<p style='color:red'>❌ Error: " . $db->error . "</p>";
    }
} else {
    echo "<p style='color:red'>File SQL not found!</p>";
}

echo "<br><a href='index.php'>Go to Homepage</a>";
echo "<br><a href='news-detail.php?slug=haaland-pecahkan-rekor-5-gol'>Test News Detail</a>";
?>