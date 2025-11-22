-- ========================================
-- MANCHESTER SIDE DATABASE SCHEMA
-- Website Berita Man City & Man United
-- ========================================

-- Create Database
CREATE DATABASE IF NOT EXISTS manchester_side;
USE manchester_side;

-- ========================================
-- 1. TABEL CLUBS
-- ========================================
CREATE TABLE clubs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE, -- 'CITY' atau 'UNITED'
    full_name VARCHAR(150) NOT NULL,
    founded_year INT NOT NULL,
    stadium_name VARCHAR(100) NOT NULL,
    stadium_capacity INT NOT NULL,
    stadium_location VARCHAR(200),
    history TEXT,
    achievements TEXT,
    color_primary VARCHAR(7) NOT NULL, -- Hex color
    color_secondary VARCHAR(7) NOT NULL,
    logo_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Data Clubs
INSERT INTO clubs (name, code, full_name, founded_year, stadium_name, stadium_capacity, stadium_location, history, achievements, color_primary, color_secondary) VALUES
('Manchester City', 'CITY', 'Manchester City Football Club', 1880, 'Etihad Stadium', 53400, 'Rowsley Street, Manchester M11 3FF', 
'Manchester City didirikan pada tahun 1880 sebagai St. Mark\'s (West Gorton). Klub ini mengalami transformasi besar sejak diakuisisi oleh City Football Group pada 2008, menjadi salah satu klub terkuat di Eropa.', 
'Premier League: 9 kali (termasuk 4 berturut-turut), FA Cup: 7 kali, UEFA Champions League: 1 kali (2023), FIFA Club World Cup: 1 kali',
'#6CABDD', '#1C2C5B'),

('Manchester United', 'UNITED', 'Manchester United Football Club', 1878, 'Old Trafford', 74140, 'Sir Matt Busby Way, Manchester M16 0RA',
'Manchester United didirikan sebagai Newton Heath LYR FC pada 1878. Dikenal sebagai "Red Devils", United adalah salah satu klub tersukses dalam sejarah sepak bola Inggris dengan era keemasan di bawah Sir Alex Ferguson.',
'Premier League: 20 kali, FA Cup: 12 kali, UEFA Champions League: 3 kali, FIFA Club World Cup: 1 kali',
'#DA291C', '#FBE122');

-- ========================================
-- 2. TABEL USERS
-- ========================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Hashed with bcrypt
    full_name VARCHAR(100),
    favorite_team VARCHAR(10), -- 'CITY', 'UNITED', atau NULL
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    bio TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (favorite_team) REFERENCES clubs(code) ON DELETE SET NULL
);

-- Insert Sample Users
INSERT INTO users (username, email, password, full_name, favorite_team) VALUES
('admin', 'admin@manchesterside.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Manchester Side', NULL),
('cityfan01', 'cityfan@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Blue', 'CITY'),
('unitedfan01', 'unitedfan@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Red', 'UNITED');
-- Password default: "password"

