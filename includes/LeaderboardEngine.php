<?php
// includes/LeaderboardEngine.php

class LeaderboardEngine {
    private $db;

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    // Mengambil Data Peringkat Sesuai Aturan Filter Waktu Terpilih
    public function getTopStudents($period = 'all_time', $limit = 10) {
        $query = "";
        $startDate = null;

        switch ($period) {
            case 'harian':
                $startDate = date('Y-m-d');
                $query = "SELECT u.id, u.nama, u.level, u.foto, SUM(xl.jumlah_xp) as total_xp 
                          FROM users u JOIN xp_logs xl ON u.id = xl.user_id 
                          WHERE xl.tanggal = :start_date 
                          GROUP BY u.id ORDER BY total_xp DESC, u.nama ASC LIMIT " . intval($limit);
                break;
            case 'mingguan':
                // Reset setiap awal minggu (Senin)
                $startDate = date('Y-m-d', strtotime('monday this week'));
                $query = "SELECT u.id, u.nama, u.level, u.foto, SUM(xl.jumlah_xp) as total_xp 
                          FROM users u JOIN xp_logs xl ON u.id = xl.user_id 
                          WHERE xl.tanggal >= :start_date 
                          GROUP BY u.id ORDER BY total_xp DESC, u.nama ASC LIMIT " . intval($limit);
                break;
            case 'bulanan':
                $startDate = date('Y-m-01');
                $query = "SELECT u.id, u.nama, u.level, u.foto, SUM(xl.jumlah_xp) as total_xp 
                          FROM users u JOIN xp_logs xl ON u.id = xl.user_id 
                          WHERE xl.tanggal >= :start_date 
                          GROUP BY u.id ORDER BY total_xp DESC, u.nama ASC LIMIT " . intval($limit);
                break;
            case 'all_time':
            default:
                // Mengambil basis data total XP absolut langsung dari tabel user utama
                $query = "SELECT id, nama, level, foto, xp as total_xp 
                          FROM users WHERE role = 'mahasiswa' 
                          ORDER BY total_xp DESC, nama ASC LIMIT " . intval($limit);
                break;
        }

        $stmt = $this->db->prepare($query);
        if ($startDate !== null) {
            $stmt->execute(['start_date' => $startDate]);
        } else {
            $stmt->execute();
        }
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $leaderboard = [];
        $rank = 1;
        foreach ($results as $row) {
            // Ambil koleksi lencana lencana aktif milik mahasiswa ini
            $row['badges'] = $this->getUserBadgesList($row['id']);
            $row['rank'] = $rank;
            $leaderboard[] = $row;
            
            // Pemicu Otomatisasi Kelayakan Pencapaian Badge Champion Bagi Top 3 Mahasiswa
            if ($rank <= 3) {
                $this->triggerChampionBadge($row['id']);
            }
            $rank++;
        }
        return $leaderboard;
    }

    private function getUserBadgesList($userId) {
        $stmt = $this->db->prepare("SELECT b.icon_name, b.nama_badge FROM user_badges ub JOIN badges b ON ub.badge_id = b.id WHERE ub.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function triggerChampionBadge($userId) {
        // Melakukan bypass pemanggilan engine internal untuk menempelkan Lencana Champion
        $bStmt = $this->db->prepare("SELECT id FROM badges WHERE slug = 'champion'");
        $bStmt->execute();
        $badge = $bStmt->fetch();
        if ($badge) {
            $ins = $this->db->prepare("INSERT OR IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)");
            $ins->execute([$userId, $badge['id']]);
        }
    }
}
?>
