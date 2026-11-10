<?php
// Navbar bersama untuk semua halaman setelah login.
// Menu otomatis menyesuaikan role akun yang sedang login.
// Catatan: memakai $_SESSION['role'] (bukan variabel $role) agar tidak tertimpa variabel lain di halaman.
$nav_role = $_SESSION['role'] ?? '';
$nav_page = basename($_SERVER['SCRIPT_NAME'] ?? '');

$nav_menu = [['dashboard.php', 'Dashboard']];

if ($nav_role === 'admin') {
    $nav_menu[] = ['adminview.php',  'Kelola User'];
    $nav_menu[] = ['admintarif.php', 'Kelola Tarif'];
    $nav_menu[] = ['adminarea.php',  'Kelola Area'];
    $nav_menu[] = ['adminlog.php',   'Log Aktivitas'];
} elseif ($nav_role === 'petugas') {
    $nav_menu[] = ['transaksi_petugas.php', 'Transaksi Parkir'];
} elseif ($nav_role === 'owner') {
    $nav_menu[] = ['rekap.php', 'Rekap Laporan'];
}
?>
<nav class="navbar">
    <div class="brand">E-Parkir</div>
    <ul>
        <?php foreach ($nav_menu as [$nav_file, $nav_label]) : ?>
            <li><a href="<?= $nav_file; ?>"<?= $nav_page === $nav_file ? ' class="active" aria-current="page"' : ''; ?>><?= htmlspecialchars($nav_label, ENT_QUOTES, 'UTF-8'); ?></a></li>
        <?php endforeach; ?>

        <li>
            <form action="../controller/auth.php" method="POST" class="logout-form" onsubmit="return confirm('Yakin ingin keluar?')">
                <input type="hidden" name="action" value="logout">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        </li>
    </ul>
</nav>
