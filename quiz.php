<?php
// quiz.php (Halaman Seleksi Bab Kuis)
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Daftar Bab Kuis
$chapters = [
    1 => "Pengenalan Sistem Operasi",
    2 => "Manajemen Proses & Penjadwalan",
    3 => "Manajemen Memori Utama & Virtual",
    4 => "Sistem Berkas & Penyimpanan",
    5 => "Keamanan & Proteksi Sistem Operasi"
];

// Ambil riwayat skor tertinggi untuk masing-masing bab
$scores = [];
foreach ($chapters as $babId => $babTitle) {
    $scoreStmt = $conn->prepare("SELECT MAX(skor) as max_score FROM quiz_attempts WHERE user_id = ? AND bab_id = ?");
    $scoreStmt->execute([$userId, $babId]);
    $res = $scoreStmt->fetch();
    $scores[$babId] = ($res && $res['max_score'] !== null) ? $res['max_score'] : null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Selector - LMS Gamifikasi</title>
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

        /* Welcome Box */
        .welcome-box {
            background: var(--primary-gradient);
            color: #FFFFFF;
            border-radius: 20px;
            padding: 35px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(108, 76, 241, 0.15);
            margin-bottom: 30px;
        }
        .welcome-box::after {
            content: "";
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
            right: -50px;
            top: -50px;
        }
        .welcome-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .welcome-desc {
            font-size: 0.95rem;
            opacity: 0.9;
            line-height: 1.5;
        }

        /* Chapters list container */
        .chapters-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .chapter-card {
            background: var(--card-bg);
            border-radius: 18px;
            padding: 25px;
            border: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.01);
            transition: all 0.25s ease;
        }
        .chapter-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(108, 76, 241, 0.06);
            border-color: rgba(108, 76, 241, 0.2);
        }
        
        .chapter-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .chapter-icon {
            width: 50px;
            height: 50px;
            background: #F1EFFF;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6C4CF1;
        }
        .chapter-icon .material-icons {
            font-size: 1.8rem;
        }
        .chapter-num {
            font-size: 0.8rem;
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .chapter-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-top: 2px;
        }
        
        .score-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }
        .score-val {
            font-size: 0.85rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 8px;
        }
        .score-val.perfect {
            background: #D1FAE5;
            color: #065F46;
        }
        .score-val.passed {
            background: #FEF3C7;
            color: #92400E;
        }
        .score-val.none {
            background: #F1F5F9;
            color: #475569;
        }

        .btn-start {
            background: #6C4CF1;
            color: #FFFFFF;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: background 0.2s, transform 0.2s;
            box-shadow: 0 4px 12px rgba(108, 76, 241, 0.15);
            font-size: 0.9rem;
        }
        .btn-start:hover {
            background: #5B3EDE;
            transform: translateY(-1px);
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
                <h1>Arena Kuis Sistem Operasi 🎯</h1>
                <p>Uji pemahaman teoritis Anda tentang materi yang telah dipelajari.</p>
            </div>
        </div>

        <div class="welcome-box">
            <div class="welcome-title">Instruksi Kuis!</div>
            <div class="welcome-desc">Kuis terdiri dari **10 soal pilihan ganda** yang diacak dari bank soal. Waktu pengerjaan adalah **15 menit**. Skor $\ge 80$ akan memberikan bonus **+100 XP** dan skor sempurna (100) akan membuka lencana **Perfect Score**!</div>
        </div>

        <div class="chapters-container">
            <?php foreach ($chapters as $babId => $babTitle): ?>
                <div class="chapter-card">
                    <div class="chapter-info">
                        <div class="chapter-icon">
                            <span class="material-icons">assignment</span>
                        </div>
                        <div>
                            <div class="chapter-num">Kuis Bab <?php echo $babId; ?></div>
                            <div class="chapter-name"><?php echo htmlspecialchars($babTitle); ?></div>
                        </div>
                    </div>
                    
                    <div class="score-info">
                        <?php if ($scores[$babId] !== null): ?>
                            <?php if ($scores[$babId] == 100): ?>
                                <span class="score-val perfect">Skor Terbaik: 100/100</span>
                            <?php else: ?>
                                <span class="score-val passed">Skor Terbaik: <?php echo $scores[$babId]; ?>/100</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="score-val none">Belum Pernah Mencoba</span>
                        <?php endif; ?>
                        
                        <a href="quiz_process.php?action=start&bab_id=<?php echo $babId; ?>" class="btn-start">
                            <span>Mulai Kuis</span>
                            <span class="material-icons" style="font-size: 1.1rem;">play_arrow</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
