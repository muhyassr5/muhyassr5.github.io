# Gamifikasi LMS – Sistem Operasi

Platform pembelajaran interaktif berbasis **gamifikasi** untuk mata kuliah **Sistem Operasi**, dibangun menggunakan PHP dan SQLite (tanpa perlu XAMPP).

---

## ✨ Fitur Utama

- 📊 **Dashboard** – Statistik Level, XP, Streak harian, dan Progress belajar per Bab
- 📚 **Materi Pembelajaran** – Konten Bab 1-5 Sistem Operasi dengan Daftar Isi interaktif dan pemutar video
- 🎯 **Quiz Interaktif** – 10 soal pilihan ganda acak per sesi dengan timer hitung mundur 15 menit
- 🏆 **Leaderboard Mingguan** – Papan peringkat yang ter-reset otomatis setiap Senin
- ⚡ **Sistem XP & Level** – Naik level setiap 500 XP dengan animasi popup perayaan
- 🔥 **Streak Login Harian** – Bonus XP untuk konsistensi login tiap hari
- 🎖️ **Badge / Lencana** – 7 lencana pencapaian dengan efek confetti saat terbuka

---

## 🚀 Cara Menjalankan (Tanpa XAMPP)

### Prasyarat
- PHP 8.0 atau lebih tinggi (sudah terinstal di sistem)

### Langkah-langkah

1. **Clone repository ini:**
   ```bash
   git clone https://github.com/USERNAME/gamifikasi-lms.git
   cd gamifikasi-lms
   ```

2. **Jalankan server lokal PHP:**
   ```bash
   php -S localhost:8000
   ```

3. **Buka browser dan akses:**
   ```
   http://localhost:8000/register.php
   ```

4. Daftar akun baru → database SQLite akan **otomatis terbuat** beserta seluruh tabel dan data awal (badge, soal kuis).

---

## 📁 Struktur Folder

```
gamifikasi-lms/
├── config/
│   └── database.php         ← Koneksi PDO + Auto-Seeding Database
├── includes/
│   ├── GamificationEngine.php   ← Engine XP, Level, Badge
│   ├── LeaderboardEngine.php    ← Engine Leaderboard + Reset Mingguan
│   ├── sidebar.php              ← Komponen Navigasi Sidebar
│   └── notifications.php       ← Popup Level Up + Efek Confetti
├── login.php
├── register.php
├── dashboard.php
├── materi.php
├── quiz.php
├── quiz_playground.php
├── quiz_process.php
├── quiz_result.php
├── leaderboard.php
└── logout.php
```

> ⚠️ File `gamifikasi_lms.db` **tidak disertakan** di repository (dikecualikan via .gitignore). Database akan dibuat otomatis saat pertama kali mendaftar.

---

## 🛠️ Teknologi

| Layer | Teknologi |
|-------|-----------|
| Backend | PHP 8.0 |
| Database | SQLite 3 (via PDO) |
| Frontend | HTML5, CSS3 Vanilla |
| Font | Google Fonts – Poppins |
| Ikon | Google Material Icons |
| Efek | canvas-confetti (CDN) |
