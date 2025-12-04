<?php
/**
 * Manchester Side - Database Configuration
 * Core configuration file untuk koneksi database dan setting global
 */

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========================================
// DATABASE CONFIGURATION
// ========================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Kosongkan jika tidak ada password
define('DB_NAME', 'manchesterside');

// ========================================
// SITE CONFIGURATION
// ========================================
define('SITE_NAME', 'manchesterside');
define('SITE_TAGLINE', 'Two Sides, One City, Endless Rivalry');
define('SITE_URL', 'http://localhost/manchesterside'); // Sesuaikan dengan URL Anda
define('SITE_EMAIL', 'info@manchesterside.com');

// ========================================
// PATH CONFIGURATION
// ========================================
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('ASSETS_URL', SITE_URL . '/assets/');

// ========================================
// CLUB CONSTANTS
// ========================================
define('CLUB_CITY', 'CITY');
define('CLUB_UNITED', 'UNITED');

// Club Colors
define('COLOR_CITY_PRIMARY', '#6CABDD');
define('COLOR_CITY_SECONDARY', '#1C2C5B');
define('COLOR_UNITED_PRIMARY', '#DA291C');
define('COLOR_UNITED_SECONDARY', '#FBE122');

// ========================================
// PAGINATION & LIMITS
// ========================================
define('ARTICLES_PER_PAGE', 12);
define('PLAYERS_PER_PAGE', 20);
define('COMMENTS_PER_PAGE', 10);

// ========================================
// FILE UPLOAD LIMITS
// ========================================
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']);

// ========================================
// DATABASE CONNECTION
// ========================================
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        
        // Set charset ke utf8mb4 untuk support emoji dan karakter khusus
        $this->connection->set_charset("utf8mb4");
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialize
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Get database connection
function getDB() {
    return Database::getInstance()->getConnection();
}

// ========================================
// HELPER FUNCTIONS
// ========================================

/**
 * Sanitize input data
 */
function sanitize($data) {
    $db = getDB();
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $db->real_escape_string($data);
}

/**
 * Generate slug from title
 */
function generateSlug($text) {
    // Replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    
    // Transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    
    // Remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    
    // Trim
    $text = trim($text, '-');
    
    // Remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    
    // Lowercase
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $user_id = $_SESSION['user_id'];
    
    $stmt = $db->prepare("SELECT id, username, email, full_name, favorite_team, avatar FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Get current admin data
 */
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $admin_id = $_SESSION['admin_id'];
    
    $stmt = $db->prepare("SELECT id, username, email, full_name, role FROM admins WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Redirect to another page
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Format date to Indonesian
 */
function formatDateIndo($date) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $timestamp = strtotime($date);
    $tanggal = date('d', $timestamp);
    $bulan_num = date('n', $timestamp);
    $tahun = date('Y', $timestamp);
    
    return $tanggal . ' ' . $bulan[$bulan_num] . ' ' . $tahun;
}

/**
 * Time ago function (contoh: "2 jam yang lalu")
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return $diff . ' detik yang lalu';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' menit yang lalu';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' jam yang lalu';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' hari yang lalu';
    } else {
        return formatDateIndo($datetime);
    }
}

/**
 * Get club color by code
 */
function getClubColor($club_code) {
    if ($club_code === CLUB_CITY) {
        return COLOR_CITY_PRIMARY;
    } elseif ($club_code === CLUB_UNITED) {
        return COLOR_UNITED_PRIMARY;
    }
    return '#6b7280'; // Default gray
}

/**
 * Truncate text
 */
function truncateText($text, $length = 150) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

/**
 * Hash password using bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

/**
 * Get flash message and clear it
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Check if favorite team is set
 */
function hasFavoriteTeam() {
    $user = getCurrentUser();
    return $user && !empty($user['favorite_team']);
}

/**
 * Get user's favorite team color
 */
function getUserThemeColor() {
    $user = getCurrentUser();
    if ($user && $user['favorite_team']) {
        return getClubColor($user['favorite_team']);
    }
    return '#6b7280'; // Default
}

/**
 * Upload image file
 */
function uploadImage($file, $folder = 'articles') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large. Max 5MB'];
    }
    
    // Check file type
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, WEBP allowed'];
    }
    
    // Create upload directory if not exists
    $upload_dir = UPLOAD_PATH . $folder . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'url' => UPLOAD_URL . $folder . '/' . $filename
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Get club emoji
 */
function getClubEmoji($club_code) {
    return $club_code === CLUB_CITY ? '🔵' : '🔴';
}

/**
 * Format number Indonesian style
 */
function formatNumber($number) {
    return number_format($number, 0, ',', '.');
}

// ========================================
// ERROR HANDLING (Development Mode)
// ========================================
// Set to false in production
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

/**
 * Get article image URL with fallback
 */
function getArticleImage($image_url, $club_code = null) {
    // Default images based on club
    $default_images = [
        'CITY' => 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=800&q=80', // City blue stadium
        'UNITED' => 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?w=800&q=80', // United red stadium
        'GENERAL' => 'https://images.unsplash.com/photo-1529900748604-07564a03e7a6?w=800&q=80' // Football generic
    ];
    
    // If image_url exists and file is accessible
    if (!empty($image_url)) {
        $file_path = __DIR__ . '/../' . $image_url;
        if (file_exists($file_path)) {
            return SITE_URL . '/' . $image_url;
        }
    }
    
    // Return default based on club
    if ($club_code === 'CITY') {
        return $default_images['CITY'];
    } elseif ($club_code === 'UNITED') {
        return $default_images['UNITED'];
    } else {
        return $default_images['GENERAL'];
    }
}

/**
 * Get club logo URL
 */
function getClubLogo($club_code) {
    $logos = [
        'CITY' => 'https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg',
        'UNITED' => 'https://upload.wikimedia.org/wikipedia/en/7/7a/Manchester_United_FC_crest.svg'
    ];
    
    return $logos[$club_code] ?? '';
}

/**
 * Get club social media links
 */
function getClubSocialMedia($club_code) {
    $social = [
        'CITY' => [
            'facebook' => 'https://www.facebook.com/mancity',
            'twitter' => 'https://twitter.com/ManCity',
            'instagram' => 'https://www.instagram.com/mancity/',
            'youtube' => 'https://www.youtube.com/mcfcofficial'
        ],
        'UNITED' => [
            'facebook' => 'https://www.facebook.com/manchesterunited',
            'twitter' => 'https://twitter.com/ManUtd',
            'instagram' => 'https://www.instagram.com/manchesterunited/',
            'youtube' => 'https://www.youtube.com/user/manchesterunited'
        ]
    ];
    
    return $social[$club_code] ?? [];

    /**
 * Get club logo HTML img tag
 */
function getClubLogoImg($club_code, $size = 'w-6 h-6') {
    $logos = [
        'CITY' => 'https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg',
        'UNITED' => 'https://upload.wikimedia.org/wikipedia/en/7/7a/Manchester_United_FC_crest.svg'
    ];
    
    $club_names = [
        'CITY' => 'Manchester City',
        'UNITED' => 'Manchester United'
    ];
    
    if (!isset($logos[$club_code])) {
        return '';
    }
    
    return '<img src="' . $logos[$club_code] . '" alt="' . $club_names[$club_code] . '" class="' . $size . ' object-contain inline-block">';
}

/**
 * Display club logo inline
 */
function displayClubLogo($club_code, $size_class = 'w-6 h-6') {
    return getClubLogoImg($club_code, $size_class);
}
}

// ========================================
// TIMEZONE
// ========================================
date_default_timezone_set('Asia/Jakarta');
?>