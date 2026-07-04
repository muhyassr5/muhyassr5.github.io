<?php
// materi.php (Halaman Materi Pembelajaran)
session_start();
require_once 'config/database.php';
require_once 'includes/GamificationEngine.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$gamification = new GamificationEngine($conn);

// Tangkap bab_id dan sub_id dari query string
$babId = isset($_GET['bab_id']) ? intval($_GET['bab_id']) : 1;
if ($babId < 1 || $babId > 5) $babId = 1;

$subId = isset($_GET['sub_id']) ? intval($_GET['sub_id']) : 1;
if ($subId < 1 || $subId > 5) $subId = 1;

// Konfigurasi Konten Pembelajaran
$chapters = [
    1 => [
        'title' => "Pengenalan Sistem Operasi",
        'sub' => [
            1 => [
                'title' => "1.1 Apa itu Sistem Operasi",
                'content' => "Sistem Operasi (Operating System / SO) adalah perangkat lunak sistem yang berperan sebagai penghubung antara pengguna, aplikasi, dan perangkat keras komputer. Sistem operasi mengelola seluruh sumber daya sistem (resource manager) dan menyediakan layanan dasar bagi program aplikasi agar dapat berjalan dengan harmonis.",
                'diagram' => true
            ],
            2 => [
                'title' => "1.2 Fungsi Sistem Operasi",
                'content' => "Fungsi utama Sistem Operasi meliputi:\n\n1. **Resource Manager**: Mengelola alokasi CPU, memori utama, dan perangkat input/output bagi proses-proses yang aktif.\n2. **User Interface (Antarmuka)**: Menyediakan jembatan komunikasi berupa Command Line Interface (CLI) atau Graphical User Interface (GUI).\n3. **Platform Program**: Menyediakan lingkungan stabil di mana perangkat lunak aplikasi (seperti Microsoft Word atau Chrome) dapat berjalan.\n4. **Pengamanan Data**: Melindungi file dan sistem dari gangguan malware atau akses yang tidak berizin.",
                'diagram' => false
            ],
            3 => [
                'title' => "1.3 Tujuan Sistem Operasi",
                'content' => "SO dirancang dengan tiga tujuan utama berikut:\n\n* **Kemudahan (Convenience)**: Membuat komputer menjadi lebih ramah, mudah digunakan, dan intuitif bagi pengguna biasa.\n* **Efisiensi (Efficiency)**: Memastikan penggunaan CPU, RAM, dan kapasitas penyimpanan berjalan sehemat dan seoptimal mungkin.\n* **Kemampuan Berkembang (Ability to evolve)**: Mempermudah programmer untuk mengintegrasikan fitur-fitur baru tanpa perlu merombak arsitektur sistem operasi secara keseluruhan.",
                'diagram' => false
            ],
            4 => [
                'title' => "1.4 Contoh Sistem Operasi",
                'content' => "Sistem operasi sangat bervariasi sesuai dengan kebutuhan perangkat keras:\n\n* **Windows**: Sistem operasi berbayar paling populer untuk PC desktop dan laptop.\n* **macOS & iOS**: Sistem operasi tertutup (proprietary) eksklusif untuk jajaran produk buatan Apple.\n* **Linux**: Sistem operasi open-source (gratis) yang mendominasi pasar server dunia dan perangkat IoT.\n* **Android**: Sistem operasi mobile berbasis Linux yang memimpin pasar smartphone dunia.",
                'diagram' => false
            ],
            5 => [
                'title' => "1.5 Ringkasan Bab 1",
                'content' => "Sistem operasi adalah jantung dari ekosistem komputer. Tanpa sistem operasi, perangkat keras komputer hanyalah sekumpulan logam mati yang tidak dapat merespons instruksi apa pun. Sistem operasi bertugas mengoordinasikan interaksi antara manusia (*brainware*), aplikasi (*software*), dan mesin (*hardware*) secara adil dan efisien.",
                'diagram' => false
            ]
        ]
    ],
    2 => [
        'title' => "Manajemen Proses & Penjadwalan",
        'sub' => [
            1 => [
                'title' => "2.1 Konsep Proses",
                'content' => "Proses didefinisikan sebagai program yang sedang dieksekusi oleh CPU. Setiap proses memiliki siklus hidup (State) seperti:\n\n* **New**: Proses sedang dibuat.\n* **Running**: CPU sedang mengeksekusi instruksi proses tersebut.\n* **Waiting / Blocked**: Proses terhenti sementara menunggu input/output selesai.\n* **Ready**: Proses siap dieksekusi dan menunggu giliran dari penjadwal CPU.\n* **Terminated**: Proses telah selesai dieksekusi.",
                'diagram' => false
            ],
            2 => [
                'title' => "2.2 Penjadwalan CPU",
                'content' => "Penjadwalan CPU bertugas mengalokasikan waktu CPU kepada proses-proses yang antre di memori utama. Algoritma penjadwalan CPU meliputi:\n\n1. **FCFS (First Come, First Served)**: Siapa yang datang pertama, dia yang dilayani pertama.\n2. **SJF (Shortest Job First)**: Proses dengan estimasi waktu eksekusi tersingkat akan dieksekusi terlebih dahulu.\n3. **Round Robin (RR)**: Setiap proses diberikan waktu jatah CPU yang sama (time quantum) secara bergiliran.",
                'diagram' => false
            ],
            3 => [
                'title' => "2.3 Sinkronisasi & Deadlock",
                'content' => "Sinkronisasi diperlukan ketika beberapa proses mengakses data bersama secara simultan untuk menghindari *race condition* (kekacauan data). \n\nNamun, sinkronisasi yang kurang tepat dapat memicu **Deadlock**. Deadlock adalah kondisi saling menunggu di mana proses A memegang sumber daya X dan meminta Y, sedangkan proses B memegang sumber daya Y dan meminta X. Akibatnya, kedua proses terkunci selamanya.",
                'diagram' => false
            ],
            4 => [
                'title' => "2.4 Konsep Utas (Threads)",
                'content' => "Thread (utas) sering disebut sebagai proses ringan (*lightweight process*). Thread merupakan unit dasar dari pemanfaatan CPU.\n\nDalam satu proses, dapat berjalan beberapa thread sekaligus (Multi-threading). Thread-thread ini berbagi kode, data, dan file sistem yang sama, sehingga pengalihan konteks (context switching) jauh lebih cepat dibanding membuat proses baru.",
                'diagram' => false
            ],
            5 => [
                'title' => "2.5 Ringkasan Bab 2",
                'content' => "Manajemen proses adalah bagian vital dari Sistem Operasi untuk memastikan CPU dapat menjalankan tugas-tugas mahasiswa secara paralel dengan efisiensi tinggi, membagi waktu giliran aplikasi secara adil, serta menangani konflik sinkronisasi dan deadlock agar komputer tidak hang.",
                'diagram' => false
            ]
        ]
    ],
    3 => [
        'title' => "Manajemen Memori Utama & Virtual",
        'sub' => [
            1 => [
                'title' => "3.1 Konsep Memori",
                'content' => "Memori utama (RAM) adalah media penyimpanan sementara berkecepatan tinggi yang diakses langsung oleh CPU sebelum data diproses. Karena kapasitas RAM terbatas, sistem operasi harus mengatur alamat memori agar aplikasi-aplikasi yang sedang berjalan tidak saling menindih data satu sama lain.",
                'diagram' => false
            ],
            2 => [
                'title' => "3.2 Paging & Segmentasi",
                'content' => "Untuk mempermudah manajemen memori, SO menggunakan dua teknik pengalamatan non-kontigu:\n\n* **Paging**: Memori fisik dibagi menjadi blok berukuran tetap bernama *Frames*, sedangkan memori logika program dibagi menjadi *Pages* berukuran sama.\n* **Segmentasi**: Memori dibagi menjadi segmen-segmen logis berdasarkan struktur program (seperti modul main, fungsi, objek, atau tumpukan/stack).",
                'diagram' => false
            ],
            3 => [
                'title' => "3.3 Memori Virtual",
                'content' => "Memori Virtual adalah teknik revolusioner yang memisahkan memori logika program dari memori fisik (RAM) komputer. \n\nDengan memori virtual, mahasiswa dapat menjalankan aplikasi yang ukurannya melebihi kapasitas RAM fisik yang tersedia. Sistem operasi akan menyembunyikan kekurangan RAM fisik dengan cara meminjam sebagian ruang dari harddisk (virtual memory swap file).",
                'diagram' => false
            ],
            4 => [
                'title' => "3.4 Page Fault & Swapping",
                'content' => "Ketika CPU meminta halaman memori logika yang belum dimuat ke RAM fisik, terjadilah kejadian yang disebut **Page Fault**.\n\nSistem operasi akan merespons dengan memotong eksekusi program sejenak, mencari halaman yang diminta di harddisk, lalu menyalinnya ke RAM fisik (*swapping*). Jika RAM penuh, SO menggunakan algoritma penggantian halaman seperti FIFO (First In First Out) atau LRU (Least Recently Used) untuk mengeluarkan halaman terlama dari RAM.",
                'diagram' => false
            ],
            5 => [
                'title' => "3.5 Ringkasan Bab 3",
                'content' => "Manajemen memori bertugas menjaga RAM agar tetap bersih, memetakan alamat logika ke fisik secara efisien, serta menyediakan Memori Virtual sehingga komputer tetap mampu menjalankan program-program berat secara multitasking tanpa hambatan.",
                'diagram' => false
            ]
        ]
    ],
    4 => [
        'title' => "Sistem Berkas & Penyimpanan",
        'sub' => [
            1 => [
                'title' => "4.1 Konsep Berkas (File)",
                'content' => "Berkas (*file*) adalah unit penyimpanan logika yang diabstraksikan oleh sistem operasi pada media penyimpanan fisik (seperti SSD/HDD). Setiap berkas memiliki atribut dasar berupa nama berkas, tipe berkas (ekstensi), ukuran berkas, penanda lokasi fisik, hak proteksi akses, dan riwayat waktu pembuatan berkas.",
                'diagram' => false
            ],
            2 => [
                'title' => "4.2 Struktur Direktori",
                'content' => "Direktori atau folder digunakan untuk mengelompokkan berkas secara logis agar mudah dikelola oleh pengguna. Struktur direktori modern menggunakan format **Pohon Hirarki (Tree-Structured Directory)**, di mana di bawah direktori utama (root) dapat berisi berkas maupun sub-direktori lainnya secara rekursif.",
                'diagram' => false
            ],
            3 => [
                'title' => "4.3 Metode Akses Berkas",
                'content' => "Ada dua metode utama bagi aplikasi untuk mengakses data di dalam berkas:\n\n* **Akses Sekuensial (Sequential Access)**: Data dibaca baris demi baris secara berurutan dari awal sampai akhir berkas (misal pada pita kaset data).\n* **Akses Langsung (Direct/Random Access)**: Aplikasi dapat langsung melompat ke bagian blok mana saja di dalam berkas tanpa perlu membaca blok sebelumnya (sangat efisien untuk database).",
                'diagram' => false
            ],
            4 => [
                'title' => "4.4 Metode Alokasi Berkas",
                'content' => "Sistem operasi mengalokasikan ruang disk untuk berkas melalui tiga metode:\n\n1. **Contiguous Allocation**: Berkas disimpan pada blok disk yang saling berdampingan secara fisik. Sangat cepat, namun memicu fragmentasi.\n2. **Linked Allocation**: Setiap blok berkas terhubung melalui pointer ke blok berikutnya. Bebas fragmentasi, namun akses lambat.\n3. **Indexed Allocation**: Menggunakan satu blok khusus bernama indeks tabel yang berisi alamat pointer ke seluruh blok berkas lainnya.",
                'diagram' => false
            ],
            5 => [
                'title' => "4.5 Ringkasan Bab 4",
                'content' => "Sistem berkas bertindak sebagai antarmuka yang menyembunyikan kerumitan sektor-sektor fisik disk drive menjadi file dan folder yang bersih, serta mengontrol penempatan data agar berkas dapat diakses secara cepat dan aman.",
                'diagram' => false
            ]
        ]
    ],
    5 => [
        'title' => "Keamanan & Proteksi Sistem Operasi",
        'sub' => [
            1 => [
                'title' => "5.1 Ancaman Keamanan",
                'content' => "Sistem operasi rentan terhadap ancaman keamanan yang diklasifikasikan menjadi:\n\n* **Malware**: Perangkat lunak jahat seperti Virus (menyebar via inang), Worm (menyebar mandiri via jaringan), Trojan (menyamar sebagai aplikasi baik), dan Ransomware (menyandera berkas dengan enkripsi).\n* **Phishing**: Penipuan memancing data kredensial pengguna.\n* **DDoS (Distributed Denial of Service)**: Serangan membanjiri server target dengan lalu lintas data palsu agar server lumpuh.",
                'diagram' => false
            ],
            2 => [
                'title' => "5.2 Mekanisme Proteksi",
                'content' => "Proteksi merujuk pada mekanisme internal SO untuk mengontrol akses pengguna ke sumber daya sistem. Proteksi diimplementasikan melalui:\n\n* **Autentikasi**: Memeriksa keaslian identitas pengguna (password, biometrik, OTP, Multi-Factor Authentication).\n* **Firewall**: Menyaring paket data masuk dan keluar berdasarkan aturan keamanan jaringan.\n* **Enkripsi**: Mengubah data penting menjadi format kode rahasia agar aman saat dikirim atau disimpan.",
                'diagram' => false
            ],
            3 => [
                'title' => "5.3 Kriptografi",
                'content' => "Kriptografi adalah ilmu menyamarkan data asli (*plaintext*) menjadi data rahasia (*ciphertext*). Terdapat dua kategori utama:\n\n* **Simetris**: Menggunakan satu kunci rahasia yang sama untuk enkripsi dan dekripsi (cepat, tapi sulit mendistribusikan kunci).\n* **Asimetris**: Menggunakan pasangan kunci publik (untuk mengenkripsi) dan kunci privat (untuk mendekripsi secara rahasia).",
                'diagram' => false
            ],
            4 => [
                'title' => "5.4 Access Control List (ACL)",
                'content' => "ACL adalah tabel otorisasi dalam sistem operasi yang mendefinisikan daftar hak akses (seperti Read, Write, Execute) bagi setiap pengguna atau grup pengguna terhadap suatu berkas, direktori, maupun perangkat keras tertentu.",
                'diagram' => false
            ],
            5 => [
                'title' => "5.5 Ringkasan Bab 5",
                'content' => "Keamanan dan proteksi adalah lapisan pertahanan terluar komputer. Sistem operasi harus terus memperbarui pertahanannya melalui otentikasi yang ketat, penyaringan jaringan, sandi kriptografi, dan manajemen hak akses agar data rahasia mahasiswa tetap terjaga.",
                'diagram' => false
            ]
        ]
    ]
];

