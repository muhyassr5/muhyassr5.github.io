<?php
// dashboard.php (Dashboard Utama Mahasiswa)
session_start();
require_once 'config/database.php';
require_once 'includes/GamificationEngine.php';
require_once 'includes/LeaderboardEngine.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$gamification = new GamificationEngine($conn);

// Periksa dan update streak login harian
$gamification->checkAndUpdateStreak($userId);

// Ambil info lengkap pengguna
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Ambil lencana yang sudah diperoleh
$badgeStmt = $conn->prepare("SELECT COUNT(*) as total_badges FROM user_badges WHERE user_id = ?");
$badgeStmt->execute([$userId]);
$totalBadges = $badgeStmt->fetch()['total_badges'];

// Level Title Mapping
$levelTitle = "Novice";
if ($user['level'] == 2) {
    $levelTitle = "Junior Learner";
} elseif ($user['level'] == 3) {
    $levelTitle = "Capable Learner";
} elseif ($user['level'] == 4) {
    $levelTitle = "Expert Learner";
} elseif ($user['level'] >= 5) {
    $levelTitle = "Grandmaster Learner";
}

// Kalkulasi target XP
$nextLevelXpTarget = $user['level'] * 500;
$currentXp = $user['xp'];
$xpLimitPerLevel = 500;
$currentXpInLevel = $currentXp % $xpLimitPerLevel;
$xpPercentage = ($currentXpInLevel / $xpLimitPerLevel) * 100;

// Daftar Bab Kuis
$chapters = [
    1 => "Pengenalan Sistem Operasi",
    2 => "Manajemen Proses & Penjadwalan",
    3 => "Manajemen Memori Utama & Virtual",
    4 => "Sistem Berkas & Penyimpanan",
    5 => "Keamanan & Proteksi Sistem Operasi"
];

// Ambil progress membaca teks & menonton video pembelajaran untuk masing-masing bab
$progress = [];
foreach ($chapters as $babId => $babTitle) {
    $progStmt = $conn->prepare("SELECT materi_selesai, video_selesai FROM materi_progress WHERE user_id = ? AND bab_id = ?");
    $progStmt->execute([$userId, $babId]);
    $res = $progStmt->fetch();
    
    $materiSelesai = $res ? intval($res['materi_selesai']) : 0;
    $videoSelesai = $res ? intval($res['video_selesai']) : 0;
    
    $percentage = ($materiSelesai * 50) + ($videoSelesai * 50);
    $progress[$babId] = $percentage;
}