-- ========================================
-- 3. TABEL ADMINS
-- ========================================
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'editor', 'writer') DEFAULT 'writer',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Insert Admin
INSERT INTO admins (username, email, password, full_name, role) VALUES
('superadmin', 'admin@manchesterside.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'super_admin'),
('editor_city', 'editor.city@manchesterside.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Editor City', 'editor');
-- Password: "password"

-- ========================================
-- 4. TABEL PLAYERS
-- ========================================
CREATE TABLE players (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    club_id INT NOT NULL,
    position ENUM('Goalkeeper', 'Defender', 'Midfielder', 'Forward') NOT NULL,
    jersey_number INT NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    birth_date DATE,
    height INT, -- in cm
    weight INT, -- in kg
    biography TEXT,
    photo_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    joined_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
);

-- Insert Sample Players - Manchester City
INSERT INTO players (name, club_id, position, jersey_number, nationality, birth_date, height, weight, biography, joined_date) VALUES
('Ederson Moraes', 1, 'Goalkeeper', 31, 'Brazil', '1993-08-17', 188, 86, 'Kiper andalan Manchester City yang dikenal dengan kemampuan passing dan reflexnya yang luar biasa.', '2017-07-01'),
('Ruben Dias', 1, 'Defender', 3, 'Portugal', '1997-05-14', 187, 82, 'Bek tengah yang menjadi pilar pertahanan City sejak kedatangannya dari Benfica.', '2020-09-29'),
('Kyle Walker', 1, 'Defender', 2, 'England', '1990-05-28', 178, 83, 'Bek kanan dengan kecepatan luar biasa dan pengalaman internasional.', '2017-07-14'),
('Kevin De Bruyne', 1, 'Midfielder', 17, 'Belgium', '1991-06-28', 181, 76, 'Playmaker brilian yang dianggap sebagai salah satu gelandang terbaik di dunia.', '2015-08-30'),
('Rodri', 1, 'Midfielder', 16, 'Spain', '1996-06-22', 191, 82, 'Gelandang bertahan yang menjadi metronom permainan Manchester City.', '2019-07-04'),
('Phil Foden', 1, 'Midfielder', 47, 'England', '2000-05-28', 171, 69, 'Produk akademi City yang berkembang menjadi bintang tim utama.', '2017-01-01'),
('Erling Haaland', 1, 'Forward', 9, 'Norway', '2000-07-21', 194, 88, 'Striker fenomenal dengan kemampuan mencetak gol yang luar biasa.', '2022-07-01'),
('Jack Grealish', 1, 'Forward', 10, 'England', '1995-09-10', 180, 76, 'Winger kreatif dengan kemampuan dribbling yang mumpuni.', '2021-08-05');

-- Insert Sample Players - Manchester United
INSERT INTO players (name, club_id, position, jersey_number, nationality, birth_date, height, weight, biography, joined_date) VALUES
('Andre Onana', 2, 'Goalkeeper', 24, 'Cameroon', '1996-04-02', 190, 82, 'Kiper modern dengan kemampuan bermain dengan kaki yang baik.', '2023-07-20'),
('Lisandro Martinez', 2, 'Defender', 6, 'Argentina', '1998-01-18', 175, 78, 'Bek tengah tangguh yang menjadi juara Piala Dunia 2022 bersama Argentina.', '2022-07-27'),
('Raphael Varane', 2, 'Defender', 19, 'France', '1993-04-25', 191, 81, 'Bek berpengalaman dengan segudang trofi di Real Madrid.', '2021-08-14'),
('Diogo Dalot', 2, 'Defender', 20, 'Portugal', '1999-03-18', 183, 78, 'Bek kanan yang berkembang pesat menjadi pilihan utama.', '2018-06-06'),
('Bruno Fernandes', 2, 'Midfielder', 8, 'Portugal', '1994-09-08', 179, 69, 'Kapten dan playmaker United dengan kontribusi gol dan assist konsisten.', '2020-01-30'),
('Casemiro', 2, 'Midfielder', 18, 'Brazil', '1992-02-23', 185, 84, 'Gelandang bertahan berpengalaman dari Real Madrid.', '2022-08-22'),
('Marcus Rashford', 2, 'Forward', 10, 'England', '1997-10-31', 180, 70, 'Produk akademi United yang menjadi ujung tombak serangan tim.', '2016-01-01'),
('Rasmus Hojlund', 2, 'Forward', 11, 'Denmark', '2003-02-04', 191, 87, 'Striker muda berbakat dengan masa depan cerah.', '2023-08-05');

-- ========================================
-- 5. TABEL STAFF
-- ========================================
CREATE TABLE staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    club_id INT NOT NULL,
    role VARCHAR(100) NOT NULL, -- 'Manager', 'Assistant Coach', 'Fitness Coach', etc
    nationality VARCHAR(50) NOT NULL,
    join_date DATE,
    biography TEXT,
    photo_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
);

-- Insert Staff
INSERT INTO staff (name, club_id, role, nationality, join_date, biography) VALUES
('Pep Guardiola', 1, 'Manager', 'Spain', '2016-07-01', 'Manajer legendaris dengan filosofi permainan yang revolusioner. Telah membawa City meraih berbagai trofi termasuk treble pada 2023.'),
('Juanma Lillo', 1, 'Assistant Manager', 'Spain', '2020-06-08', 'Asisten manajer berpengalaman yang menjadi tangan kanan Guardiola.'),

('Erik ten Hag', 2, 'Manager', 'Netherlands', '2022-05-18', 'Manajer yang sukses di Ajax dan kini memimpin proses rebuilding Manchester United.'),
('Mitchell van der Gaag', 2, 'Assistant Manager', 'Netherlands', '2022-05-18', 'Asisten pelatih yang mengikuti Ten Hag dari Ajax ke Manchester United.');