// Ambil data progress saat ini dari database
$progStmt = $conn->prepare("SELECT materi_selesai, video_selesai FROM materi_progress WHERE user_id = ? AND bab_id = ?");
$progStmt->execute([$userId, $babId]);
$progressData = $progStmt->fetch();

$materiSelesai = $progressData ? intval($progressData['materi_selesai']) : 0;
$videoSelesai = $progressData ? intval($progressData['video_selesai']) : 0;
$chapterProgress = ($materiSelesai * 50) + ($videoSelesai * 50);

// PROSES SUBMIT: SELESAI MEMBACA TEKS
if (isset($_POST['complete_reading'])) {
    if (!$progressData) {
        $ins = $conn->prepare("INSERT INTO materi_progress (user_id, bab_id, materi_selesai, video_selesai) VALUES (?, ?, 1, 0)");
        $ins->execute([$userId, $babId]);
    } else {
        $up = $conn->prepare("UPDATE materi_progress SET materi_selesai = 1 WHERE user_id = ? AND bab_id = ?");
        $up->execute([$userId, $babId]);
    }
    
    // Berikan hadiah XP (+20 XP untuk menyelesaikan membaca materi teks)
    $gamification->addXp($userId, 20, 'materi_selesai');
    
    header("Location: materi.php?bab_id=$babId&sub_id=$subId");
    exit();
}

