<?php
// leaderboard.php (Papan Peringkat Mahasiswa)
session_start();
require_once 'config/database.php';
require_once 'includes/LeaderboardEngine.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Default filter adalah mingguan sesuai mockup gambar
$period = $_GET['period'] ?? 'mingguan';
if (!in_array($period, ['harian', 'mingguan', 'bulanan', 'all_time'])) {
    $period = 'mingguan';
}

$leaderboardEngine = new LeaderboardEngine($conn);
$students = $leaderboardEngine->getTopStudents($period, 10);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - LMS Gamifikasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --bg-color: #F8FAFC;
            --primary-gradient: linear-gradient(135deg, #6C4CF1 0%, #8A73FF 100%);
            --card-bg: #FFFFFF;
            --text-dark: #1E293B;
            --text-light: #64748B;
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
            display: flex;
            flex-direction: column;
        }

        @media (max-width: 900px) {
            .content-area {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
        }

        /* Leaderboard Title Block */
        .title-block {
            text-align: center;
            margin-bottom: 35px;
        }
        .title-block h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        .title-block p {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        /* Period Filter Tab */
        .filter-tabs {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 35px;
            background: #E2E8F0;
            padding: 6px;
            border-radius: 16px;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }
        .tab-btn {
            border: none;
            background: transparent;
            padding: 8px 20px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .tab-btn.active {
            background: #FFFFFF;
            color: #6C4CF1;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
        }

        /* Podium Top 3 (Mockup 5 Layout) */
        .podium-container {
            display: grid;
            grid-template-columns: 1fr 1.15fr 1fr;
            gap: 20px;
            align-items: end;
            margin-bottom: 40px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            width: 100%;
        }
        
        @media (max-width: 600px) {
            .podium-container {
                grid-template-columns: 1fr;
                gap: 20px;
                align-items: stretch;
            }
        }

        .podium-card {
            background: var(--card-bg);
            border-radius: 24px;
            border: 1px solid var(--border-color);
            padding: 30px 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.02);
            transition: transform 0.2s;
            position: relative;
        }
        .podium-card:hover {
            transform: translateY(-4px);
        }
        
        /* Rank Styles */
        .rank-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 12px;
        }
        
        .podium-1st {
            border: 2.5px solid #FFD700;
            order: 2;
            transform: scale(1.05);
            box-shadow: 0 12px 35px rgba(255, 215, 0, 0.08);
            z-index: 2;
        }
        .podium-1st .rank-badge {
            background: #FFD700;
            box-shadow: 0 4px 10px rgba(255, 215, 0, 0.3);
        }
        .podium-1st::before {
            content: "👑";
            font-size: 2.2rem;
            position: absolute;
            top: -28px;
        }

        .podium-2nd {
            border: 2px solid #C0C0C0;
            order: 1;
            box-shadow: 0 8px 25px rgba(192, 192, 192, 0.05);
        }
        .podium-2nd .rank-badge {
            background: #C0C0C0;
            box-shadow: 0 4px 10px rgba(192, 192, 192, 0.3);
        }

        .podium-3rd {
            border: 2px solid #CD7F32;
            order: 3;
            box-shadow: 0 8px 25px rgba(205, 127, 50, 0.05);
        }
        .podium-3rd .rank-badge {
            background: #CD7F32;
            box-shadow: 0 4px 10px rgba(205, 127, 50, 0.3);
        }

        @media (max-width: 600px) {
            .podium-1st, .podium-2nd, .podium-3rd {
                order: initial;
                transform: none;
            }
            .podium-1st::before {
                top: -15px;
            }
        }

        .podium-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 3px solid #F1F5F9;
            object-fit: cover;
            margin-bottom: 12px;
        }
        .podium-1st .podium-avatar {
            width: 85px;
            height: 85px;
            border-color: #FEF08A;
        }

        .podium-name {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--text-dark);
            margin-bottom: 2px;
        }
        .podium-lvl {
            font-size: 0.8rem;
            font-weight: 600;
            color: #8A73FF;
            margin-bottom: 15px;
        }
        .podium-xp {
            font-size: 1.1rem;
            font-weight: 800;
            color: #6C4CF1;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .podium-xp span {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-light);
        }

        /* Leaderboard List Table Card */
        .list-card {
            background: var(--card-bg);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 30px rgba(0,0,0,0.02);
            overflow: hidden;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            width: 100%;
        }
        
        .list-row {
            display: grid;
            grid-template-columns: 80px 1.5fr 1fr 1fr;
            padding: 16px 25px;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            transition: background 0.2s;
        }
        .list-row:last-child {
            border-bottom: none;
        }
        .list-row:hover {
            background: #F8FAFC;
        }
        
        .list-row.current-user {
            background: #F5F3FF;
            border-left: 4px solid #6C4CF1;
        }
        .list-row.current-user:hover {
            background: #EDE9FE;
        }

        .row-rank {
            font-size: 1rem;
            font-weight: 700;
            color: #64748B;
            padding-left: 10px;
        }
        .row-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .row-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .row-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-dark);
        }
        .row-lvl {
            font-size: 0.75rem;
            color: var(--text-light);
            font-weight: 500;
            margin-top: 2px;
        }
        .row-lvl strong {
            color: #8A73FF;
        }

        .row-xp {
            font-weight: 700;
            font-size: 1rem;
            color: #6C4CF1;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        /* Badges column in row */
        .row-badges {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .row-badge-icon {
            background: #F1F5F9;
            border-radius: 8px;
            padding: 4px 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .row-badge-icon .material-icons {
            font-size: 1.05rem;
            color: #8A73FF;
        }
        
        /* Row badge tooltip */
        .row-badge-icon::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%) scale(0);
            background: #1E293B;
            color: #FFFFFF;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.65rem;
            white-space: nowrap;
            transition: 0.12s ease;
            pointer-events: none;
            opacity: 0;
            font-weight: 500;
            z-index: 10;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .row-badge-icon:hover::after {
            transform: translateX(-50%) scale(1);
            opacity: 1;
        }
    </style>
</head>
<body>

    <!-- Sidebar Menu -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="content-area">
        <div class="title-block">
            <h1>Papan Peringkat (Leaderboard) 🏆</h1>
            <p>Saling mengejar poin dan tunjukkan keaktifan belajarmu di kelas Sistem Operasi.</p>
        </div>

        <!-- Filter Tab -->
        <div class="filter-tabs">
            <a href="leaderboard.php?period=all_time" class="tab-btn <?php echo $period == 'all_time' ? 'active' : ''; ?>">All Time</a>
            <a href="leaderboard.php?period=bulanan" class="tab-btn <?php echo $period == 'bulanan' ? 'active' : ''; ?>">Bulanan</a>
            <a href="leaderboard.php?period=mingguan" class="tab-btn <?php echo $period == 'mingguan' ? 'active' : ''; ?>">Mingguan (Reset Senin)</a>
            <a href="leaderboard.php?period=harian" class="tab-btn <?php echo $period == 'harian' ? 'active' : ''; ?>">Harian</a>
        </div>

        <?php
        // Pecah array menjadi Top 3 (untuk podium) dan Sisanya (untuk list)
        $top3 = array_slice($students, 0, 3);
        ?>

        <?php if (count($students) === 0): ?>
            <!-- Tampilan Kosong Sesuai Permintaan User -->
            <div class="list-card" style="padding: 40px; text-align: center; border: 1.5px dashed #CBD5E1;">
                <span class="material-icons" style="font-size: 3rem; color: #94A3B8; margin-bottom: 12px;">leaderboard</span>
                <h3 style="font-size: 1.1rem; color: var(--text-dark); font-weight: 700; margin-bottom: 6px;">Leaderboard Masih Kosong</h3>
                <p style="font-size: 0.85rem; color: var(--text-light); line-height: 1.5; max-width: 400px; margin-left: auto; margin-right: auto;">Belum ada peserta yang aktif atau login pada periode ini. Jadilah yang pertama masuk ke dalam papan peringkat dengan terus belajar!</p>
            </div>
        <?php else: ?>
            <!-- Render Podium untuk Top 3 -->
            <div class="podium-container">
                <!-- Juara 2 -->
                <?php if (isset($top3[1])): ?>
                    <div class="podium-card podium-2nd">
                        <div class="rank-badge">2</div>
                        <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=<?php echo urlencode($top3[1]['nama']); ?>" class="podium-avatar" alt="Juara 2">
                        <div class="podium-name"><?php echo htmlspecialchars($top3[1]['nama']); ?></div>
                        <div class="podium-lvl">Level <?php echo $top3[1]['level']; ?></div>
                        <div class="podium-xp"><?php echo $top3[1]['total_xp']; ?> <span>XP</span></div>
                        <div class="row-badges" style="margin-top: 10px;">
                            <?php foreach ($top3[1]['badges'] as $badge): ?>
                                <div class="row-badge-icon" data-tooltip="<?php echo htmlspecialchars($badge['nama_badge']); ?>">
                                    <span class="material-icons"><?php echo htmlspecialchars($badge['icon_name']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Juara 1 -->
                <?php if (isset($top3[0])): ?>
                    <div class="podium-card podium-1st">
                        <div class="rank-badge">1</div>
                        <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=<?php echo urlencode($top3[0]['nama']); ?>" class="podium-avatar" alt="Juara 1">
                        <div class="podium-name"><?php echo htmlspecialchars($top3[0]['nama']); ?></div>
                        <div class="podium-lvl">Level <?php echo $top3[0]['level']; ?></div>
                        <div class="podium-xp"><?php echo $top3[0]['total_xp']; ?> <span>XP</span></div>
                        <div class="row-badges" style="margin-top: 10px;">
                            <?php foreach ($top3[0]['badges'] as $badge): ?>
                                <div class="row-badge-icon" data-tooltip="<?php echo htmlspecialchars($badge['nama_badge']); ?>">
                                    <span class="material-icons"><?php echo htmlspecialchars($badge['icon_name']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Juara 3 -->
                <?php if (isset($top3[2])): ?>
                    <div class="podium-card podium-3rd">
                        <div class="rank-badge">3</div>
                        <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=<?php echo urlencode($top3[2]['nama']); ?>" class="podium-avatar" alt="Juara 3">
                        <div class="podium-name"><?php echo htmlspecialchars($top3[2]['nama']); ?></div>
                        <div class="podium-lvl">Level <?php echo $top3[2]['level']; ?></div>
                        <div class="podium-xp"><?php echo $top3[2]['total_xp']; ?> <span>XP</span></div>
                        <div class="row-badges" style="margin-top: 10px;">
                            <?php foreach ($top3[2]['badges'] as $badge): ?>
                                <div class="row-badge-icon" data-tooltip="<?php echo htmlspecialchars($badge['nama_badge']); ?>">
                                    <span class="material-icons"><?php echo htmlspecialchars($badge['icon_name']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Render List Peringkat Lengkap 1 s/d 10 -->
            <div class="list-card">
                <?php foreach ($students as $student): ?>
                    <?php 
                    $isCurrentUser = ($student['id'] == $userId);
                    $rowClass = $isCurrentUser ? 'list-row current-user' : 'list-row';
                    ?>
                    <div class="<?php echo $rowClass; ?>">
                        <div class="row-rank">#<?php echo $student['rank']; ?></div>
                        <div class="row-profile">
                            <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=<?php echo urlencode($student['nama']); ?>" class="row-avatar" alt="Avatar">
                            <div>
                                <div class="row-name"><?php echo htmlspecialchars($student['nama']); ?></div>
                                <div class="row-lvl">Level <strong><?php echo $student['level']; ?></strong></div>
                            </div>
                        </div>
                        <div class="row-xp">
                            <span class="material-icons" style="font-size: 1.1rem; color: #10B981;">bolt</span>
                            <span><?php echo $student['total_xp']; ?> XP</span>
                        </div>
                        <div class="row-badges">
                            <?php foreach ($student['badges'] as $badge): ?>
                                <div class="row-badge-icon" data-tooltip="<?php echo htmlspecialchars($badge['nama_badge']); ?>">
                                    <span class="material-icons"><?php echo htmlspecialchars($badge['icon_name']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
