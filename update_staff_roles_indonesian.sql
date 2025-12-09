-- Update Staff Roles dari Bahasa Inggris ke Bahasa Indonesia
-- Jalankan query ini di phpMyAdmin atau MySQL client

UPDATE staff SET role = 'Manajer' WHERE role = 'Manager';
UPDATE staff SET role = 'Asisten Manajer' WHERE role = 'Assistant Manager';
UPDATE staff SET role = 'Pelatih Kepala' WHERE role = 'Head Coach';
UPDATE staff SET role = 'Asisten Pelatih' WHERE role = 'Assistant Coach';
UPDATE staff SET role = 'Pelatih Kiper' WHERE role = 'Goalkeeping Coach';
UPDATE staff SET role = 'Pelatih Fisik' WHERE role = 'Fitness Coach';
UPDATE staff SET role = 'Direktur Teknik' WHERE role = 'Technical Director';

-- Verifikasi hasil update
SELECT id, name, role, club_id FROM staff ORDER BY role, name;
