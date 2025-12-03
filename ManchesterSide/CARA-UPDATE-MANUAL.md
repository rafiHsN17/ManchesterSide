# 📘 Cara Update Header & Footer Secara Manual

## 🎯 Tujuan
Mengganti navbar dan footer di setiap file dengan komponen yang konsisten.

## 📋 File yang Perlu Diupdate

1. ⏳ index.php
2. ⏳ news.php
3. ⏳ club.php
4. ⏳ club-full.php
5. ⏳ profile.php
6. ⏳ standings.php

## 🔧 Langkah-Langkah Update

### Untuk Setiap File:

#### 1. Buka File di Editor

#### 2. Cari dan Hapus Navbar Lama

**Cari kode ini:**
```php
<!-- Navigation -->
<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- ... banyak kode navbar ... -->
    </div>
</nav>
```

**Atau cari dari:**
```php
<nav class="bg-white shadow-lg
```

**Sampai:**
```php
</nav>
```

**Hapus semua kode navbar tersebut!**

#### 3. Ganti dengan Include Header

**Di tempat yang sama, ketik:**
```php
<?php include 'includes/header.php'; ?>
```

#### 4. Cari dan Hapus Footer Lama

**Cari kode ini:**
```php
<!-- Footer -->
<footer class="bg-gray-900 text-white py-12 mt-16">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <!-- ... kode footer ... -->
    </div>
</footer>
```

**Atau cari dari:**
```php
<footer class="bg-gray-900
```

**Sampai:**
```php
</footer>
```

**Hapus semua kode footer tersebut!**

#### 5. Ganti dengan Include Footer

**Di tempat yang sama, ketik:**
```php
<?php include 'includes/footer.php'; ?>
```

#### 6. Save File

#### 7. Test di Browser

Buka file di browser dan cek:
- ✅ Header muncul
- ✅ Logo tampil
- ✅ Menu navigasi berfungsi
- ✅ Footer muncul
- ✅ Responsive OK

## 📝 Contoh Sebelum & Sesudah

### SEBELUM:
```php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Halaman</title>
    <!-- CSS -->
</head>
<body>

    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- 50+ baris kode navbar -->
            </div>
        </div>
    </nav>

    <main>
        <!-- Konten halaman -->
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <!-- 40+ baris kode footer -->
        </div>
    </footer>

</body>
</html>
```

### SESUDAH:
```php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Halaman</title>
    <!-- CSS -->
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <!-- Konten halaman -->
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>
```

## ⚡ Tips Cepat

### Menggunakan Find & Replace di Editor

**Untuk Navbar:**
1. Tekan `Ctrl+H` (Find & Replace)
2. Aktifkan "Regex" mode
3. Find: `<!-- Navigation -->[\s\S]*?</nav>`
4. Replace: `<?php include 'includes/header.php'; ?>`
5. Replace

**Untuk Footer:**
1. Tekan `Ctrl+H` (Find & Replace)
2. Aktifkan "Regex" mode
3. Find: `<!-- Footer -->[\s\S]*?</footer>`
4. Replace: `<?php include 'includes/footer.php'; ?>`
5. Replace

## ✅ Checklist Per File

### index.php
- [ ] Backup file
- [ ] Replace navbar dengan include header
- [ ] Replace footer dengan include footer
- [ ] Test di browser
- [ ] Verifikasi semua fungsi OK

### news.php
- [ ] Backup file
- [ ] Replace navbar dengan include header
- [ ] Replace footer dengan include footer
- [ ] Test di browser
- [ ] Verifikasi semua fungsi OK

### club.php
- [ ] Backup file
- [ ] Replace navbar dengan include header
- [ ] Replace footer dengan include footer
- [ ] Test di browser
- [ ] Verifikasi semua fungsi OK

### club-full.php
- [ ] Backup file
- [ ] Replace navbar dengan include header
- [ ] Replace footer dengan include footer
- [ ] Test di browser
- [ ] Verifikasi semua fungsi OK

### profile.php
- [ ] Backup file
- [ ] Replace navbar dengan include header
- [ ] Replace footer dengan include footer
- [ ] Test di browser
- [ ] Verifikasi semua fungsi OK

### standings.php
- [ ] Backup file
- [ ] Replace navbar dengan include header
- [ ] Replace footer dengan include footer
- [ ] Test di browser
- [ ] Verifikasi semua fungsi OK

## 🐛 Troubleshooting

### Header tidak muncul
- Pastikan path `includes/header.php` benar
- Pastikan file `includes/header.php` ada
- Check error PHP di browser

### Footer tidak muncul
- Pastikan path `includes/footer.php` benar
- Pastikan file `includes/footer.php` ada
- Pastikan tidak ada error PHP sebelum footer

### Style tidak sesuai
- Pastikan Tailwind CSS masih di-load di `<head>`
- Pastikan konfigurasi warna Tailwind masih ada

## 🎉 Selesai!

Setelah semua file diupdate:
1. Test semua halaman
2. Verifikasi responsive design
3. Hapus file backup
4. Selesai! 🎊

---

**Manchester Side** - Two Sides, One City ⚽
