<?php
    session_start();
    include __DIR__ . '/koneksi.php';

    header('Content-Type: application/json; charset=utf-8');

    function balas($sukses, $pesan, $extra = []) {
        echo json_encode(array_merge(['success' => $sukses, 'message' => $pesan], $extra));
        exit();
    }

    if (!isset($_SESSION['status']) || $_SESSION['status'] != true) {
        http_response_code(401);
        balas(false, 'Silakan login terlebih dahulu.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        balas(false, 'Metode tidak valid.');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $items = $input['items'] ?? [];
    $metode = mysqli_real_escape_string($koneksi, $input['metode_pembayaran'] ?? '');
    $catatan = mysqli_real_escape_string($koneksi, substr(trim($input['catatan'] ?? ''), 0, 255));
    $user_id = (int) $_SESSION['id'];

    if (!is_array($items) || count($items) === 0) {
        balas(false, 'Keranjang masih kosong.');
    }

    mysqli_begin_transaction($koneksi);

    try {
        $total = 0;
        $detail_pesanan = []; 
        
        foreach ($items as $item) {
            $menu_id = (int) ($item['id'] ?? 0);
            $qty = (int) ($item['qty'] ?? 0);
            $level = (int) ($item['level'] ?? 0);
            $catatan_item = mysqli_real_escape_string($koneksi, substr(trim($item['catatan'] ?? ''), 0, 100));
            
            if ($menu_id <= 0 || $qty < 1 || $qty > 99) {
                throw new Exception('Data pesanan tidak valid.');
            }
            
            $query_menu = "SELECT nama_menu, kategori, harga FROM menu WHERE id = $menu_id";
            $result_menu = mysqli_query($koneksi, $query_menu);
            $menu = mysqli_fetch_assoc($result_menu);
            
            if (!$menu) {
                throw new Exception('Ada menu yang sudah tidak tersedia.');
            }
            
            if ($menu['kategori'] === 'mie') {
                $query_level = "SELECT id FROM level_pedas WHERE id = $level";
                $result_level = mysqli_query($koneksi, $query_level);
                
                if (!mysqli_fetch_assoc($result_level)) {
                    throw new Exception('Pilih level pedas untuk ' . $menu['nama_menu'] . '.');
                }
            } else {
                $level = 1; 
            }
            
            $query_stok = "UPDATE menu SET ketersediaan = ketersediaan - $qty 
                        WHERE id = $menu_id AND ketersediaan >= $qty";
            mysqli_query($koneksi, $query_stok);
            
            if (mysqli_affected_rows($koneksi) !== 1) {
                throw new Exception('Stok ' . $menu['nama_menu'] . ' tidak mencukupi.');
            }
            
            $harga = (int) $menu['harga'];
            $subtotal = $harga * $qty;
            $total += $subtotal;
            
            $detail_pesanan[] = [
                'menu_id' => $menu_id, 'qty' => $qty, 'level' => $level,
                'harga' => $harga, 'subtotal' => $subtotal, 'catatan' => $catatan_item
            ];
        }
        
        $query_pesanan = "INSERT INTO pesanan (user_id, tanggal_pesanan, total_harga, metode_pembayaran, catatan) 
                        VALUES ($user_id, NOW(), $total, '$metode', '$catatan')";
        mysqli_query($koneksi, $query_pesanan);
        
        $pesanan_id = mysqli_insert_id($koneksi);
        if ($pesanan_id <= 0) {
            throw new Exception('ID pesanan tidak terbentuk.');
        }
        
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
        
        mysqli_commit($koneksi);
        
        balas(true, 'Pesanan #' . $pesanan_id . ' berhasil dibuat. Total Rp ' . number_format($total, 0, ',', '.') . '.', [
            'pesanan_id' => $pesanan_id, 'total' => $total,
        ]);
        
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        balas(false, $e->getMessage());
    }
?>