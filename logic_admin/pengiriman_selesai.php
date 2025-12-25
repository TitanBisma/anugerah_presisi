<?php
session_start();
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$id_kirim = $_POST['id_kirim'] ?? null;
$tgl_tiba = $_POST['tgl_tiba'] ?? null;

if (!$id_kirim || !$tgl_tiba) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data tidak lengkap'
    ]);
    exit;
}

$query = "
    UPDATE tb_pengiriman
    SET status_kirim = 'Sudah Tiba',
        tgl_tiba = ?
    WHERE id_kirim = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param('si', $tgl_tiba, $id_kirim);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengupdate pengiriman'
    ]);
}
