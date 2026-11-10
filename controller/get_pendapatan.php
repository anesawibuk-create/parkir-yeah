<?php
// Endpoint ringan (JSON) untuk polling AJAX dari dashboard.
// Dipanggil berkala oleh JavaScript supaya kartu "Total Pendapatan"
// bisa update/bertambah sendiri tanpa reload halaman.

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../model/admin.php';

header('Content-Type: application/json');

// Hanya boleh diakses oleh yang sudah login sebagai admin/owner
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'] ?? '', ['admin', 'owner'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$kendaraan_parkir = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM kendaraan WHERE status = 'parkir'")
)['total'] ?? 0;

echo json_encode([
    'pendapatan_total'    => get_pendapatan_total(),
    'pendapatan_hari_ini' => get_pendapatan_hari_ini(),
    'transaksi_hari_ini'  => get_jumlah_transaksi_hari_ini(),
    'kendaraan_parkir'    => (int)$kendaraan_parkir,
]);
