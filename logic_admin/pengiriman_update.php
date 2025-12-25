<?php
session_start();
require_once __DIR__ . '/../config/config.php';

$data = json_decode(file_get_contents("php://input"), true);

$id_kirim  = (int)$data['id_kirim'];
$kurir     = trim($data['kurir']);
$tgl_kirim = $data['tgl_kirim'];
$no_resi   = trim($data['no_resi']);
$ongkir    = (int)$data['ongkir'];

if (!$id_kirim || !$kurir || !$tgl_kirim || !$no_resi || !$ongkir) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data tidak lengkap'
    ]);
    exit;
}

$stmt = $conn->prepare("
    UPDATE tb_pengiriman 
    SET kurir=?, tgl_kirim=?, no_resi=?, ongkir=?
    WHERE id_kirim=?
");
$stmt->bind_param(
    "sssii",
    $kurir,
    $tgl_kirim,
    $no_resi,
    $ongkir,
    $id_kirim
);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal update data'
    ]);
}
