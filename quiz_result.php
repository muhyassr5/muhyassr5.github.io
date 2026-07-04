<?php
// quiz_result.php (Halaman Ringkasan Hasil Kuis)
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['flash_quiz_score'])) {
    header("Location: quiz.php");
    exit();
}

$score = $_SESSION['flash_quiz_score'];
$xp = $_SESSION['flash_quiz_xp'];
$babId = $_SESSION['flash_quiz_bab'];

// Hapus flash session setelah diambil agar tidak berulang saat refresh
unset($_SESSION['flash_quiz_score']);
unset($_SESSION['flash_quiz_xp']);
unset($_SESSION['flash_quiz_bab']);

// Daftar Bab Kuis
$chapters = [
    1 => "Pengenalan Sistem Operasi",
    2 => "Manajemen Proses & Penjadwalan",
    3 => "Manajemen Memori Utama & Virtual",
    4 => "Sistem Berkas & Penyimpanan",
    5 => "Keamanan & Proteksi Sistem Operasi"
];
$babTitle = $chapters[$babId] ?? "Materi Sistem Operasi";

// Hitung statistik jawaban
$correctAnswers = $score / 10;
$wrongAnswers = 10 - $correctAnswers;

// Lencana yang diperoleh (jika score = 100, perfect score; jika >= 90 dan ini kuis pertama, dll)
$badgeUnlocked = null;
if ($score == 100) {
    $badgeUnlocked = [
        'name' => 'Perfect Score',
        'desc' => 'Menjawab seluruh soal dengan benar.',
        'icon' => 'star'
    ];
} elseif ($score >= 90) {
    $badgeUnlocked = [
        'name' => 'Quiz Master',
        'desc' => 'Nilai kuis di atas 90.',
        'icon' => 'psychology'
    ];
}

// Next Bab Route
$nextBabId = $babId + 1;
$hasNextBab = array_key_exists($nextBabId, $chapters);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Kuis - LMS Gamifikasi</title>
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
            --primary-color: #6C4CF1;
            --accent-green: #10B981;
            --accent-red: #EF4444;
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
            justify-content: center;
            align-items: center;
        }

        @media (max-width: 900px) {
            .content-area {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
        }

        .result-card {
            background: #FFFFFF;
            width: 100%;
            max-width: 580px;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(108, 76, 241, 0.05);
            border: 1px solid var(--border-color);
            text-align: center;
        }

        .trophy-icon {
            font-size: 80px;
            margin-bottom: 15px;
            animation: bounce 2s infinite alternate;
        }
        
        .result-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
        }
        .result-subtitle {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 30px;
        }

        /* Stat Grid (mockup style) */
        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            background: #F8FAFC;
            padding: 25px;
            border-radius: 16px;
            border: 1.5px dashed #E2E8F0;
            margin-bottom: 25px;
        }
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .stat-item.border-right {
            border-right: 1.5px solid #E2E8F0;
        }
        .stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .stat-val {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text-dark);
        }
        .stat-val span {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-light);
        }
        .xp-gain {
            color: #6C4CF1;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Answer Count row */
        .answer-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }
        .answer-box {
            background: #F8FAFC;
            padding: 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-light);
            border: 1px solid var(--border-color);
        }
        .answer-box strong {
            font-size: 1rem;
            font-weight: 700;
        }

        /* Badge Card Display */
        .badge-unlock-card {
            background: #FFFBEB;
            border: 1px solid #FDE68A;
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            text-align: left;
            margin-bottom: 35px;
        }
        .badge-unlock-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #FEF3C7;
            color: #D97706;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .badge-unlock-info h4 {
            font-size: 0.9rem;
            font-weight: 700;
            color: #92400E;
        }
        .badge-unlock-info p {
            font-size: 0.75rem;
            color: #B45309;
            margin-top: 2px;
        }

        /* Footer buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
        }
        .btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 12px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            gap: 6px;
        }
        .btn-outline {
            border: 1.5px solid var(--border-color);
            background: #FFFFFF;
            color: var(--text-light);
        }
        .btn-outline:hover {
            border-color: #6C4CF1;
            color: #6C4CF1;
            background: #F5F3FF;
        }
        .btn-primary {
            background: var(--primary-color);
            color: #FFFFFF;
            border: none;
            box-shadow: 0 4px 15px rgba(108, 76, 241, 0.2);
        }
        .btn-primary:hover {
            background: #5B3EDE;
            transform: translateY(-1px);
        }

        @keyframes bounce { from { transform: translateY(0); } to { transform: translateY(-8px); } }
    </style>
</head>
<body>

    <!-- Sidebar Menu -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="content-area">
        <div class="result-card">
            <div class="trophy-icon">🏆</div>
            
            <h1 class="result-title">Kuis Selesai!</h1>
            <p class="result-subtitle">Bab <?php echo $babId; ?> - <?php echo htmlspecialchars($babTitle); ?></p>
            
            <div class="stat-grid">
                <div class="stat-item border-right">
                    <span class="stat-label">Nilai Anda</span>
                    <span class="stat-val"><?php echo $score; ?><span>/100</span></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">XP Diperoleh</span>
                    <span class="stat-val xp-gain">
                        <span class="material-icons">bolt</span>
                        <span>+<?php echo $xp; ?> XP</span>
                    </span>
                </div>
            </div>
            
            <div class="answer-row">
                <div class="answer-box">
                    Jawaban Benar: <strong style="color: var(--accent-green);"><?php echo $correctAnswers; ?></strong> Soal
                </div>
                <div class="answer-box">
                    Jawaban Salah: <strong style="color: var(--accent-red);"><?php echo $wrongAnswers; ?></strong> Soal
                </div>
            </div>

            <!-- Menampilkan Lencana Jika Didapat -->
            <?php if ($badgeUnlocked): ?>
                <div class="badge-unlock-card">
                    <div class="badge-unlock-icon">
                        <span class="material-icons"><?php echo $badgeUnlocked['icon']; ?></span>
                    </div>
                    <div class="badge-unlock-info">
                        <h4>Lencana Baru Terbuka: <?php echo htmlspecialchars($badgeUnlocked['name']); ?></h4>
                        <p><?php echo htmlspecialchars($badgeUnlocked['desc']); ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="badge-unlock-card" style="background: #F8FAFC; border-color: var(--border-color);">
                    <div class="badge-unlock-icon" style="background: #E2E8F0; color: #64748B;">
                        <span class="material-icons">lock</span>
                    </div>
                    <div class="badge-unlock-info">
                        <h4 style="color: var(--text-dark);">Belum Ada Lencana Baru</h4>
                        <p style="color: var(--text-light);">Dapatkan nilai sempurna 100 untuk membuka lencana Perfect Score.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="quiz_process.php?action=start&bab_id=<?php echo $babId; ?>" class="btn btn-outline">
                    <span class="material-icons" style="font-size: 1.15rem;">replay</span>
                    <span>Ulangi Kuis</span>
                </a>
                
                <?php if ($hasNextBab): ?>
                    <a href="quiz_process.php?action=start&bab_id=<?php echo $nextBabId; ?>" class="btn btn-primary">
                        <span>Lanjut ke Bab <?php echo $nextBabId; ?></span>
                        <span class="material-icons" style="font-size: 1.15rem;">arrow_forward</span>
                    </a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-primary">
                        <span>Lanjut ke Dashboard</span>
                        <span class="material-icons" style="font-size: 1.15rem;">dashboard</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Notification Engine (Level Up / Confetti) -->
    <?php include 'includes/notifications.php'; ?>
</body>
</html>
