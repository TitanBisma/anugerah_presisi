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

/* UPDATE STATUS PESANAN */
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