// Ambil Leaderboard Mingguan
$leaderboardEngine = new LeaderboardEngine($conn);
$weeklyLeaderboard = $leaderboardEngine->getTopStudents('mingguan', 4);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Gamifikasi - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --bg-color: #F8FAFC;
            --primary-gradient: linear-gradient(135deg, #6C4CF1 0%, #8A73FF 100%);
            --card-bg: #FFFFFF;
            --text-dark: #1E293B;
            --text-light: #64748B;
            --accent-green: #10B981;
            --accent-orange: #F59E0B;
            --border-color: #E2E8F0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background: var(--bg-color); color: var(--text-dark); min-height: 100vh; padding: 0; display: flex; }

        /* Content Area */
        .content-area {
            margin-left: 250px;
            padding: 40px;
            width: calc(100% - 250px);
            min-height: 100vh;
        }

        @media (max-width: 900px) {
            .content-area {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
        }

        /* Top Header */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
        }
        .header-title h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-dark);
        }
        .header-title p {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #FFFFFF;
            padding: 8px 16px;
            border-radius: 50px;
            border: 1px solid var(--border-color);
        }
        .user-dropdown-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }
        .user-dropdown-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        /* Stats Row */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        .stat-card {
            background: var(--card-bg);
            border-radius: 18px;
            padding: 20px 25px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 10px rgba(0,0,0,0.01);
        }
        .stat-card-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .stat-card-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-dark);
        }
        .stat-card-desc {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-top: 2px;
        }

        /* Quote Banner */
        .quote-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--primary-gradient);
            color: #FFFFFF;
            border-radius: 20px;
            padding: 25px 35px;
            margin-bottom: 30px;
            box-shadow: 0 8px 24px rgba(108,76,241,0.15);
            position: relative;
            overflow: hidden;
        }
        .quote-banner::after {
            content: "";
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
            right: -30px;
            top: -30px;
        }
        .quote-text h2 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .quote-text p {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .quote-image {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        /* Bottom Layout columns */
        .main-layout {
            display: grid;
            grid-template-columns: 1.7fr 1.3fr;
            gap: 30px;
        }
        @media (max-width: 900px) {
            .main-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Card Container style */
        .dashboard-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 30px rgba(0,0,0,0.02);
            height: fit-content;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
        }
        .card-link {
            font-size: 0.85rem;
            color: #6C4CF1;
            text-decoration: none;
            font-weight: 600;
        }
        .card-link:hover {
            text-decoration: underline;
        }

        /* Progress List (Table representation) */
        .progress-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .progress-item {
            display: grid;
            grid-template-columns: 80px 1.5fr 1.2fr 40px;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #F1F5F9;
        }
        .progress-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .progress-bab-num {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-light);
            text-transform: uppercase;
        }
        .progress-bab-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-dark);
            padding-right: 15px;
        }
        
        .progress-bar-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .progress-bar {
            flex-grow: 1;
            height: 8px;
            background: #EAF4FF;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background: var(--primary-gradient);
            border-radius: 4px;
        }
        .progress-text {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6C4CF1;
            width: 35px;
            text-align: right;
        }
        
        .progress-status .material-icons {
            font-size: 1.3rem;
            color: var(--accent-green);
        }
        .progress-status .material-icons.incomplete {
            color: #CBD5E1;
        }

        /* Mini Leaderboard */
        .mini-leaderboard {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .leaderboard-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: #F8FAFC;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            transition: all 0.2s;
        }
        .leaderboard-item.current-user {
            background: #F5F3FF;
            border-color: rgba(108,76,241,0.2);
        }
        .leaderboard-rank {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-light);
            width: 30px;
        }
        .leaderboard-rank.rank-1 { color: #FFD700; font-size: 1.1rem; }
        .leaderboard-rank.rank-2 { color: #C0C0C0; font-size: 1.1rem; }
        .leaderboard-rank.rank-3 { color: #CD7F32; font-size: 1.1rem; }
        
        .leaderboard-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
        }
        .leaderboard-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-dark);
            flex-grow: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-right: 10px;
        }
        .leaderboard-xp {
            font-size: 0.9rem;
            font-weight: 700;
            color: #6C4CF1;
        }

        /* Btn Full */
        .btn-full {
            display: block;
            text-align: center;
            background: #F1F5F9;
            color: #475569;
            padding: 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            margin-top: 20px;
            transition: background 0.2s;
        }
        .btn-full:hover {
            background: #E2E8F0;
        }
    </style>
