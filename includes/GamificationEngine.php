<?php
// includes/GamificationEngine.php

class GamificationEngine {
    private $db;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    // Fungsi Utama Penambahan XP & Logging Berdasarkan Aturan Main
    public function addXp($userId, $xpAmount, $activityType) {
        if ($xpAmount <= 0) return false;

        // 1. Log Aktivitas XP untuk Tracking Leaderboard Periodik
        $today = date('Y-m-d');
        $logStmt = $this->db->prepare("INSERT INTO xp_logs (user_id, jumlah_xp, tipe_aktivitas, tanggal) VALUES (?, ?, ?, ?)");
        $logStmt->execute([$userId, $xpAmount, $activityType, $today]);

        // 2. Ambil XP Lama User
        $userStmt = $this->db->prepare("SELECT xp, level FROM users WHERE id = ?");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch();

        if (!$user) return false;

        $currentXp = $user['xp'];
        $currentLevel = $user['level'];
        $newXp = $currentXp + $xpAmount;

        // 3. Hitung Level Otomatis Berdasarkan Aturan XP Ganjil Kelipatan 500 XP
        // Level 1: 0-499, Level 2: 500-999, Level 3: 1000-1499, dst.
        $newLevel = floor($newXp / 500) + 1;
        
        $levelUpTriggered = false;
        if ($newLevel > $currentLevel) {
            $levelUpTriggered = true;
            $_SESSION['level_up_popup'] = $newLevel; // Pemicu animasi popup di frontend
        }

        // 4. Update data pengguna ke database
        $updateStmt = $this->db->prepare("UPDATE users SET xp = ?, level = ? WHERE id = ?");
        $updateStmt->execute([$newXp, $newLevel, $userId]);

        // 5. Jalankan Evaluasi Badge Otomatis Akibat Perubahan Atribut Terbaru
        $this->evaluateBadges($userId);

        return [
            'xp_added' => $xpAmount,
            'new_xp' => $newXp,
            'level_up' => $levelUpTriggered,
            'current_level' => $newLevel
        ];
    }

    // Mengelola Logika Penghitungan Login Streak Harian Mahasiswa
    public function checkAndUpdateStreak($userId) {
        $today = new DateTime(date('Y-m-d'));
        $stmt = $this->db->prepare("SELECT last_login, current_streak FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) return;

        if ($user['last_login'] === null || $user['last_login'] === '') {
            // Pertama kali login sepanjang hidup akun
            $newStreak = 1;
            $this->updateLoginState($userId, $newStreak);
            $this->addXp($userId, 10, 'login_harian');
        } else {
            $lastLogin = new DateTime($user['last_login']);
            $diff = $today->diff($lastLogin)->days;

            if ($diff == 1) {
                // Login berturut-turut besoknya
                $newStreak = $user['current_streak'] + 1;
                $this->updateLoginState($userId, $newStreak);
                $this->addXp($userId, 10, 'login_harian');
            } elseif ($diff > 1) {
                // Bolong login, streak hangus kembali ke 0 kemudian langsung dihitung 1 hari keaktifan baru
                $newStreak = 1;
                $this->updateLoginState($userId, $newStreak);
                $this->addXp($userId, 10, 'login_harian');
            }
            // Jika $diff == 0 artinya user login berkali-kali di hari yang sama, streak & XP login tetap aman.
        }
    }

    private function updateLoginState($userId, $streak) {
        $todayStr = date('Y-m-d');
        $stmt = $this->db->prepare("UPDATE users SET current_streak = ?, last_login = ? WHERE id = ?");
        $stmt->execute([$streak, $todayStr, $userId]);
    }

    // Sistem Otomatisasi Validasi Syarat Unlock Pencapaian Lencana (Badge)
    public function evaluateBadges($userId) {
        // Ambil info dasar pengguna
        $stmt = $this->db->prepare("SELECT xp, level, current_streak FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) return;

        // 1. Badge: Beginner (Belajar Pertama Kali - Jika XP > 10)
        if ($user['xp'] >= 10) { $this->assignBadge($userId, 'beginner'); }

        // 2. Badge: Consistent (Login 7 Hari Beruntun)
        if ($user['current_streak'] >= 7) { $this->assignBadge($userId, 'consistent'); }

        // 3. Badge: Fast Learner & Explorer Tracking via progress table
        $progressStmt = $this->db->prepare("SELECT COUNT(*) as total_selesai FROM materi_progress WHERE user_id = ? AND materi_selesai = 1");
        $progressStmt->execute([$userId]);
        $totalSelesaiMateri = $progressStmt->fetch()['total_selesai'];

        if ($totalSelesaiMateri >= 1) { $this->assignBadge($userId, 'beginner'); } 
        if ($totalSelesaiMateri >= 5) { 
            $this->assignBadge($userId, 'fast_learner'); 
            $this->assignBadge($userId, 'explorer'); 
        }

        // 4. Badge: Quiz Master (Rata-rata Nilai > 90)
        $quizStmt = $this->db->prepare("SELECT AVG(skor) as rata_rata FROM quiz_attempts WHERE user_id = ?");
        $quizStmt->execute([$userId]);
        $res = $quizStmt->fetch();
        $avgScore = $res ? $res['rata_rata'] : 0;
        if ($avgScore >= 90) { $this->assignBadge($userId, 'quiz_master'); }
    }

    public function assignBadge($userId, $badgeSlug) {
        $bStmt = $this->db->prepare("SELECT id FROM badges WHERE slug = ?");
        $bStmt->execute([$badgeSlug]);
        $badge = $bStmt->fetch();

        if ($badge) {
            $badgeId = $badge['id'];
            $insStmt = $this->db->prepare("INSERT OR IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)");
            $insStmt->execute([$userId, $badgeId]);
            
            if ($insStmt->rowCount() > 0) {
                $_SESSION['badge_unlocked_confetti'] = $badgeSlug; // Memicu efek animasi confetti di sisi client browser
                return true;
            }
        }
        return false;
    }
}
?>
