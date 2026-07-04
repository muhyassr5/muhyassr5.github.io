<?php
// config/database.php

$dbPath = __DIR__ . '/../gamifikasi_lms.db';
$dbExists = file_exists($dbPath);

try {
    // Membuat koneksi ke SQLite
    $conn = new PDO("sqlite:" . $dbPath);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Aktifkan foreign key di SQLite
    $conn->exec("PRAGMA foreign_keys = ON;");

    // Jika database baru pertama kali dibuat, inisialisasi tabel dan seed data
    if (!$dbExists || filesize($dbPath) === 0) {
        // 1. Table: Users
        $conn->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT CHECK(role IN ('mahasiswa', 'admin')) DEFAULT 'mahasiswa',
            foto TEXT DEFAULT 'default_avatar.png',
            xp INTEGER DEFAULT 0,
            level INTEGER DEFAULT 1,
            current_streak INTEGER DEFAULT 0,
            last_login TEXT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );");

        // 2. Table: Materi Progress
        $conn->exec("CREATE TABLE IF NOT EXISTS materi_progress (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            bab_id INTEGER NOT NULL,
            materi_selesai INTEGER DEFAULT 0,
            video_selesai INTEGER DEFAULT 0,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE (user_id, bab_id)
        );");

        // 3. Table: Bank Soal Quiz
        $conn->exec("CREATE TABLE IF NOT EXISTS quiz_questions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            bab_id INTEGER NOT NULL,
            pertanyaan TEXT NOT NULL,
            opsi_a TEXT NOT NULL,
            opsi_b TEXT NOT NULL,
            opsi_c TEXT NOT NULL,
            opsi_d TEXT NOT NULL,
            jawaban_benar TEXT CHECK(jawaban_benar IN ('A', 'B', 'C', 'D')) NOT NULL
        );");

        // 4. Table: Riwayat Nilai Quiz
        $conn->exec("CREATE TABLE IF NOT EXISTS quiz_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            bab_id INTEGER NOT NULL,
            skor INTEGER NOT NULL,
            xp_diperoleh INTEGER DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );");

        // 5. Table: Master Badges
        $conn->exec("CREATE TABLE IF NOT EXISTS badges (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT UNIQUE NOT NULL,
            nama_badge TEXT NOT NULL,
            deskripsi TEXT NOT NULL,
            icon_name TEXT NOT NULL
        );");

        // 6. Table: User Badges Mapping
        $conn->exec("CREATE TABLE IF NOT EXISTS user_badges (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            badge_id INTEGER,
            unlocked_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
            UNIQUE (user_id, badge_id)
        );");

        // 7. Table: XP Log
        $conn->exec("CREATE TABLE IF NOT EXISTS xp_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            jumlah_xp INTEGER NOT NULL,
            tipe_aktivitas TEXT NOT NULL,
            tanggal TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );");

        // --- SEEDING MASTER BADGES ---
        $badgesStmt = $conn->prepare("INSERT OR IGNORE INTO badges (slug, nama_badge, deskripsi, icon_name) VALUES (?, ?, ?, ?)");
        $badges = [
            ['beginner', 'Beginner', 'Belajar pertama kali di LMS', 'school'],
            ['consistent', 'Consistent', 'Login 7 hari berturut-turut', 'workspace_premium'],
            ['explorer', 'Explorer', 'Menyelesaikan semua materi bab', 'explore'],
            ['quiz_master', 'Quiz Master', 'Nilai rata-rata kuis di atas 90', 'psychology'],
            ['perfect_score', 'Perfect Score', 'Mendapatkan nilai sempurna 100 pada kuis', 'star'],
            ['fast_learner', 'Fast Learner', 'Menyelesaikan 5 materi pembelajaran', 'speed'],
            ['champion', 'Champion', 'Masuk ke dalam jajaran Top 3 Leaderboard', 'military_tech']
        ];
        foreach ($badges as $badge) {
            $badgesStmt->execute($badge);
        }

        // --- SEEDING BANK SOAL QUIZ (Sistem Operasi - Bab 1 s/d Bab 5) ---
        $quizQuestionsStmt = $conn->prepare("INSERT INTO quiz_questions (bab_id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban_benar) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $questions = [
            // Bab 1
            [1, "Apa fungsi utama dari Sistem Operasi?", "Menjalankan aplikasi game saja", "Menghubungkan perangkat keras dengan pengguna dan perangkat lunak aplikasi", "Mengirim email secara otomatis", "Membersihkan debu komputer secara fisik", "B"],
            [1, "Komponen Sistem Operasi yang berfungsi sebagai inti dan mengelola sumber daya sistem adalah...", "Command Prompt", "Antarmuka Pengguna (GUI)", "Kernel", "Web Browser", "C"],
            
            // Bab 2
            [2, "Keadaan di mana dua atau lebih proses saling menunggu sumber daya yang dimiliki oleh proses lain sehingga tidak ada yang bisa berjalan disebut...", "Starvation", "Deadlock", "Race Condition", "Context Switch", "B"],
            [2, "Algoritma penjadwalan proses di mana proses yang datang pertama akan dilayani pertama disebut...", "Shortest Job First (SJF)", "Round Robin (RR)", "First-Come First-Served (FCFS)", "Priority Scheduling", "C"],
            
            // Bab 3
            [3, "Teknik manajemen memori di mana memori fisik dibagi menjadi blok-blok berukuran tetap disebut...", "Pages", "Segments", "Frames", "Sectors", "C"],
            [3, "Fungsi dari Virtual Memory pada Sistem Operasi adalah...", "Meningkatkan kecepatan processor secara fisik", "Menyimpan data permanen saat komputer mati", "Mengizinkan pengeksekusian proses yang ukurannya melebihi kapasitas memori fisik (RAM)", "Melindungi sistem dari serangan virus eksternal", "C"],
            
            // Bab 4
            [4, "Struktur penyimpanan berkas pada Sistem Operasi umumnya diorganisasikan dalam bentuk...", "Array satu dimensi", "Pohon Hirarki (Directory Tree)", "Antrean (Queue)", "Tumpukan (Stack)", "B"],
            [4, "Sistem berkas default yang digunakan secara umum pada sistem operasi Windows modern adalah...", "FAT32", "ext4", "NTFS", "APFS", "C"],
            
            // Bab 5
            [5, "Serangan yang bertujuan untuk membanjiri server dengan lalu lintas data palsu agar server tidak dapat diakses disebut...", "Phishing", "Denial of Service (DoS / DDoS)", "SQL Injection", "Trojan Horse", "B"],
            [5, "Mekanisme keamanan untuk membatasi hak akses pengguna atau proses ke sumber daya sistem tertentu disebut...", "Enkripsi", "Autentikasi", "Otorisasi (Access Control)", "Auditing", "C"]
        ];
        foreach ($questions as $q) {
            $quizQuestionsStmt->execute($q);
        }

        // --- BUAT AKUN ADMIN DEFAULT ---
        $adminStmt = $conn->prepare("INSERT OR IGNORE INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
        $adminPass = password_hash('admin123', PASSWORD_BCRYPT);
        $adminStmt->execute(['Admin LMS', 'admin@pti.ac.id', $adminPass, 'admin']);
    }
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
