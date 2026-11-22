<?php
session_start();
include __DIR__ . '/koneksi.php';

header('Content-Type: application/json; charset=utf-8');

// Error khusus yang pesannya boleh ditampilkan ke pelanggan
class PesananException extends Exception {}

function balas($sukses, $pesan, $extra = []) {
    echo json_encode(array_merge(['success' => $sukses, 'message' => $pesan], $extra));
    exit();
}

// --- Pengecekan awal ---
if (!isset($_SESSION['status']) || $_SESSION['status'] != true) {
    http_response_code(401);
    balas(false, 'Silakan login terlebih dahulu.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    balas(false, 'Metode tidak valid.');
}

$input   = json_decode(file_get_contents('php://input'), true);
$items   = $input['items'] ?? [];
$metode  = $input['metode_pembayaran'] ?? '';
$catatan = mb_substr(trim($input['catatan'] ?? ''), 0, 255);
$user_id = (int) $_SESSION['id'];

if (!is_array($items) || count($items) === 0) {
    balas(false, 'Keranjang masih kosong.');
}
if (!in_array($metode, ['cash', 'qris'], true)) {
    balas(false, 'Pilih metode pembayaran terlebih dahulu.');
}

mysqli_begin_transaction($koneksi);

try {
    $stmtMenu  = mysqli_prepare($koneksi, "SELECT nama_menu, kategori, harga FROM menu WHERE id = ?");
    $stmtStok  = mysqli_prepare($koneksi, "UPDATE menu SET ketersediaan = ketersediaan - ? WHERE id = ? AND ketersediaan >= ?");
    $stmtLevel = mysqli_prepare($koneksi, "SELECT id FROM level_pedas WHERE id = ?");

    $total = 0;
    $baris = [];

    foreach ($items as $it) {
        $menu_id = (int) ($it['id'] ?? 0);
        $qty     = (int) ($it['qty'] ?? 0);
        $level   = (int) ($it['level'] ?? 0);

        if ($menu_id <= 0 || $qty < 1 || $qty > 99) {
            throw new PesananException('Data pesanan tidak valid.');
        }

        // Harga SELALU diambil dari database, bukan dari browser
        mysqli_stmt_bind_param($stmtMenu, "i", $menu_id);
        mysqli_stmt_execute($stmtMenu);
        $menu = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtMenu));
        if (!$menu) {
            throw new PesananException('Ada menu yang sudah tidak tersedia.');
        }

        // Level pedas hanya berlaku untuk mie. Menu lain memakai Level 0 (id 1)
        if ($menu['kategori'] === 'mie') {
            mysqli_stmt_bind_param($stmtLevel, "i", $level);
            mysqli_stmt_execute($stmtLevel);
            if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmtLevel))) {
                throw new PesananException('Pilih level pedas untuk ' . $menu['nama_menu'] . '.');
            }
        } else {
            $level = 1;
        }

        // Kurangi stok; gagal kalau stok tidak cukup
        mysqli_stmt_bind_param($stmtStok, "iii", $qty, $menu_id, $qty);
        mysqli_stmt_execute($stmtStok);
        if (mysqli_stmt_affected_rows($stmtStok) !== 1) {
            throw new PesananException('Stok ' . $menu['nama_menu'] . ' tidak mencukupi.');
        }

        $harga    = (int) $menu['harga'];
        $subtotal = $harga * $qty;
        $total   += $subtotal;

        $baris[] = [$menu_id, $qty, $level, $harga, $subtotal];
    }

    // Simpan data pesanan
    $stmtPesanan = mysqli_prepare($koneksi,
        "INSERT INTO pesanan (user_id, tanggal_pesanan, total_harga, metode_pembayaran, catatan)
         VALUES (?, NOW(), 'pending', ?, ?, ?)");
    mysqli_stmt_bind_param($stmtPesanan, "iiss", $user_id, $total, $metode, $catatan);
    mysqli_stmt_execute($stmtPesanan);
    $pesanan_id = mysqli_insert_id($koneksi);

    if ($pesanan_id <= 0) {
        throw new Exception('ID pesanan tidak terbentuk (cek AUTO_INCREMENT di tabel pesanan).');
    }

    // Simpan tiap item pesanan
    $stmtDetail = mysqli_prepare($koneksi,
        "INSERT INTO detail_pesanan (pesanan_id, menu_id, jumlah, level_pedas_id, harga_satuan, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($baris as [$menu_id, $qty, $level, $harga, $subtotal]) {
        mysqli_stmt_bind_param($stmtDetail, "iiiiii", $pesanan_id, $menu_id, $qty, $level, $harga, $subtotal);
        mysqli_stmt_execute($stmtDetail);
    }

    mysqli_commit($koneksi);

    balas(true, 'Pesanan #' . $pesanan_id . ' berhasil dibuat. Total ' . 'Rp ' . number_format($total, 0, ',', '.') . '.', [
        'pesanan_id' => $pesanan_id,
        'total'      => $total,
    ]);

} catch (PesananException $e) {
    mysqli_rollback($koneksi);
    balas(false, $e->getMessage());
} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    error_log('proses_pesanan: ' . $e->getMessage());
    balas(false, 'Terjadi kesalahan di server. Pesanan belum dibuat.');
}

?>