</head>
<body>

    <!-- Sidebar Menu -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="content-area">
        <!-- Header -->
        <div class="header-section">
            <div class="header-title">
                <h1>Halo, <?php echo htmlspecialchars($user['nama']); ?>! 👋</h1>
                <p>Semangat belajar hari ini!</p>
            </div>
            
            <div class="user-dropdown">
                <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=<?php echo urlencode($user['nama']); ?>" class="user-dropdown-avatar" alt="Avatar">
                <span class="user-dropdown-name"><?php echo htmlspecialchars($user['nama']); ?></span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-title">Level</div>
                <div class="stat-card-value"><?php echo $user['level']; ?></div>
                <div class="stat-card-desc"><?php echo $levelTitle; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-card-title">Total XP</div>
                <div class="stat-card-value"><?php echo $user['xp']; ?> <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-light);">/ <?php echo $nextLevelXpTarget; ?></span></div>
                <div class="stat-card-desc">Progress level: <?php echo round($xpPercentage); ?>%</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-title">Streak</div>
                <div class="stat-card-value"><?php echo $user['current_streak']; ?> Hari</div>
                <div class="stat-card-desc">Keaktifan login harian</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-title">Lencana</div>
                <div class="stat-card-value"><?php echo $totalBadges; ?></div>
                <div class="stat-card-desc">Pencapaian dibuka</div>
            </div>
        </div>

        <!-- Banner Quote motivasi -->
        <div class="quote-banner">
            <div class="quote-text">
                <h2>"Belajar hari ini, Bersinar esok hari!"</h2>
                <p>Terus tingkatkan XP dan raih peringkat mingguan teratas di LMS!</p>
            </div>
            <div class="quote-image">💡</div>
        </div>

        <!-- Main Layout columns -->
        <div class="main-layout">
            <!-- Kolom Kiri: Progress Pembelajaran -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">Progress Pembelajaran</h2>
                    <a href="materi.php" class="card-link">Lihat Semua</a>
                </div>

                <div class="progress-list">
                    <?php foreach ($chapters as $babId => $babTitle): ?>
                        <?php 
                        $pct = $progress[$babId];
                        $isCompleted = ($pct == 100);
                        ?>
                        <div class="progress-item">
                            <div class="progress-bab-num">Bab <?php echo $babId; ?></div>
                            <div class="progress-bab-title"><?php echo htmlspecialchars($babTitle); ?></div>
                            <div class="progress-bar-wrapper">
                                <div class="progress-bar">
                                    <div class="progress-bar-fill" style="width: <?php echo $pct; ?>%;"></div>
                                </div>
                                <span class="progress-text"><?php echo $pct; ?>%</span>
                            </div>
                            <div class="progress-status">
                                <?php if ($isCompleted): ?>
                                    <span class="material-icons">check_circle</span>
                                <?php else: ?>
                                    <span class="material-icons incomplete">radio_button_unchecked</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Kolom Kanan: Leaderboard Mingguan -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">Leaderboard Mingguan</h2>
                </div>

                <div class="mini-leaderboard">
                    <?php if (count($weeklyLeaderboard) === 0): ?>
                        <p style="text-align: center; color: var(--text-light); font-size: 0.85rem; padding: 20px 0;">Belum ada mahasiswa aktif minggu ini.</p>
                    <?php else: ?>
                        <?php foreach ($weeklyLeaderboard as $student): ?>
                            <?php 
                            $isCurrentUser = ($student['id'] == $userId);
                            $rankClass = "leaderboard-rank";
                            if ($student['rank'] == 1) $rankClass .= " rank-1";
                            elseif ($student['rank'] == 2) $rankClass .= " rank-2";
                            elseif ($student['rank'] == 3) $rankClass .= " rank-3";
                            ?>
                            <div class="leaderboard-item <?php echo $isCurrentUser ? 'current-user' : ''; ?>">
                                <div class="<?php echo $rankClass; ?>">
                                    <?php 
                                    if ($student['rank'] == 1) echo "🥇";
                                    elseif ($student['rank'] == 2) echo "🥈";
                                    elseif ($student['rank'] == 3) echo "🥉";
                                    else echo "#" . $student['rank'];
                                    ?>
                                </div>
                                <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=<?php echo urlencode($student['nama']); ?>" class="leaderboard-avatar" alt="Avatar">
                                <div class="leaderboard-name"><?php echo htmlspecialchars($student['nama']); ?></div>
                                <div class="leaderboard-xp"><?php echo $student['total_xp']; ?> XP</div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <a href="leaderboard.php?period=mingguan" class="btn-full">Lihat Leaderboard Lengkap</a>
            </div>
        </div>
    </div>

    <!-- Notification Engine (Level Up / Confetti) -->
    <?php include 'includes/notifications.php'; ?>
</body>
</html>
