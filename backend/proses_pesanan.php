<?php
session_start();
include __DIR__ . '/koneksi.php';

header('Content-Type: application/json; charset=utf-8');

// Fungsi bantu kirim respons JSON
function balas($sukses, $pesan, $extra = []) {
    echo json_encode(array_merge(['success' => $sukses, 'message' => $pesan], $extra));
    exit();
}

// 1. Cek Login & Metode Request
if (!isset($_SESSION['status']) || $_SESSION['status'] != true) {
    http_response_code(401);
    balas(false, 'Silakan login terlebih dahulu.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    balas(false, 'Metode tidak valid.');
}

// 2. Ambil Data dari Frontend
$input = json_decode(file_get_contents('php://input'), true);
$items = $input['items'] ?? [];
$metode = $input['metode_pembayaran'] ?? '';
$catatan = substr(trim($input['catatan'] ?? ''), 0, 255);
$user_id = (int) $_SESSION['id'];

// Validasi input dasar
if (!is_array($items) || count($items) === 0) {
    balas(false, 'Keranjang masih kosong.');
}
if (!in_array($metode, ['cash', 'qris'], true)) {
    balas(false, 'Pilih metode pembayaran terlebih dahulu.');
}

// Mulai Transaksi Database
mysqli_begin_transaction($koneksi);

try {
    $total = 0;
    $detail_pesanan = []; // Wadah sementara buat simpan data item
    
    // Loop setiap item di keranjang
    foreach ($items as $item) {
        $menu_id = (int) ($item['id'] ?? 0);
        $qty = (int) ($item['qty'] ?? 0);
        $level = (int) ($item['level'] ?? 0);
        $catatan_item = substr(trim($item['catatan'] ?? ''), 0, 100);
        
        if ($menu_id <= 0 || $qty < 1 || $qty > 99) {
            throw new Exception('Data pesanan tidak valid.');
        }
        
        // A. Cek Menu & Harga dari Database
        $query_menu = "SELECT nama_menu, kategori, harga FROM menu WHERE id = $menu_id";
        $result_menu = mysqli_query($koneksi, $query_menu);
        $menu = mysqli_fetch_assoc($result_menu);
        
        if (!$menu) {
            throw new Exception('Ada menu yang sudah tidak tersedia.');
        }
        
        // B. Cek Level Pedas (khusus mie)
        if ($menu['kategori'] === 'mie') {
            $query_level = "SELECT id FROM level_pedas WHERE id = $level";
            $result_level = mysqli_query($koneksi, $query_level);
            
            if (!mysqli_fetch_assoc($result_level)) {
                throw new Exception('Pilih level pedas untuk ' . $menu['nama_menu'] . '.');
            }
        } else {
            $level = 1; // Kalau bukan mie, level default 1
        }
        
        // C. Kurangi Stok
        // Query ini akan berhasil hanya jika stok >= qty
        $query_stok = "UPDATE menu SET ketersediaan = ketersediaan - $qty 
                       WHERE id = $menu_id AND ketersediaan >= $qty";
        mysqli_query($koneksi, $query_stok);
        
        if (mysqli_affected_rows($koneksi) !== 1) {
            throw new Exception('Stok ' . $menu['nama_menu'] . ' tidak mencukupi.');
        }
        
        // Hitung Total
        $harga = (int) $menu['harga'];
        $subtotal = $harga * $qty;
        $total += $subtotal;
        
        // Simpan detail ke array sementara
        $detail_pesanan[] = [
            'menu_id' => $menu_id,
            'qty' => $qty,
            'level' => $level,
            'harga' => $harga,
            'subtotal' => $subtotal,
            'catatan' => $catatan_item
        ];
    }
    
    // 3. Simpan ke Tabel Pesanan
    $query_pesanan = "INSERT INTO pesanan (user_id, tanggal_pesanan, total_harga, metode_pembayaran, catatan) 
                      VALUES ($user_id, NOW(), $total, '$metode', '$catatan')";
    mysqli_query($koneksi, $query_pesanan);
    
    $pesanan_id = mysqli_insert_id($koneksi);
    
    if ($pesanan_id <= 0) {
        throw new Exception('ID pesanan tidak terbentuk.');
    }
    
    // 4. Simpan Detail Item ke Tabel Detail Pesanan
    foreach ($detail_pesanan as $detail) {
        $mid = $detail['menu_id'];
        $q = $detail['qty'];
        $l = $detail['level'];
        $h = $detail['harga'];
        $s = $detail['subtotal'];
        $c = $detail['catatan'];
        
        $query_detail = "INSERT INTO detail_pesanan (pesanan_id, menu_id, jumlah, level_pedas_id, harga_satuan, subtotal, catatan) 
                         VALUES ($pesanan_id, $mid, $q, $l, $h, $s, '$c')";
        mysqli_query($koneksi, $query_detail);
    }
    
    // Commit (Simpan Permanen)
    mysqli_commit($koneksi);
    
    balas(true, 'Pesanan #' . $pesanan_id . ' berhasil dibuat. Total Rp ' . number_format($total, 0, ',', '.') . '.', [
        'pesanan_id' => $pesanan_id,
        'total' => $total,
    ]);
    
} catch (Exception $e) {
    // Rollback (Batalkan semua perubahan kalau ada error)
    mysqli_rollback($koneksi);
    balas(false, $e->getMessage());
}
?>