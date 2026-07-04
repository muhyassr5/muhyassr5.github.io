<?php
// quiz_playground.php (Halaman Pengerjaan Soal Kuis)
session_start();

if (!isset($_SESSION['quiz_questions']) || !isset($_SESSION['quiz_current_index'])) {
    header("Location: quiz.php");
    exit();
}

$currentIndex = $_SESSION['quiz_current_index'];
$questions = $_SESSION['quiz_questions'];
$currentQuestion = $questions[$currentIndex];

// Kalkulasi sisa waktu (15 menit = 900 detik)
$elapsedTime = time() - $_SESSION['quiz_start_time'];
$remainingTime = 900 - $elapsedTime;

if ($remainingTime <= 0) {
    header("Location: quiz_process.php?action=finalize");
    exit();
}

// Bab Title Mapping
$chapters = [
    1 => "Pengenalan Sistem Operasi",
    2 => "Manajemen Proses & Penjadwalan",
    3 => "Manajemen Memori Utama & Virtual",
    4 => "Sistem Berkas & Penyimpanan",
    5 => "Keamanan & Proteksi Sistem Operasi"
];
$babId = $_SESSION['quiz_bab_id'];
$babTitle = $chapters[$babId] ?? "Materi Sistem Operasi";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Arena - LMS Gamifikasi</title>
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
            --hover-color: #7A5FFF;
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

        /* Quiz Container */
        .quiz-container {
            background: #FFFFFF;
            width: 100%;
            max-width: 750px;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(108, 76, 241, 0.04);
            border: 1px solid var(--border-color);
        }
        
        .quiz-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #F0F4F8;
            padding-bottom: 15px;
        }
        .quiz-header-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-dark);
        }
        .timer-box {
            display: flex;
            align-items: center;
            background: #F1EFFF;
            color: #6C4CF1;
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .timer-box .material-icons {
            margin-right: 6px;
            font-size: 1.15rem;
        }

        .progress-bar-wrapper {
            width: 100%;
            background: #EAF4FF;
            height: 8px;
            border-radius: 4px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background: var(--primary-color);
            transition: width 0.4s ease;
        }

        .question-number {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .question-text {
            font-size: 1.15rem;
            color: var(--text-dark);
            font-weight: 600;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        /* Options list */
        .options-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 35px;
        }
        .option-item {
            border: 2px solid #F1F5F9;
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #FFFFFF;
        }
        .option-item:hover {
            border-color: #8A73FF;
            background: #F8F6FF;
            transform: translateY(-1px);
        }
        .option-item input[type="radio"] {
            display: none;
        }
        .option-label-letter {
            width: 30px;
            height: 30px;
            background: #F1F5F9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--text-light);
            margin-right: 16px;
            transition: all 0.2s ease;
            font-size: 0.85rem;
        }
        .option-item input[type="radio"]:checked + .option-label-letter {
            background: var(--primary-color);
            color: #FFFFFF;
        }
        .option-item input[type="radio"]:checked ~ .option-text {
            color: #6C4CF1;
            font-weight: 600;
        }
        .option-text {
            font-size: 0.95rem;
            color: #475569;
            font-weight: 500;
        }

        /* Action buttons footer */
        .quiz-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }
        .btn-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #FFFFFF;
            border: 1.5px solid var(--border-color);
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-light);
            text-decoration: none;
            cursor: not-allowed;
            opacity: 0.4;
        }
        
        .btn-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: #FFFFFF;
            border: none;
            padding: 12px 28px;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(108, 76, 241, 0.2);
            gap: 6px;
        }
        .btn-submit:hover {
            background: var(--hover-color);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(108, 76, 241, 0.25);
        }
    </style>
</head>
<body>

    <!-- Sidebar Menu -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="content-area">
        <div class="quiz-container">
            <div class="quiz-header">
                <div class="quiz-header-title">BAB <?php echo $babId; ?> - <?php echo htmlspecialchars($babTitle); ?></div>
                <div class="timer-box" id="timerContainer">
                    <span class="material-icons">timer</span>
                    <span id="countdownDisplay">15:00</span>
                </div>
            </div>

            <!-- Progres Bar kuis -->
            <div class="progress-bar-wrapper">
                <div class="progress-bar-fill" style="width: <?php echo (($currentIndex) / count($questions)) * 100; ?>%;"></div>
            </div>

            <form action="quiz_process.php" method="POST" id="quizForm">
                <div class="question-number">Soal <?php echo ($currentIndex + 1); ?> dari <?php echo count($questions); ?></div>
                <div class="question-text">
                    <?php echo htmlspecialchars($currentQuestion['pertanyaan']); ?>
                </div>

                <div class="options-list">
                    <?php foreach ($currentQuestion['options'] as $option): ?>
                        <label class="option-item">
                            <input type="radio" name="answer" value="<?php echo $option['key']; ?>" required>
                            <div class="option-label-letter"><?php echo $option['key']; ?></div>
                            <div class="option-text"><?php echo htmlspecialchars($option['val']); ?></div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="quiz-actions">
                    <button type="button" class="btn-nav" disabled>
                        <span class="material-icons" style="font-size: 1.15rem;">arrow_back</span>
                        <span>Sebelumnya</span>
                    </button>

                    <button type="submit" name="submit_answer" class="btn-submit">
                        <span><?php echo ($currentIndex === count($questions) - 1) ? 'Selesaikan Kuis' : 'Selanjutnya'; ?></span>
                        <span class="material-icons" style="font-size: 1.15rem;">arrow_forward</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Hitung mundur sinkron aman sisi client
        let totalSecondsLeft = <?php echo $remainingTime; ?>;
        const countdownDisplay = document.getElementById('countdownDisplay');

        const timerInterval = setInterval(() => {
            if (totalSecondsLeft <= 0) {
                clearInterval(timerInterval);
                alert("Waktu pengerjaan kuis telah habis! Kuis Anda akan dihitung otomatis.");
                window.location.href = "quiz_process.php?action=finalize";
            } else {
                totalSecondsLeft--;
                let minutes = Math.floor(totalSecondsLeft / 60);
                let seconds = totalSecondsLeft % 60;
                
                minutes = minutes < 10 ? '0' + minutes : minutes;
                seconds = seconds < 10 ? '0' + seconds : seconds;
                
                countdownDisplay.textContent = `${minutes}:${seconds}`;
            }
        }, 1000);
    </script>
</body>
</html>
