-- ========================================
-- MANCHESTER SIDE - COMPLETE DATABASE
-- Database Lengkap dengan Semua Fitur
-- ========================================

-- Drop database jika sudah ada (hati-hati!)
-- DROP DATABASE IF EXISTS manchesterside;

-- Create database
CREATE DATABASE IF NOT EXISTS manchesterside;
USE manchesterside;

-- ========================================
-- 1. CLUBS TABLE
-- ========================================
CREATE TABLE clubs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    founded_year INT NOT NULL,
    stadium_name VARCHAR(100),
    stadium_location VARCHAR(100),
    stadium_capacity INT,
    color_primary VARCHAR(7),
    color_secondary VARCHAR(7),
    history TEXT,
    achievements TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert clubs data
INSERT INTO clubs (name, full_name, code, founded_year, stadium_name, stadium_location, stadium_capacity, color_primary, color_secondary, history, achievements) VALUES
('Manchester City', 'Manchester City Football Club', 'CITY', 1880, 'Etihad Stadium', 'Manchester, England', 53400, '#6CABDD', '#1C2C5B', 
'Manchester City didirikan pada tahun 1880 sebagai St. Mark''s (West Gorton). Klub ini telah mengalami transformasi dramatis sejak diakuisisi oleh Abu Dhabi United Group pada tahun 2008. Di bawah kepemimpinan Pep Guardiola, City telah menjadi salah satu kekuatan dominan di sepak bola Eropa.',
'Premier League: 9 kali (termasuk era modern)\nFA Cup: 7 kali\nEFL Cup: 8 kali\nChampions League: 1 kali (2023)\nTreble Winner 2022/2023'),

('Manchester United', 'Manchester United Football Club', 'UNITED', 1878, 'Old Trafford', 'Manchester, England', 74310, '#DA291C', '#FBE122',
'Manchester United, juga dikenal sebagai Red Devils, adalah salah satu klub tersukses dalam sejarah sepak bola Inggris. Didirikan pada tahun 1878 sebagai Newton Heath LYR, klub ini berganti nama menjadi Manchester United pada tahun 1902. Era keemasan di bawah Sir Alex Ferguson (1986-2013) menghasilkan 13 gelar Premier League.',
'Premier League: 20 kali\nFA Cup: 12 kali\nEFL Cup: 6 kali\nChampions League: 3 kali\nTreble Winner 1998/1999');

