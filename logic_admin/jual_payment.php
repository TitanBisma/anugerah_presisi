<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$id_jual = (int)($_GET['id'] ?? 0);
if ($id_jual <= 0) {
    header('Location: ../penjualan.php');
    exit;
}

/* ======================
   AMBIL DATA PESANAN
====================== */
$stmt = $conn->prepare("
    SELECT * FROM tb_penjualan WHERE id_jual = ?
");
$stmt->bind_param("i", $id_jual);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: ../penjualan.php');
    exit;
}

$email = $order['email'];
$nama  = $order['nama_cust'];

$statusBayarBaru = '';
$statusOrderBaru = 'Menunggu Pengiriman';
$subject = '';
$body = '';

/* ======================
   LOGIKA PEMBAYARAN
====================== */
if ($order['jenis_bayar'] === 'DP') {

    // DP tahap 1
    if ($order['status_bayar'] === 'Belum Bayar') {
        $statusBayarBaru = 'Lunas DP1';
        $statusOrderBaru = 'Menunggu Pembayaran';

        $subject = 'Konfirmasi Pembayaran DP 50%';
        $body = "
        Halo <b>{$nama}</b>,<br><br>
        Kami menginformasikan bahwa pembayaran <b>DP 50%</b> untuk pesanan Anda
        telah <b>kami terima</b>.<br><br>
        Silakan lakukan pelunasan pembayaran untuk melanjutkan proses pesanan.<br><br>
        Terima kasih.
        ";
    }

    // DP tahap 2 (pelunasan)
    elseif ($order['status_bayar'] === 'Lunas DP1') {
        $statusBayarBaru = 'Lunas Full';
        $statusOrderBaru = 'Menunggu Dikirim';

        $subject = 'Pelunasan Pembayaran Berhasil';
        $body = "
        Halo <b>{$nama}</b>,<br><br>
        Kami mengonfirmasi bahwa pembayaran pesanan Anda telah
        <b>lunas sepenuhnya</b>.<br><br>
        Mohon ditunggu informasi pengiriman dalam
        <b>1x24 jam hari kerja</b>.<br><br>
        Terima kasih atas kepercayaan Anda.
        ";
    }
} else {
    // BAYAR FULL
    $statusBayarBaru = 'Lunas Full';
    $statusOrderBaru = 'Menunggu Dikirim';

    $subject = 'Konfirmasi Pembayaran Berhasil';
    $body = "
    Halo <b>{$nama}</b>,<br><br>
    Kami menginformasikan bahwa pembayaran pesanan Anda telah
    <b>kami terima</b>.<br><br>
    Mohon ditunggu informasi pengiriman dalam
    <b>1x24 jam hari kerja</b>.<br><br>
    Terima kasih.
    ";
}

/* ======================
   UPDATE DATABASE
====================== */
$upd = $conn->prepare("
    UPDATE tb_penjualan SET
        status_bayar = ?,
        status_order = ?
    WHERE id_jual = ?
");
$upd->bind_param("ssi", $statusBayarBaru, $statusOrderBaru, $id_jual);
$upd->execute();

/* ======================
   KIRIM EMAIL
====================== */
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'siapafikri045@gmail.com';
    $mail->Password   = 'gvsv ximh bxwp xfbq';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('siapafikri045@gmail.com', 'Admin Presisi');
    $mail->addAddress($email, $nama);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    $mail->send();
} catch (Exception $e) {
    // kalau email gagal, status tetap update (opsional)
}

/* ======================
   REDIRECT
====================== */
$_SESSION['flash'] = [
    'type' => 'success',
    'msg'  => 'Pembayaran berhasil dikonfirmasi & email terkirim'
];

header('Location: ../penjualan.php');
exit;