-- ========================================
-- 6. TABEL ARTICLES
-- ========================================
CREATE TABLE articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt VARCHAR(500),
    image_url VARCHAR(255),
    club_id INT, -- NULL jika berita umum/derby
    author_id INT NOT NULL,
    category ENUM('news', 'match', 'transfer', 'interview', 'analysis') DEFAULT 'news',
    is_published TINYINT(1) DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_published (is_published, published_at)
);

-- Insert Sample Articles
INSERT INTO articles (title, slug, content, excerpt, club_id, author_id, category, is_published, is_featured, published_at) VALUES
('Haaland Pecahkan Rekor Baru dengan 5 Gol dalam Seminggu', 'haaland-pecahkan-rekor-5-gol', 
'Erling Haaland kembali menunjukkan performa luar biasa dengan mencetak 5 gol dalam 2 pertandingan terakhir Manchester City. Striker asal Norwegia ini membuktikan dirinya sebagai salah satu penyerang terbaik di dunia saat ini.

Dalam laga melawan Arsenal, Haaland mencetak hat-trick yang membantu City meraih kemenangan 3-1. Penampilannya tidak berhenti di situ, ia kembali mencetak 2 gol dalam pertandingan berikutnya melawan Newcastle United.

Pep Guardiola memuji performa pemainnya: "Erling adalah pemain yang luar biasa. Kemampuannya mencetak gol dari berbagai situasi membuat perbedaan besar bagi tim kami."

Dengan pencapaian ini, Haaland kini telah mencetak 18 gol dalam 12 pertandingan musim ini, angka yang fantastis untuk ukuran striker manapun.',
'Erling Haaland mencetak 5 gol dalam 2 pertandingan terakhir, membuktikan dirinya sebagai mesin gol Manchester City.',
1, 1, 'news', 1, 1, NOW()),

('Guardiola Puji Performa De Bruyne Setelah Comeback', 'guardiola-puji-de-bruyne-comeback',
'Pep Guardiola memberikan pujian khusus untuk Kevin De Bruyne yang kembali tampil impresif setelah pulih dari cedera. Gelandang asal Belgia itu mencetak 2 assist dalam kemenangan City atas Chelsea.

"Kevin adalah jantung tim kami. Kreativitas dan visinya di lapangan tidak tertandingi," ujar Guardiola dalam konferensi pers pasca pertandingan.

De Bruyne sendiri mengaku senang bisa kembali membantu tim. "Saya merasa sangat baik di lapangan. Tim bermain dengan sangat baik dan saya hanya melakukan pekerjaan saya."',
'Pep Guardiola memuji Kevin De Bruyne yang kembali cemerlang setelah pulih dari cedera.',
1, 1, 'news', 1, 0, NOW() - INTERVAL 4 HOUR),

('Rashford: Derby Manchester Adalah Pertandingan Terpenting Musim Ini', 'rashford-derby-manchester-pertandingan-terpenting',
'Marcus Rashford berbicara tentang persiapan Manchester United menghadapi derby melawan City yang akan berlangsung bulan depan. Striker Inggris ini menegaskan pentingnya pertandingan tersebut bagi timnya.

"Derby Manchester selalu spesial. Ini bukan hanya tentang tiga poin, tapi juga tentang kebanggaan kota," kata Rashford dalam wawancara eksklusif.

Erik ten Hag juga memberikan komentar tentang persiapan timnya: "Kami akan mempersiapkan diri dengan maksimal. City adalah tim yang sangat kuat, tapi kami percaya dengan kemampuan kami."

United datang dengan performa yang cukup baik dalam beberapa pertandingan terakhir, meskipun masih tertinggal dari City di klasemen liga.',
'Marcus Rashford menyatakan derby Manchester adalah pertandingan terpenting musim ini untuk United.',
2, 1, 'interview', 1, 1, NOW() - INTERVAL 2 HOUR),