-- ========================================
-- 2. ADMINS TABLE
-- ========================================
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('superadmin', 'admin', 'editor') DEFAULT 'admin',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert admin (password: password)
INSERT INTO admins (username, email, password, full_name, role) VALUES
('superadmin', 'admin@manchesterside.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'superadmin'),
('admin1', 'admin1@manchesterside.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin City', 'admin'),
('admin2', 'admin2@manchesterside.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin United', 'admin');

-- ========================================
-- 3. USERS TABLE
-- ========================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    bio TEXT,
    avatar VARCHAR(255),
    favorite_team ENUM('CITY', 'UNITED') DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample users (password: password)
INSERT INTO users (username, email, password, full_name, favorite_team) VALUES
('cityfan01', 'cityfan@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'City Fan 01', 'CITY'),
('unitedfan01', 'unitedfan@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'United Fan 01', 'UNITED'),
('neutralfan', 'neutral@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Neutral Fan', NULL);

-- ========================================
-- 4. ARTICLES TABLE
-- ========================================
CREATE TABLE articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt VARCHAR(500),
    image_url VARCHAR(255),
    club_id INT DEFAULT NULL,
    author_id INT NOT NULL,
    category ENUM('news', 'match', 'transfer', 'interview', 'analysis') DEFAULT 'news',
    is_published TINYINT(1) DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    reaction_like INT DEFAULT 0,
    reaction_love INT DEFAULT 0,
    reaction_wow INT DEFAULT 0,
    reaction_sad INT DEFAULT 0,
    reaction_angry INT DEFAULT 0,
    total_reactions INT DEFAULT 0,
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_published (is_published),
    INDEX idx_club (club_id),
    INDEX idx_category (category),
    FULLTEXT KEY ft_search (title, content, excerpt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample articles
INSERT INTO articles (title, slug, content, excerpt, club_id, author_id, category, is_published, is_featured, views, published_at) VALUES
('Haaland Pecahkan Rekor 5 Gol dalam Satu Pertandingan', 'haaland-pecahkan-rekor-5-gol', 
'Erling Haaland sekali lagi menunjukkan ketajamannya di depan gawang dengan mencetak 5 gol dalam satu pertandingan melawan tim rival. Pencapaian luar biasa ini membuatnya menjadi pemain ketiga dalam sejarah Premier League yang mampu mencetak lima gol dalam satu laga.\n\nPenampilan gemilang Haaland dimulai dari menit ke-8 ketika ia memanfaatkan umpan silang Kevin De Bruyne. Gol kedua dan ketiga datang sebelum turun minum, menunjukkan dominasi City di babak pertama.\n\nDi babak kedua, Haaland melengkapi hat-tricknya hanya dalam 10 menit. Gol keempat datang dari titik penalti yang dieksekusi dengan sempurna. Gol kelima yang spektakuler dari luar kotak penalti menutup pesta golnya.\n\nPep Guardiola memuji pemainnya: "Erling luar biasa. Dia pemain yang selalu lapar akan gol dan tidak pernah puas. Ini adalah performa yang akan dikenang selamanya."\n\nDengan pencapaian ini, Haaland kini telah mengumpulkan 35 gol dalam musim ini, menempatkannya sebagai top scorer Premier League.', 
'Striker Manchester City mencetak rekor fenomenal dengan 5 gol dalam satu pertandingan, menunjukkan dominasi luar biasa di Premier League.',
1, 1, 'news', 1, 1, 1543, NOW()),

('Rashford Raih Penghargaan Pemain Terbaik Bulan Ini', 'rashford-pemain-terbaik-bulan-ini',
'Marcus Rashford dinobatkan sebagai Pemain Terbaik Bulan Ini setelah tampil gemilang dengan 6 gol dan 3 assist dalam 5 pertandingan terakhir. Performa impresif striker asal Inggris ini membawa Manchester United kembali ke jalur kemenangan.\n\nPenghargaan ini adalah yang ketiga kalinya bagi Rashford musim ini, menunjukkan konsistensi luar biasa. Ia menjadi kunci utama dalam skema permainan Erik ten Hag.\n\n"Saya sangat bangga dengan pencapaian ini, tapi yang terpenting adalah kemenangan tim," ujar Rashford dalam konferensi pers.\n\nPerforma terbaiknya datang saat melawan rival kota dengan mencetak hat-trick spektakuler yang membawa United meraih kemenangan 3-1.\n\nErik ten Hag mengatakan: "Marcus adalah pemain yang sangat penting bagi kami. Dia menunjukkan mentalitas juara sejati dan dedikasi penuh untuk tim."',
'Marcus Rashford menunjukkan performa luar biasa dengan 6 gol dan 3 assist, meraih penghargaan Pemain Terbaik bulan ini untuk Manchester United.',
2, 2, 'news', 1, 1, 987, NOW()),

('Manchester Derby: Pertarungan Sengit di Old Trafford', 'manchester-derby-old-trafford',
'Pertandingan Manchester Derby selalu menjadi sorotan utama, dan pertemuan kali ini tidak mengecewakan. Pertarungan sengit di Old Trafford berakhir dengan skor 2-2 yang dramatis.\n\nManchester United unggul lebih dulu melalui gol Bruno Fernandes di menit ke-12. City menyamakan kedudukan melalui gol bunuh diri yang tidak disengaja di menit ke-35.\n\nBabak kedua menjadi lebih intens dengan kedua tim saling menyerang. Rashford mengembalikan keunggulan United di menit ke-67 dengan tendangan keras dari luar kotak penalti.\n\nNamun drama sejati terjadi di injury time ketika Phil Foden mencetak gol penyama kedudukan di menit 90+4, membuat Etihad Stadium meledak dalam perayaan.\n\nHasil imbang ini membuat kedua tim berbagi poin dan menjaga persaingan ketat di puncak klasemen.',
'Derby Manchester berakhir dramatis 2-2 di Old Trafford dengan gol injury time dari Phil Foden menyelamatkan City dari kekalahan.',
NULL, 1, 'match', 1, 1, 2341, NOW()),

('City Resmi Dapatkan Bintang Baru dari Brasil', 'city-rekrut-pemain-brasil',
'Manchester City mengumumkan rekrutmen bintang muda Brasil yang sangat berbakat dengan nilai transfer mencapai £45 juta. Pemain berusia 21 tahun ini diharapkan memperkuat lini tengah City.\n\nDirektur Olahraga City, Txiki Begiristain, mengatakan: "Kami sangat senang mendapatkan pemain muda berbakat ini. Dia memiliki potensi luar biasa dan kami yakin akan berkembang pesat di bawah Pep."\n\nPemain ini telah menjalani tes medis dan menandatangani kontrak 5 tahun. Ia akan mengenakan nomor punggung 25.\n\n"Ini impian saya sejak kecil untuk bermain di Premier League, apalagi untuk klub sebesar Manchester City," ujar sang pemain.\n\nPep Guardiola menyambut baik kedatangan pemain baru ini dan berharap ia bisa segera beradaptasi dengan gaya permainan City.',
'Manchester City resmi merekrut bintang muda Brasil dengan nilai £45 juta untuk memperkuat skuad mereka musim depan.',
1, 1, 'transfer', 1, 0, 756, NOW()),

('Ten Hag: Kami Siap Bersaing untuk Juara', 'ten-hag-siap-bersaing-juara',
'Pelatih Manchester United Erik ten Hag menegaskan timnya siap bersaing untuk gelar juara musim ini. Pernyataan ini datang setelah United meraih 5 kemenangan beruntun.\n\n"Kami telah menunjukkan kualitas dan konsistensi. Skuad ini memiliki mentalitas juara dan kami akan berjuang sampai pertandingan terakhir," tegas Ten Hag.\n\nUnited kini berada di posisi kedua klasemen dengan selisih 3 poin dari puncak. Performa solid di semua lini membuat mereka menjadi kandidat kuat juara.\n\n"Setiap pertandingan adalah final bagi kami. Kami harus tetap fokus dan tidak boleh lengah," tambah pelatih asal Belanda ini.\n\nSkuad United mendapat dorongan besar dengan kembalinya beberapa pemain kunci dari cedera.',
'Erik ten Hag optimis Manchester United bisa merebut gelar juara dengan 5 kemenangan beruntun dan performa impresif.',
2, 2, 'interview', 1, 0, 654, NOW());

-- ========================================
-- 5. ARTICLE REACTIONS TABLE
-- ========================================
CREATE TABLE article_reactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id INT NOT NULL,
    user_id INT NOT NULL,
    reaction_type ENUM('like', 'love', 'wow', 'sad', 'angry') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_article_reaction (user_id, article_id),
    INDEX idx_article (article_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 6. USER FAVORITES TABLE
-- ========================================
CREATE TABLE user_favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    article_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_article (user_id, article_id),
    INDEX idx_user (user_id),
    INDEX idx_article (article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 7. PLAYERS TABLE
-- ========================================
CREATE TABLE players (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    club_id INT NOT NULL,
    position ENUM('Goalkeeper', 'Defender', 'Midfielder', 'Forward') NOT NULL,
    jersey_number INT NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    birth_date DATE,
    height INT,
    weight INT,
    biography TEXT,
    joined_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    INDEX idx_club (club_id),
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample players
INSERT INTO players (name, club_id, position, jersey_number, nationality, birth_date, height, weight, joined_date, is_active) VALUES
-- Manchester City
('Erling Haaland', 1, 'Forward', 9, 'Norway', '2000-07-21', 194, 88, '2022-07-01', 1),
('Kevin De Bruyne', 1, 'Midfielder', 17, 'Belgium', '1991-06-28', 181, 76, '2015-08-30', 1),
('Ederson', 1, 'Goalkeeper', 31, 'Brazil', '1993-08-17', 188, 86, '2017-07-01', 1),
('Phil Foden', 1, 'Midfielder', 47, 'England', '2000-05-28', 171, 69, '2017-01-01', 1),
('Ruben Dias', 1, 'Defender', 3, 'Portugal', '1997-05-14', 187, 82, '2020-09-29', 1),

-- Manchester United
('Marcus Rashford', 2, 'Forward', 10, 'England', '1997-10-31', 180, 70, '2016-02-01', 1),
('Bruno Fernandes', 2, 'Midfielder', 8, 'Portugal', '1994-09-08', 179, 69, '2020-01-30', 1),
('Andre Onana', 2, 'Goalkeeper', 24, 'Cameroon', '1996-04-02', 190, 88, '2023-07-01', 1),
('Lisandro Martinez', 2, 'Defender', 6, 'Argentina', '1998-01-18', 175, 77, '2022-07-27', 1),
('Casemiro', 2, 'Midfielder', 18, 'Brazil', '1992-02-23', 185, 84, '2022-08-22', 1);

-- ========================================
-- 8. STAFF TABLE
-- ========================================
CREATE TABLE staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    club_id INT NOT NULL,
    role VARCHAR(100) NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    join_date DATE,
    biography TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    INDEX idx_club (club_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample staff
INSERT INTO staff (name, club_id, role, nationality, join_date, is_active) VALUES
('Pep Guardiola', 1, 'Manager', 'Spain', '2016-07-01', 1),
('Juanma Lillo', 1, 'Assistant Manager', 'Spain', '2020-06-08', 1),
('Erik ten Hag', 2, 'Manager', 'Netherlands', '2022-05-23', 1),
('Steve McClaren', 2, 'Assistant Manager', 'England', '2022-07-01', 1);

-- ========================================
-- 9. MATCHES TABLE
-- ========================================
CREATE TABLE matches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    home_team_id INT NOT NULL,
    away_team_id INT NOT NULL,
    competition VARCHAR(100) NOT NULL,
    match_date DATETIME NOT NULL,
    venue VARCHAR(100),
    home_score INT DEFAULT NULL,
    away_score INT DEFAULT NULL,
    status ENUM('scheduled', 'live', 'finished', 'postponed') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (home_team_id) REFERENCES clubs(id) ON DELETE CASCADE,
    FOREIGN KEY (away_team_id) REFERENCES clubs(id) ON DELETE CASCADE,
    INDEX idx_date (match_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample matches
INSERT INTO matches (home_team_id, away_team_id, competition, match_date, venue, home_score, away_score, status) VALUES
(1, 2, 'Premier League', '2024-03-03 16:30:00', 'Etihad Stadium', 3, 1, 'finished'),
(2, 1, 'Premier League', '2024-10-29 17:00:00', 'Old Trafford', 1, 2, 'finished'),
(1, 2, 'FA Cup Final', '2024-05-25 17:00:00', 'Wembley Stadium', 2, 1, 'finished'),
(2, 1, 'Premier League', '2025-04-06 16:00:00', 'Old Trafford', NULL, NULL, 'scheduled'),
(1, 2, 'Premier League', '2025-12-14 17:30:00', 'Etihad Stadium', NULL, NULL, 'scheduled');

-- ========================================
-- 10. SETTINGS TABLE
-- ========================================
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'Manchester Side', 'Nama website'),
('site_tagline', 'Two Sides, One City, Endless Rivalry', 'Tagline website'),
('site_email', 'info@manchesterside.com', 'Email kontak'),
('articles_per_page', '12', 'Jumlah artikel per halaman'),
('allow_comments', '1', 'Izinkan komentar (1=ya, 0=tidak)'),
('maintenance_mode', '0', 'Mode maintenance (1=aktif, 0=nonaktif)');

-- ========================================
-- 11. TRIGGERS FOR REACTIONS
-- ========================================
DELIMITER //

-- Trigger after insert reaction
CREATE TRIGGER after_reaction_insert
AFTER INSERT ON article_reactions
FOR EACH ROW
BEGIN
    UPDATE articles 
    SET 
        reaction_like = reaction_like + IF(NEW.reaction_type = 'like', 1, 0),
        reaction_love = reaction_love + IF(NEW.reaction_type = 'love', 1, 0),
        reaction_wow = reaction_wow + IF(NEW.reaction_type = 'wow', 1, 0),
        reaction_sad = reaction_sad + IF(NEW.reaction_type = 'sad', 1, 0),
        reaction_angry = reaction_angry + IF(NEW.reaction_type = 'angry', 1, 0),
        total_reactions = total_reactions + 1
    WHERE id = NEW.article_id;
END//

-- Trigger after update reaction
CREATE TRIGGER after_reaction_update
AFTER UPDATE ON article_reactions
FOR EACH ROW
BEGIN
    UPDATE articles 
    SET 
        reaction_like = reaction_like + IF(NEW.reaction_type = 'like', 1, 0) - IF(OLD.reaction_type = 'like', 1, 0),
        reaction_love = reaction_love + IF(NEW.reaction_type = 'love', 1, 0) - IF(OLD.reaction_type = 'love', 1, 0),
        reaction_wow = reaction_wow + IF(NEW.reaction_type = 'wow', 1, 0) - IF(OLD.reaction_type = 'wow', 1, 0),
        reaction_sad = reaction_sad + IF(NEW.reaction_type = 'sad', 1, 0) - IF(OLD.reaction_type = 'sad', 1, 0),
        reaction_angry = reaction_angry + IF(NEW.reaction_type = 'angry', 1, 0) - IF(OLD.reaction_type = 'angry', 1, 0)
    WHERE id = NEW.article_id;
END//

-- Trigger after delete reaction
CREATE TRIGGER after_reaction_delete
AFTER DELETE ON article_reactions
FOR EACH ROW
BEGIN
    UPDATE articles 
    SET 
        reaction_like = reaction_like - IF(OLD.reaction_type = 'like', 1, 0),
        reaction_love = reaction_love - IF(OLD.reaction_type = 'love', 1, 0),
        reaction_wow = reaction_wow - IF(OLD.reaction_type = 'wow', 1, 0),
        reaction_sad = reaction_sad - IF(OLD.reaction_type = 'sad', 1, 0),
        reaction_angry = reaction_angry - IF(OLD.reaction_type = 'angry', 1, 0),
        total_reactions = total_reactions - 1
    WHERE id = OLD.article_id;
END//

DELIMITER ;

-- ========================================
-- 12. VERIFICATION QUERIES
-- ========================================
SELECT '✅ Database created successfully!' AS Status;

SELECT 'Tables Created:' AS Info;
SHOW TABLES;

SELECT 'Sample Data:' AS Info;
SELECT COUNT(*) as total_clubs FROM clubs;
SELECT COUNT(*) as total_admins FROM admins;
SELECT COUNT(*) as total_users FROM users;
SELECT COUNT(*) as total_articles FROM articles;
SELECT COUNT(*) as total_players FROM players;
SELECT COUNT(*) as total_staff FROM staff;
SELECT COUNT(*) as total_matches FROM matches;

-- ========================================
-- SELESAI!
-- ========================================
-- Username Admin: superadmin
-- Password Admin: password
-- 
-- Username User: cityfan01 / unitedfan01
-- Password User: password
-- ========================================