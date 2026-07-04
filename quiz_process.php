<?php
// quiz_process.php
session_start();
require_once 'config/database.php';
require_once 'includes/GamificationEngine.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$gamification = new GamificationEngine($conn);

// PROSES 1: INISIALISASI ATAU AMBIL SOAL KUIS (ACAK SOAL & PILIHAN JAWABAN)
if (isset($_GET['action']) && $_GET['action'] == 'start') {
    $babId = intval($_GET['bab_id']);
    if ($babId < 1 || $babId > 5) $babId = 1;
    
    // Tarik 10 soal acak dari database (menggunakan RANDOM() SQLite)
    $qStmt = $conn->prepare("SELECT id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban_benar FROM quiz_questions WHERE bab_id = ? ORDER BY RANDOM() LIMIT 10");
    $qStmt->execute([$babId]);
    $results = $qStmt->fetchAll();

    if (empty($results)) {
        // Jika soal belum di-seed, paksa redirect kembali
        header("Location: quiz.php");
        exit();
    }

    $questions = [];
    foreach ($results as $row) {
        // Melakukan pengacakan opsi jawaban tanpa merusak relasi kunci jawaban asli
        $options = [
            ['key' => 'A', 'val' => $row['opsi_a']],
            ['key' => 'B', 'val' => $row['opsi_b']],
            ['key' => 'C', 'val' => $row['opsi_c']],
            ['key' => 'D', 'val' => $row['opsi_d']]
        ];
        shuffle($options); // Mengacak urutan visual pilihan ganda

        $questions[] = [
            'id' => $row['id'],
            'pertanyaan' => $row['pertanyaan'],
            'options' => $options,
            'jawaban_benar' => $row['jawaban_benar']
        ];
    }

    // Set Session State Kuis
    $_SESSION['quiz_bab_id'] = $babId;
    $_SESSION['quiz_questions'] = $questions;
    $_SESSION['quiz_current_index'] = 0;
    $_SESSION['quiz_score'] = 0;
    $_SESSION['quiz_xp_total'] = 0;
    $_SESSION['quiz_start_time'] = time(); // Hitung mundur 15 menit

    header("Location: quiz_playground.php");
    exit();
}

// PROSES 2: SUBMIT JAWABAN PER NOMOR (NAVIGASI SEARAH)
if (isset($_POST['submit_answer'])) {
    if (!isset($_SESSION['quiz_questions'])) {
        header("Location: quiz.php");
        exit();
    }

    $currentIndex = $_SESSION['quiz_current_index'];
    $questions = $_SESSION['quiz_questions'];
    $userAnswer = $_POST['answer'] ?? ''; // Radio button pilihan

    // Bandingkan dengan kunci jawaban
    if ($userAnswer != '') {
        $correctKey = $questions[$currentIndex]['jawaban_benar'];
        
        if ($userAnswer === $correctKey) {
            $_SESSION['quiz_score'] += 10; // +10 skor per jawaban benar
            $_SESSION['quiz_xp_total'] += 10; // +10 XP per jawaban benar
            $gamification->addXp($userId, 10, 'quiz_benar');
        }
    }

    // Maju ke soal berikutnya
    $_SESSION['quiz_current_index']++;

    // Jika sudah soal terakhir (indeks 10), masuk ke halaman kalkulasi final
    if ($_SESSION['quiz_current_index'] >= count($questions)) {
        header("Location: quiz_process.php?action=finalize");
    } else {
        header("Location: quiz_playground.php");
    }
    exit();
}

// PROSES 3: KALKULASI FINAL, PENENTUAN BONUS XP & BADGES
if (isset($_GET['action']) && $_GET['action'] == 'finalize') {
    if (!isset($_SESSION['quiz_bab_id'])) {
        header("Location: quiz.php");
        exit();
    }

    $babId = $_SESSION['quiz_bab_id'];
    $finalScore = $_SESSION['quiz_score'];
    $xpEarnedFromQuizResult = 50; // XP Pokok menyelesaikan kuis
    
    // Berikan XP Pokok
    $gamification->addXp($userId, 50, 'quiz_selesai');

    // Tambahan bonus XP jika skor >= 80
    if ($finalScore >= 80) {
        $xpEarnedFromQuizResult += 100;
        $gamification->addXp($userId, 100, 'quiz_bonus_80');
    }

    // Lencana khusus Perfect Score jika skor 100
    if ($finalScore == 100) {
        $gamification->assignBadge($userId, 'perfect_score');
    }

    // Rekam riwayat kuis
    $attemptStmt = $conn->prepare("INSERT INTO quiz_attempts (user_id, bab_id, skor, xp_diperoleh) VALUES (?, ?, ?, ?)");
    $attemptStmt->execute([$userId, $babId, $finalScore, $xpEarnedFromQuizResult]);

    // Bersihkan session kuis
    unset($_SESSION['quiz_questions']);
    unset($_SESSION['quiz_current_index']);
    unset($_SESSION['quiz_bab_id']);
    
    // Simpan flash session untuk quiz_result.php
    $_SESSION['flash_quiz_bab'] = $babId;
    $_SESSION['flash_quiz_score'] = $finalScore;
    $_SESSION['flash_quiz_xp'] = $xpEarnedFromQuizResult;

    header("Location: quiz_result.php");
    exit();
}
?>
