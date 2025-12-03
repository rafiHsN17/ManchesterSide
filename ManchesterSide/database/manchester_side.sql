-- ========================================
-- MANCHESTER SIDE - DATABASE UPDATE
-- Features: Image Upload, Search History, Password Reset
-- No Comments Feature
-- ========================================

USE manchester_side;

-- 1. Ensure image_url column exists in articles
ALTER TABLE articles 
ADD COLUMN IF NOT EXISTS image_url VARCHAR(255) AFTER excerpt;

-- 2. Create Search History Table
CREATE TABLE IF NOT EXISTS search_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    search_query VARCHAR(255) NOT NULL,
    results_count INT DEFAULT 0,
    club_filter VARCHAR(20) DEFAULT NULL,
    category_filter VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, created_at DESC),
    INDEX idx_query (search_query)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create Password Reset Tokens Table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    admin_id INT DEFAULT NULL,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    used_at DATETIME DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_email (email),
    INDEX idx_expires (expires_at),
    INDEX idx_used (used)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Create Email Logs Table (untuk tracking email)
CREATE TABLE IF NOT EXISTS email_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipient_email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    email_type ENUM('password_reset', 'welcome', 'notification') NOT NULL,
    status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
    error_message TEXT DEFAULT NULL,
    sent_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (recipient_email),
    INDEX idx_type (email_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Add settings for email configuration
INSERT INTO settings (setting_key, setting_value) VALUES
('smtp_host', 'smtp.gmail.com'),
('smtp_port', '587'),
('smtp_username', 'your-email@gmail.com'),
('smtp_password', ''),
('smtp_from_email', 'noreply@manchesterside.com'),
('smtp_from_name', 'Manchester Side'),
('site_url', 'http://localhost/manchesterside')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- 6. Create stored procedure to clean old password reset tokens
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS cleanup_expired_tokens()
BEGIN
    DELETE FROM password_reset_tokens 
    WHERE expires_at < NOW() 
    OR (used = 1 AND used_at < DATE_SUB(NOW(), INTERVAL 7 DAY));
END //

DELIMITER ;

-- 7. Create event to auto-cleanup tokens (runs daily)
CREATE EVENT IF NOT EXISTS daily_token_cleanup
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO CALL cleanup_expired_tokens();

-- 8. Add sample data for testing
-- Insert sample search history for existing users
INSERT INTO search_history (user_id, search_query, results_count, club_filter) VALUES
(2, 'haaland', 3, 'city'),
(2, 'transfer news', 5, NULL),
(3, 'manchester derby', 2, NULL),
(3, 'rashford', 4, 'united');

-- 9. Update articles to have some sample images
UPDATE articles 
SET image_url = CASE 
    WHEN club_id = 1 THEN 'uploads/articles/city-default.jpg'
    WHEN club_id = 2 THEN 'uploads/articles/united-default.jpg'
    ELSE 'uploads/articles/default.jpg'
END
WHERE image_url IS NULL;

SELECT '✅ Database updated successfully!' AS Status;
SELECT '✅ Features added: Image Upload, Search History, Password Reset' AS Features;
SELECT '❌ Comments feature removed as requested' AS Note;