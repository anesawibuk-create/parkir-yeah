<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../model/parkir.php';

if (function_exists('cek_akses')) {
    cek_akses(['owner']);
}

$list_kendaraan = get_kendaraan_aktif();

$sukses = isset($_GET['sukses']);
$gagal  = isset($_GET['gagal']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kendaraan Keluar & Bayar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; padding: 30px; }
        .container { max-width: 1000px; margin: 0 auto; background: #ffffff; padding: 25px 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .btn-back { display: inline-block; color: #4a5568; text-decoration: none; font-weight: 600; font-size: 14px; margin-bottom: 20px; }
        .btn-back:hover { color: #3182ce; }
        h2 { color: #1a202c; font-size: 24px; font-weight: 700; margin-bottom: 8px; }
        .subtitle { color: #718096; font-size: 14px; margin-bottom: 25px; }

        .alert { padding: 16px 20px; border-radius: 8px; margin-bottom: 25px; }
        .alert-sukses { background-color: #f0fff4; border: 1px solid #9ae6b4; color: #22543d; }
        .alert-gagal { background-color: #fff5f5; border: 1px solid #feb2b2; color: #742a2a; }
        .alert h3 { margin-bottom: 8px; font-size: 16px; }
        .alert .total-bayar { font-size: 22px; font-weight: 800; color: #2f855a; margin: 6px 0; }
        .btn-print { margin-top: 8px; padding: 8px 16px; background: #2f855a; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 13px; }

        table { width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; overflow: hidden; }
        table th, table td { padding: 12px 14px; text-align: left; font-size: 14px; }
        table th { background-color: #edf2f7; color: #4a5568; font-weight: 700; font-size: 12px; text-transform: uppercase; }
        table tr { border-bottom: 1px solid #edf2f7; }

        .estimasi { font-weight: 700; color: #2b6cb0; }
        .btn-keluar { color: #fff; background: #2f855a; padding: 7px 14px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; }
        .btn-keluar:hover { background: #276749; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: #fff; }
            .container { box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="btn-back no-print">← Kembali ke Dashboard</a>
    <h2>Kendaraan Keluar & Bayar</h2>
    <p class="subtitle">Tandai kendaraan yang sudah keluar dari area parkir dan sudah melakukan pembayaran.</p>

    <?php if ($sukses): ?>
        <div class="alert alert-sukses" id="strukSukses">
            <h3>✅ Pembayaran berhasil dicatat!</h3>
            <div>Plat Nomor : <strong><?= htmlspecialchars($_GET['plat'] ?? '-'); ?></strong></div>
            <div>Jenis Kendaraan : <?= ucfirst(htmlspecialchars($_GET['jenis'] ?? '-')); ?></div>
            <div>Durasi Parkir : <?= (int)($_GET['durasi'] ?? 0); ?> Jam</div>
            <div class="total-bayar">Total Dibayar: Rp <?= number_format((int)($_GET['total'] ?? 0), 0, ',', '.'); ?></div>
            <button type="button" class="btn-print no-print" onclick="window.print()">🖨️ Cetak Struk</button>
        </div>
    <?php endif; ?>

    <?php if ($gagal): ?>
        <div class="alert alert-gagal">
            <h3>⚠️ Gagal memproses kendaraan.</h3>
            <div>Kendaraan mungkin sudah keluar sebelumnya atau data tidak ditemukan.</div>
        </div>
    <?php endif; ?>

    <table class="no-print">
        <thead>
            <tr>
                <th>Plat Nomor</th>
                <th>Jenis</th>
                <th>Area</th>
                <th>Jam Masuk</th>
                <th>Tarif/Jam</th>
                <th>Estimasi Biaya Saat Ini</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($list_kendaraan && mysqli_num_rows($list_kendaraan) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($list_kendaraan)): ?>
                    <?php
                        $tarif_per_jam = (int)($row['tarif_per_jam'] ?? 0);
                        $jam_masuk_dt  = new DateTime($row['jam_masuk']);
                        $selisih_detik = max(0, (new DateTime())->getTimestamp() - $jam_masuk_dt->getTimestamp());
                        $durasi_estimasi = max(1, (int)ceil($selisih_detik / 3600));
                        $estimasi_biaya  = $durasi_estimasi * $tarif_per_jam;
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['plat_nomor'] ?? ''); ?></strong></td>
                        <td><?= ucfirst(htmlspecialchars($row['jenis_kendaraan'] ?? '')); ?></td>
                        <td><?= htmlspecialchars($row['nama_area'] ?? '-'); ?></td>
                        <td><?= !empty($row['jam_masuk']) ? date('d/m/Y H:i', strtotime($row['jam_masuk'])) : '-'; ?></td>
                        <td><?= $tarif_per_jam > 0 ? 'Rp ' . number_format($tarif_per_jam, 0, ',', '.') : '-'; ?></td>
                        <td class="estimasi">Rp <?= number_format($estimasi_biaya, 0, ',', '.'); ?></td>
                        <td>
                            <a href="../controller/ownercontroller.php?aksi=keluar_kendaraan&id=<?= $row['id_kendaraan']; ?>"
                               class="btn-keluar"
                               onclick="return confirm('Konfirmasi: kendaraan <?= htmlspecialchars($row['plat_nomor'] ?? ''); ?> sudah keluar dan sudah membayar?')">
                               Keluar & Bayar
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada kendaraan yang sedang parkir.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