('Bruno Fernandes Perpanjang Kontrak Hingga 2028', 'bruno-fernandes-perpanjang-kontrak-2028',
'Manchester United mengumumkan perpanjangan kontrak Bruno Fernandes hingga tahun 2028. Keputusan ini menunjukkan komitmen klub terhadap kapten tim mereka.

"Bruno adalah pemain kunci dalam rencana kami. Dia menunjukkan leadership dan kualitas yang luar biasa setiap pertandingan," kata direktur olahraga United, John Murtough.

Fernandes sendiri mengungkapkan kebahagiaannya: "Manchester United adalah rumah saya. Saya sangat bahagia bisa melanjutkan perjalanan bersama klub ini dan meraih lebih banyak trofi."

Sejak bergabung pada Januari 2020, Fernandes telah mencetak 60 gol dan memberikan 49 assist dalam 200 penampilan untuk United.',
'Manchester United resmi memperpanjang kontrak Bruno Fernandes hingga 2028.',
2, 1, 'news', 1, 0, NOW() - INTERVAL 6 HOUR);

-- ========================================
-- 7. TABEL USER_FAVORITES
-- ========================================
CREATE TABLE user_favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    article_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, article_id)
);

-- ========================================
-- 8. TABEL MATCHES (Jadwal Pertandingan)
-- ========================================
CREATE TABLE matches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    home_team_id INT NOT NULL,
    away_team_id INT NOT NULL,
    competition VARCHAR(100) NOT NULL, -- 'Premier League', 'FA Cup', 'Champions League'
    match_date DATETIME NOT NULL,
    venue VARCHAR(200),
    home_score INT,
    away_score INT,
    status ENUM('scheduled', 'live', 'finished', 'postponed') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (home_team_id) REFERENCES clubs(id) ON DELETE CASCADE,
    FOREIGN KEY (away_team_id) REFERENCES clubs(id) ON DELETE CASCADE
);

-- Insert Sample Matches
INSERT INTO matches (home_team_id, away_team_id, competition, match_date, venue, status) VALUES
(1, 2, 'Premier League', '2025-12-15 17:30:00', 'Etihad Stadium', 'scheduled'),
(2, 1, 'Premier League', '2026-03-22 16:00:00', 'Old Trafford', 'scheduled');

-- Insert Past Matches
INSERT INTO matches (home_team_id, away_team_id, competition, match_date, venue, home_score, away_score, status) VALUES
(1, 2, 'Premier League', '2024-10-29 16:30:00', 'Etihad Stadium', 3, 1, 'finished'),
(2, 1, 'FA Cup', '2024-05-25 17:00:00', 'Wembley Stadium', 1, 2, 'finished');

-- ========================================
-- 9. TABEL COMMENTS (Komentar Berita)
-- ========================================
CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    article_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    is_approved TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================
-- 10. TABEL SETTINGS (Konfigurasi Website)
-- ========================================
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert Default Settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Manchester Side'),
('site_tagline', 'Two Sides, One City, Endless Rivalry'),
('site_description', 'Platform berita eksklusif Manchester City dan Manchester United'),
('site_email', 'info@manchesterside.com'),
('articles_per_page', '12'),
('allow_comments', '1'),
('maintenance_mode', '0');

-- ========================================
-- VIEWS & INDEXES untuk Performance
-- ========================================

-- View untuk artikel dengan informasi lengkap
CREATE VIEW v_articles_full AS
SELECT 
    a.id,
    a.title,
    a.slug,
    a.excerpt,
    a.image_url,
    a.category,
    a.views,
    a.published_at,
    c.name as club_name,
    c.code as club_code,
    c.color_primary,
    ad.full_name as author_name
FROM articles a
LEFT JOIN clubs c ON a.club_id = c.id
JOIN admins ad ON a.author_id = ad.id
WHERE a.is_published = 1
ORDER BY a.published_at DESC;

-- ========================================
-- TRIGGERS
-- ========================================

-- Trigger untuk auto-generate slug dari title
DELIMITER //
CREATE TRIGGER before_article_insert
BEFORE INSERT ON articles
FOR EACH ROW
BEGIN
    IF NEW.slug IS NULL OR NEW.slug = '' THEN
        SET NEW.slug = LOWER(REPLACE(REPLACE(NEW.title, ' ', '-'), ',', ''));
    END IF;
END;//
DELIMITER ;

-- ========================================
-- SAMPLE DATA SUMMARY
-- ========================================
-- ✅ 2 Clubs (Manchester City & Manchester United)
-- ✅ 3 Sample Users (1 admin, 2 fans)
-- ✅ 2 Admin accounts
-- ✅ 16 Players (8 per club)
-- ✅ 4 Staff members
-- ✅ 4 Sample articles
-- ✅ 4 Matches (2 scheduled, 2 finished)
-- ========================================