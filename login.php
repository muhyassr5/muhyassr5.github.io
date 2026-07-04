<?php
// login.php (Halaman Masuk)
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi!';
    } else {
        // Cari user berdasarkan email
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Email atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk LMS - LMS Gamifikasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #6C4CF1 0%, #8A73FF 100%);
            --primary-color: #6C4CF1;
            --hover-color: #5B3EDE;
            --card-bg: #FFFFFF;
            --text-dark: #1E293B;
            --text-light: #64748B;
            --border-color: #E2E8F0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background: var(--bg-gradient); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }

        .auth-container { background: var(--card-bg); width: 100%; max-width: 450px; border-radius: 24px; padding: 40px; box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15); }
        .auth-header { text-align: center; margin-bottom: 30px; }
        .auth-logo { display: inline-flex; align-items: center; justify-content: center; width: 60px; height: 60px; background: #F1EFFF; border-radius: 16px; color: var(--primary-color); margin-bottom: 15px; }
        .auth-logo .material-icons { font-size: 2.2rem; }
        .auth-title { font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin-bottom: 5px; }
        .auth-subtitle { font-size: 0.85rem; color: var(--text-light); }

        .form-group { margin-bottom: 20px; text-align: left; }
        .form-label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
        .input-wrapper { position: relative; }
        .input-wrapper .material-icons { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 1.25rem; }
        .form-input { width: 100%; padding: 14px 16px 14px 48px; border: 1.5px solid var(--border-color); border-radius: 12px; font-size: 0.9rem; color: var(--text-dark); transition: border-color 0.2s, box-shadow 0.2s; background: #F8FAFC; }
        .form-input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(108, 76, 241, 0.1); background: #FFFFFF; }

        .alert { padding: 12px 16px; border-radius: 12px; font-size: 0.85rem; font-weight: 500; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .alert-danger { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }

        .btn-submit { background: var(--primary-color); color: #FFFFFF; border: none; padding: 14px 20px; font-size: 0.95rem; font-weight: 600; border-radius: 12px; cursor: pointer; transition: all 0.2s; width: 100%; box-shadow: 0 4px 15px rgba(108, 76, 241, 0.2); }
        .btn-submit:hover { background: var(--hover-color); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(108, 76, 241, 0.3); }

        .auth-footer { text-align: center; margin-top: 25px; font-size: 0.85rem; color: var(--text-light); }
        .auth-footer a { color: var(--primary-color); text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-header">
        <div class="auth-logo">
            <span class="material-icons">auto_stories</span>
        </div>
        <div class="auth-title">Selamat Datang Kembali</div>
        <div class="auth-subtitle">Masuk ke Akun LMS Anda untuk Melanjutkan</div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <span class="material-icons" style="font-size: 1.2rem;">error_outline</span>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label class="form-label" for="email">Alamat Email</label>
            <div class="input-wrapper">
                <span class="material-icons">email</span>
                <input class="form-input" type="email" id="email" name="email" placeholder="contoh@pti.ac.id" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="input-wrapper">
                <span class="material-icons">lock</span>
                <input class="form-input" type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
            </div>
        </div>

        <button type="submit" name="login" class="btn-submit">Masuk Ke Sistem</button>
    </form>

    <div class="auth-footer">
        <span>Belum memiliki akun? </span>
        <a href="register.php">Daftar sekarang</a>
    </div>
</div>

</body>
</html>
