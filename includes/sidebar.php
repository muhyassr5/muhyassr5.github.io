<?php
// includes/sidebar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <span class="material-icons">auto_stories</span>
        <span>Gamifikasi LMS</span>
    </div>
    
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="menu-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
            <span class="material-icons">dashboard</span>
            <span>Dashboard</span>
        </a>
        <a href="materi.php" class="menu-item <?php echo $currentPage == 'materi.php' ? 'active' : ''; ?>">
            <span class="material-icons">menu_book</span>
            <span>Materi</span>
        </a>
        <a href="quiz.php" class="menu-item <?php echo in_array($currentPage, ['quiz.php', 'quiz_playground.php', 'quiz_result.php']) ? 'active' : ''; ?>">
            <span class="material-icons">assignment</span>
            <span>Quiz</span>
        </a>
        <a href="leaderboard.php" class="menu-item <?php echo $currentPage == 'leaderboard.php' ? 'active' : ''; ?>">
            <span class="material-icons">leaderboard</span>
            <span>Leaderboard</span>
        </a>
    </nav>
    
    <a href="logout.php" class="sidebar-logout">
        <span class="material-icons">logout</span>
        <span>Keluar</span>
    </a>
</aside>

<style>
/* CSS Sidebar Global */
.sidebar {
    width: 250px;
    background: #FFFFFF;
    border-right: 1px solid var(--border-color, #E2E8F0);
    display: flex;
    flex-direction: column;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 99;
    padding: 30px 20px;
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 700;
    font-size: 1.3rem;
    color: #6C4CF1;
    margin-bottom: 40px;
}

.sidebar-logo .material-icons {
    font-size: 2rem;
}

.sidebar-menu {
    display: flex;
    flex-direction: column;
    gap: 12px;
    flex-grow: 1;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px 20px;
    border-radius: 12px;
    text-decoration: none;
    color: #64748B;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.menu-item:hover {
    background: #F8FAFC;
    color: #6C4CF1;
}

.menu-item.active {
    background: #F1EFFF;
    color: #6C4CF1;
    font-weight: 600;
}

.sidebar-logout {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px 20px;
    border-radius: 12px;
    text-decoration: none;
    color: #F43F5E;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    background: #FFF1F2;
    margin-top: auto;
}

.sidebar-logout:hover {
    background: #FFE4E6;
}
</style>