// PROSES SUBMIT: NONTON VIDEO
if (isset($_POST['complete_video'])) {
    if (!$progressData) {
        $ins = $conn->prepare("INSERT INTO materi_progress (user_id, bab_id, materi_selesai, video_selesai) VALUES (?, ?, 0, 1)");
        $ins->execute([$userId, $babId]);
    } else {
        $up = $conn->prepare("UPDATE materi_progress SET video_selesai = 1 WHERE user_id = ? AND bab_id = ?");
        $up->execute([$userId, $babId]);
    }
    
    // Berikan hadiah XP (+30 XP untuk menonton video pembelajaran)
    $gamification->addXp($userId, 30, 'video_selesai');
    
    header("Location: materi.php?bab_id=$babId&sub_id=$subId");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materi Pembelajaran - LMS Gamifikasi</title>
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

        /* Top Header */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .header-title h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-dark);
        }
        .header-title p {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .bab-selector-pills {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            overflow-x: auto;
            padding-bottom: 8px;
        }
        .bab-pill {
            text-decoration: none;
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            color: var(--text-light);
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.2s;
        }
        .bab-pill.active {
            background: #6C4CF1;
            color: #FFFFFF;
            border-color: #6C4CF1;
            box-shadow: 0 4px 10px rgba(108,76,241,0.15);
        }

        /* Main Panel Layout */
        .materi-grid {
            display: grid;
            grid-template-columns: 1fr 2.5fr;
            gap: 30px;
            flex-grow: 1;
            align-items: start;
        }
        @media (max-width: 900px) {
            .materi-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Card Container style */
        .materi-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 30px rgba(0,0,0,0.02);
        }

        /* Left Side: Daftar Isi list */
        .daftar-isi-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1.5px solid #F1F5F9;
        }
        .daftar-isi-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .daftar-isi-item {
            text-decoration: none;
            color: var(--text-light);
            font-size: 0.85rem;
            font-weight: 500;
            padding: 12px 16px;
            border-radius: 10px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .daftar-isi-item:hover {
            background: #F8FAFC;
            color: #6C4CF1;
        }
        .daftar-isi-item.active {
            background: #F1EFFF;
            color: #6C4CF1;
            font-weight: 600;
        }

        /* Right Side: Content Panel */
        .content-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 20px;
        }
        .content-body {
            font-size: 0.95rem;
            color: #475569;
            line-height: 1.7;
            margin-bottom: 30px;
            white-space: pre-line; /* parse newlines */
        }

        /* Diagram CSS */
        .diagram-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
            background: #F8FAFC;
            padding: 25px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
        }
        .diagram-box {
            background: #FFFFFF;
            border: 1.5px solid var(--border-color);
            padding: 12px 20px;
            border-radius: 12px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-dark);
            box-shadow: 0 4px 8px rgba(0,0,0,0.01);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }
        .diagram-box .material-icons {
            font-size: 1.5rem;
            color: #6C4CF1;
        }
        .diagram-arrow {
            color: #94A3B8;
            font-size: 1.5rem;
        }

        /* Video Section */
        .video-section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 35px 0 15px 0;
        }
        .video-card {
            background: #0F172A;
            border-radius: 16px;
            aspect-ratio: 16/9;
            width: 100%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            cursor: pointer;
            border: none;
        }
        .video-card:hover .play-btn {
            transform: scale(1.1);
        }
        .play-btn {
            width: 65px;
            height: 65px;
            background: #EF4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: #FFFFFF;
            transition: transform 0.2s ease;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
            z-index: 2;
        }
        .video-title {
            margin-top: 15px;
            font-weight: 500;
            font-size: 0.9rem;
            opacity: 0.9;
            z-index: 2;
        }
        .video-overlay-text {
            position: absolute;
            bottom: 12px;
            left: 16px;
            font-size: 0.75rem;
            background: rgba(0, 0, 0, 0.6);
            padding: 4px 8px;
            border-radius: 6px;
            z-index: 2;
        }

        /* Footer Controls and Progress bar */
        .materi-footer {
            margin-top: 35px;
            padding-top: 25px;
            border-top: 1.5px solid #F1F5F9;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .progress-materi-bar-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #F8FAFC;
            padding: 12px 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        .progress-materi-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-light);
        }
        .progress-materi-track {
            flex-grow: 1;
            height: 8px;
            background: #E2E8F0;
            border-radius: 4px;
            margin: 0 15px;
            overflow: hidden;
        }
        .progress-materi-fill {
            height: 100%;
            background: var(--accent-green);
            border-radius: 4px;
            transition: width 0.3s;
        }
        .progress-materi-value {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--accent-green);
        }

        .materi-actions {
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
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-light);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-nav:hover:not(.disabled) {
            border-color: #6C4CF1;
            color: #6C4CF1;
            background: #F5F3FF;
        }
        .btn-nav.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        .btn-claim {
            background: #6C4CF1;
            color: #FFFFFF;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(108,76,241,0.2);
            transition: background 0.2s;
        }
        .btn-claim:hover {
            background: #5B3EDE;
        }
        .btn-claim.completed {
            background: #D1FAE5;
            color: #065F46;
            box-shadow: none;
            cursor: default;
            pointer-events: none;
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
                <h1>Materi Pembelajaran 📚</h1>
                <p>Pelajari konsep fundamental dan tonton video penjelasan untuk menyelesaikan Bab.</p>
            </div>
        </div>

        <!-- Bab selector pills -->
        <div class="bab-selector-pills">
            <?php foreach ($chapters as $id => $chap): ?>
                <a href="materi.php?bab_id=<?php echo $id; ?>&sub_id=1" class="bab-pill <?php echo $babId == $id ? 'active' : ''; ?>">
                    Bab <?php echo $id; ?>: <?php echo htmlspecialchars($chap['title']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Main Panel Grid Layout -->
        <div class="materi-grid">
            <!-- Sisi Kiri: Daftar Isi Sub-bab -->
            <div class="materi-card" style="padding: 20px;">
                <div class="daftar-isi-title">Daftar Isi</div>
                <div class="daftar-isi-list">
                    <?php 
                    $subChapters = $chapters[$babId]['sub'];
                    foreach ($subChapters as $id => $sub):
                    ?>
                        <a href="materi.php?bab_id=<?php echo $babId; ?>&sub_id=<?php echo $id; ?>" class="daftar-isi-item <?php echo $subId == $id ? 'active' : ''; ?>">
                            <span><?php echo htmlspecialchars($sub['title']); ?></span>
                            <?php if ($id === 5 && $chapterProgress == 100): ?>
                                <span class="material-icons" style="font-size: 1rem; color: var(--accent-green);">check_circle</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sisi Kanan: Teks & Video Content -->
            <div class="materi-card">
                <?php 
                $activeSub = $subChapters[$subId];
                ?>
                <h2 class="content-title"><?php echo htmlspecialchars($activeSub['title']); ?></h2>
                
                <div class="content-body">
                    <?php echo htmlspecialchars($activeSub['content']); ?>
                </div>

                <!-- Custom Diagram untuk Bab 1 Sub 1 -->
                <?php if (isset($activeSub['diagram']) && $activeSub['diagram'] === true): ?>
                    <div class="diagram-container">
                        <div class="diagram-box">
                            <span class="material-icons">account_circle</span>
                            <span>Pengguna</span>
                        </div>
                        <span class="material-icons diagram-arrow">arrow_forward</span>
                        <div class="diagram-box" style="border-color: #6C4CF1; background: #F5F3FF;">
                            <span class="material-icons">settings</span>
                            <span>Sistem Operasi</span>
                        </div>
                        <span class="material-icons diagram-arrow">arrow_forward</span>
                        <div class="diagram-box">
                            <span class="material-icons">computer</span>
                            <span>Perangkat Keras</span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Video Pembelajaran (Muncul di Sub-bab 5 / Ringkasan) -->
                <?php if ($subId === 5): ?>
                    <div class="video-section-title">Video Pembelajaran</div>
                    
                    <form method="POST" action="materi.php?bab_id=<?php echo $babId; ?>&sub_id=<?php echo $subId; ?>">
                        <?php if ($videoSelesai == 1): ?>
                            <div class="video-card" style="cursor: default;">
                                <div class="play-btn" style="background: var(--accent-green); box-shadow: none;">
                                    <span class="material-icons">check</span>
                                </div>
                                <span class="video-title">Video Penjelasan Bab <?php echo $babId; ?> Telah Ditonton</span>
                                <span class="video-overlay-text">Selesai (+30 XP)</span>
                            </div>
                        <?php else: ?>
                            <button type="submit" name="complete_video" class="video-card">
                                <div class="play-btn">
                                    <span class="material-icons">play_arrow</span>
                                </div>
                                <span class="video-title">Tonton Video Penjelasan Bab <?php echo $babId; ?></span>
                                <span class="video-overlay-text">Durasi ~5:00 (+30 XP)</span>
                            </button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>

                <!-- Footer Nav & Action Bar -->
                <div class="materi-footer">
                    <!-- Progress Bar Bab -->
                    <div class="progress-materi-bar-wrapper">
                        <span class="progress-materi-label">Progress Belajar Bab <?php echo $babId; ?></span>
                        <div class="progress-materi-track">
                            <div class="progress-materi-fill" style="width: <?php echo $chapterProgress; ?>%;"></div>
                        </div>
                        <span class="progress-materi-value"><?php echo $chapterProgress; ?>%</span>
                    </div>

                    <div class="materi-actions">
                        <!-- Navigation Buttons -->
                        <div style="display: flex; gap: 10px;">
                            <a href="materi.php?bab_id=<?php echo $babId; ?>&sub_id=<?php echo ($subId - 1); ?>" class="btn-nav <?php echo $subId == 1 ? 'disabled' : ''; ?>">
                                <span class="material-icons" style="font-size: 1.1rem;">arrow_back</span>
                                <span>Sebelumnya</span>
                            </a>
                            
                            <a href="materi.php?bab_id=<?php echo $babId; ?>&sub_id=<?php echo ($subId + 1); ?>" class="btn-nav <?php echo $subId == 5 ? 'disabled' : ''; ?>">
                                <span>Selanjutnya</span>
                                <span class="material-icons" style="font-size: 1.1rem;">arrow_forward</span>
                            </a>
                        </div>

                        <!-- Claim Reading XP Button (Hanya ada di sub-chapter selain sub 5, atau bisa diklik di mana saja) -->
                        <form method="POST" action="materi.php?bab_id=<?php echo $babId; ?>&sub_id=<?php echo $subId; ?>">
                            <?php if ($materiSelesai == 1): ?>
                                <button type="button" class="btn-claim completed">
                                    <span class="material-icons">check_circle</span>
                                    <span>Selesai Membaca (+20 XP)</span>
                                </button>
                            <?php else: ?>
                                <button type="submit" name="complete_reading" class="btn-claim">
                                    <span class="material-icons">check</span>
                                    <span>Tandai Selesai Membaca (+20 XP)</span>
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Notification Engine (Level Up / Confetti) -->
    <?php include 'includes/notifications.php'; ?>
</body>
</html>
