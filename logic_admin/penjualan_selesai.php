<?php
session_start();
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized'
    ]);
    exit;
}

$id = $_POST['id_jual'] ?? null;

if (!$id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID tidak valid'
    ]);
    exit;
}

/* ===========================
   CEK STATUS PENGIRIMAN
=========================== */
$cek = mysqli_prepare($conn, "
    SELECT status_kirim 
    FROM tb_pengiriman 
    WHERE id_jual = ?

");
mysqli_stmt_bind_param($cek, 'i', $id);
mysqli_stmt_execute($cek);
$result = mysqli_stmt_get_result($cek);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data pengiriman belum tersedia'
    ]);
    exit;
}

if ($data['status_kirim'] !== 'Sudah Tiba') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Pesanan tidak dapat diselesaikan karena barang belum tiba di tujuan'
    ]);
    exit;
}

/* ===========================
   UPDATE STATUS PESANAN
=========================== */
$query = "UPDATE tb_penjualan 
          SET status_order = 'Selesai' 
          WHERE id_jual = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'status' => 'success'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menyelesaikan pesanan'
    ]);
}
