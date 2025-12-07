<?php
/**
 * Update Staff Table - Add Missing Columns
 * Run this file once to add new columns to staff table
 */
require_once 'includes/config.php';

$db = getDB();

echo "<h2>Updating Staff Table...</h2>";

// Add birth_date column
$sql = "ALTER TABLE staff ADD COLUMN IF NOT EXISTS birth_date DATE AFTER nationality";
if ($db->query($sql)) {
    echo "<p>✅ Added birth_date column</p>";
} else {
    echo "<p>❌ Error adding birth_date: " . $db->error . "</p>";
}

// Add previous_club column
$sql = "ALTER TABLE staff ADD COLUMN IF NOT EXISTS previous_club VARCHAR(100) AFTER join_date";
if ($db->query($sql)) {
    echo "<p>✅ Added previous_club column</p>";
} else {
    echo "<p>❌ Error adding previous_club: " . $db->error . "</p>";
}

// Add achievements column
$sql = "ALTER TABLE staff ADD COLUMN IF NOT EXISTS achievements TEXT AFTER previous_club";
if ($db->query($sql)) {
    echo "<p>✅ Added achievements column</p>";
} else {
    echo "<p>❌ Error adding achievements: " . $db->error . "</p>";
}

// Add photo_url column
$sql = "ALTER TABLE staff ADD COLUMN IF NOT EXISTS photo_url VARCHAR(255) AFTER biography";
if ($db->query($sql)) {
    echo "<p>✅ Added photo_url column</p>";
} else {
    echo "<p>❌ Error adding photo_url: " . $db->error . "</p>";
}

echo "<h3>✅ Staff table updated successfully!</h3>";
echo "<p><a href='admin/staff/index.php'>Go to Staff Management</a></p>";
?>
