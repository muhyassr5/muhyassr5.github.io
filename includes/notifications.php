<?php
// includes/notifications.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php if (isset($_SESSION['level_up_popup'])): ?>
    <div id="levelUpOverlay" style="position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); z-index: 9999; display: flex; justify-content: center; align-items: center; animation: fadeIn 0.4s ease;">
        <div style="background: #FFFFFF; width: 90%; max-width: 420px; border-radius: 24px; padding: 40px; text-align: center; box-shadow: 0 20px 50px rgba(108,76,241,0.3); border: 2px solid #6C4CF1; transform: scale(1); animation: popUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
            <div style="font-size: 80px; margin-bottom: 10px;">🎉</div>
            <h2 style="font-family: 'Poppins', sans-serif; font-size: 1.8rem; color: #1E293B; font-weight: 700; margin-bottom: 8px;">Luar Biasa!</h2>
            <p style="font-family: 'Poppins', sans-serif; color: #64748B; font-size: 1rem; margin-bottom: 25px;">Kamu berhasil naik ke tingkat baru.</p>
            <div style="display: inline-block; background: linear-gradient(135deg, #6C4CF1, #8A73FF); color: #FFFFFF; font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 700; padding: 12px 35px; border-radius: 50px; margin-bottom: 30px; box-shadow: 0 8px 20px rgba(108,76,241,0.3);">
                LEVEL <?php echo htmlspecialchars($_SESSION['level_up_popup']); ?>
            </div>
            <button onclick="document.getElementById('levelUpOverlay').remove();" style="width: 100%; background: #F1F5F9; border: none; padding: 14px; border-radius: 14px; font-family: 'Poppins', sans-serif; font-size: 1rem; font-weight: 600; color: #475569; cursor: pointer; transition: background 0.2s;">
                Lanjutkan Belajar
            </button>
        </div>
    </div>
    <script>
        // Menghapus session flash pasca rendering pop up berhasil dipicu
        <?php unset($_SESSION['level_up_popup']); ?>
    </script>
<?php endif; ?>

<?php if (isset($_SESSION['badge_unlocked_confetti'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Jalankan selebrasi semburan partikel warna-warni (Confetti)
            var duration = 3 * 1000;
            var end = Date.now() + duration;

            (function frame() {
                confetti({
                    particleCount: 5,
                    angle: 60,
                    spread: 55,
                    origin: { x: 0 },
                    colors: ['#6C4CF1', '#8A73FF', '#FFD700']
                });
                confetti({
                    particleCount: 5,
                    angle: 120,
                    spread: 55,
                    origin: { x: 1 },
                    colors: ['#6C4CF1', '#8A73FF', '#FFD700']
                });

                if (Date.now() < end) {
                    requestAnimationFrame(frame);
                }
            }());
        });
    </script>
    <?php unset($_SESSION['badge_unlocked_confetti']); ?>
<?php endif; ?>

<style>
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes popUp { from { opacity: 0; transform: scale(0.7); } to { opacity: 1; transform: scale(1); } }
</style>
