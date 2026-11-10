<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../model/parkir.php';
require_once __DIR__ . '/../model/admin.php';

// Pastikan session aktif sebelum membaca $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

cek_akses(['owner']);
$aksi = $_GET['aksi'] ?? '';

// --- OWNER: KENDARAAN KELUAR & SUDAH BAYAR ---
if ($aksi == 'keluar_kendaraan') {
    $id     = (int)($_GET['id'] ?? 0);
    $hasil  = $id > 0 ? keluar_kendaraan($id) : false;

    if ($hasil) {
        if (function_exists('catat_log') && isset($_SESSION['id_user'])) {
            catat_log(
                $_SESSION['id_user'],
                "Kendaraan keluar & bayar (owner): {$hasil['plat_nomor']} - Rp" . number_format($hasil['total_bayar'], 0, ',', '.')
            );
        }

        $params = http_build_query([
            'sukses'  => 1,
            'plat'    => $hasil['plat_nomor'],
            'jenis'   => $hasil['jenis_kendaraan'],
            'durasi'  => $hasil['durasi_jam'],
            'total'   => $hasil['total_bayar'],
        ]);
        header("Location: ../view/owner_kendaraan.php?$params");
        exit;
    }

    header("Location: ../view/owner_kendaraan.php?gagal=1");
    exit;
}

header("Location: ../view/owner_kendaraan.php");
exit;
