<?php
session_start();
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

/* ======================
   HELPER RESPONSE
====================== */
function response($ok, $msg, $code = 200)
{
    http_response_code($code);
    echo json_encode([
        'ok' => $ok,
        'message' => $msg
    ]);
    exit;
}

/* ======================
   HANYA POST
====================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(false, 'Metode tidak diizinkan', 405);
}

/* ======================
   AMBIL & VALIDASI INPUT
====================== */
$id_brg   = (int)($_POST['id_brg'] ?? 0);
$qty      = (int)($_POST['qty'] ?? 0);

$nama     = trim($_POST['nama_cust'] ?? '');
$email    = trim($_POST['email'] ?? '');
$telp     = trim($_POST['notelp'] ?? '');
$alamat   = trim($_POST['alamat'] ?? '');
$kota     = trim($_POST['kota'] ?? '');
$tgl_beli = $_POST['tgl_beli'] ?? '';
$jenis    = $_POST['jenisbayar'] ?? '';

if (!$id_brg || !$qty || !$nama || !$email || !$telp || !$alamat || !$kota || !$tgl_beli || !$jenis) {
    response(false, 'Semua field wajib diisi', 400);
}

if ($qty < 10) {
    response(false, 'Minimal pemesanan adalah 10 pcs', 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    response(false, 'Format email tidak valid', 400);
}

if (!in_array($jenis, ['Full', 'DP'], true)) {
    response(false, 'Jenis pembayaran tidak valid', 400);
}

/* ======================
   AMBIL HARGA ASLI BARANG
====================== */
$stmt = db()->prepare("SELECT harga FROM tb_barang WHERE id_brg = ?");
$stmt->bind_param("i", $id_brg);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    response(false, 'Barang tidak ditemukan', 404);
}

$row = $res->fetch_assoc();
$harga_satuan = (float)$row['harga'];
$harga_total  = $harga_satuan * $qty;

/* ======================
   DEFAULT STATUS
====================== */
$status_order = 'Belum Diterima';
$status_bayar = 'Belum Bayar';

/* ======================
   SIMPAN KE DATABASE
====================== */
$insert = db()->prepare("
    INSERT INTO tb_penjualan
    (
        id_brg,
        nama_cust,
        email,
        no_telp,
        alamat,
        kota,
        tgl_beli,
        qty,
        harga_satuan,
        harga_total,
        jenis_bayar,
        status_bayar,
        status_order
    )
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$insert->bind_param(
    "issssssiiisss",
    $id_brg,
    $nama,
    $email,
    $telp,
    $alamat,
    $kota,
    $tgl_beli,
    $qty,
    $harga_satuan,
    $harga_total,
    $jenis,
    $status_bayar,
    $status_order
);

if (!$insert->execute()) {
    response(false, 'Gagal menyimpan pesanan', 500);
}

/* ======================
   BERHASIL
====================== */
response(true, 'Pesanan berhasil disimpan');
