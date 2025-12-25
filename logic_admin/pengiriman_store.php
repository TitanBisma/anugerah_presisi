<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$data = json_decode(file_get_contents("php://input"), true);

$id_jual   = (int)$data['id_jual'];
$kurir     = trim($data['kurir']);
$tgl_kirim = $data['tgl_kirim'];
$no_resi   = trim($data['no_resi']);
$ongkir    = (int)$data['ongkir'];

/* Ambil data customer dari tb_penjualan */
$q = mysqli_query($conn, "
    SELECT nama_cust, email 
    FROM tb_penjualan 
    WHERE id_jual = $id_jual
");
$cust = mysqli_fetch_assoc($q);

/* SIMPAN PENGIRIMAN */
$stmt = $conn->prepare("
    INSERT INTO tb_pengiriman
    (id_jual, kurir, tgl_kirim, no_resi, ongkir)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "isssi",
    $id_jual,
    $kurir,
    $tgl_kirim,
    $no_resi,
    $ongkir
);
$stmt->execute();

/* UPDATE STATUS ORDER */
$stmtUpdate = $conn->prepare("
    UPDATE tb_penjualan 
    SET status_order = 'Dalam Pengiriman'
    WHERE id_jual = ?
");
$stmtUpdate->bind_param("i", $id_jual);
$stmtUpdate->execute();


/* KIRIM EMAIL */
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'siapafikri045@gmail.com';
$mail->Password   = 'gvsv ximh bxwp xfbq';
$mail->SMTPSecure = 'tls';
$mail->Port       = 587;

$mail->setFrom('siapafikri045@gmail.com', 'Admin');
$mail->addAddress($cust['email'], $cust['nama_cust']);

$mail->Subject = 'Pesanan Sedang Dikirim';
$mail->Body = "
Halo {$cust['nama_cust']},

Pesanan Anda telah dikirim dengan detail berikut:

Kurir   : $kurir
No Resi : $no_resi
Ongkir  : Rp ".number_format($ongkir,0,',','.')."

Mohon ditunggu, paket masih dalam perjalanan.

Terima kasih.
";

$mail->send();
$_SESSION['flash'] = [
    'type' => 'success',
    'msg'  => 'Email berhasil dikirim ke customer'
  ];


echo json_encode(['ok' => true]);
