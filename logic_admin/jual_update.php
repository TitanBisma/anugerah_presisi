<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'message' => 'Invalid request']);
    exit;
}

/* ======================
   AMBIL & VALIDASI INPUT
====================== */
$id_jual      = (int)$_POST['id_jual'];
$nama_cust    = trim($_POST['nama_cust']);
$email        = trim($_POST['email']);
$no_telp      = trim($_POST['no_telp']);
$alamat       = trim($_POST['alamat']);
$kota         = trim($_POST['kota']);
$qty          = (int)$_POST['qty'];
$harga_satuan = (int)$_POST['harga_satuan'];
$jenis_bayar  = $_POST['jenis_bayar'];
$status_order = $_POST['status_order'] ?? null;
$status_bayar = $_POST['status_bayar'] ?? null;


/* ======================
   VALIDASI
====================== */
if ($id_jual <= 0 || $qty < 10 || $harga_satuan <= 0) {
    echo json_encode([
        'ok' => false,
        'message' => 'Data tidak valid (qty min 10)'
    ]);
    exit;
}

if (!$status_order || !$status_bayar) {
    echo json_encode([
        'ok' => false,
        'message' => 'Status order / bayar tidak boleh kosong'
    ]);
    exit;
}


/* ======================
   HITUNG TOTAL
====================== */
$harga_total = $qty * $harga_satuan;

/* ======================
   UPDATE DATABASE
====================== */
$stmt = db()->prepare("
    UPDATE tb_penjualan SET
        nama_cust     = ?,
        email         = ?,
        no_telp       = ?,
        alamat        = ?,
        kota          = ?,
        qty           = ?,
        harga_satuan  = ?,
        harga_total   = ?,
        jenis_bayar   = ?,
        status_bayar  = ?,
        status_order  = ?
    WHERE id_jual = ?
");


$stmt->bind_param(
    "sssssiissssi",
    $nama_cust,
    $email,
    $no_telp,
    $alamat,
    $kota,
    $qty,
    $harga_satuan,
    $harga_total,
    $jenis_bayar,
    $status_bayar,
    $status_order,
    $id_jual
);


if ($stmt->execute()) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode([
        'ok' => false,
        'message' => 'Gagal update database'
    ]);
}
exit;

