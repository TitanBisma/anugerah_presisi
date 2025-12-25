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

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  header('Location: ../penjualan.php');
  exit;
}

/* ======================
   AMBIL DATA PESANAN
====================== */
$stmt = db()->prepare("SELECT * FROM tb_penjualan WHERE id_jual = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
  $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Data tidak ditemukan'];
  header('Location: ../penjualan.php');
  exit;
}

/* ======================
   BLOK STATUS SELESAI
====================== */
if ($data['status_order'] === 'Selesai') {
  $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Pesanan sudah selesai'];
  header('Location: ../penjualan.php');
  exit;
}

/* ======================
   TENTUKAN ISI EMAIL
====================== */
$subject = '';
$message = '';

$nama  = htmlspecialchars($data['nama_cust']);
$total = number_format($data['harga_total'], 0, ',', '.');
$sisa  = number_format($data['harga_total'] / 2, 0, ',', '.');

if ($data['status_order'] === 'Belum Bayar') {

  $subject = 'Reminder Pembayaran Pesanan';
  $message = "
        Yth. $nama,<br><br>
        Ini adalah pengingat bahwa batas waktu pembayaran pesanan Anda adalah
        <b>hari ini pukul 24.00 WIB</b>.<br><br>
        Total yang harus dibayarkan: <b>Rp $total</b><br><br>
        Mohon segera melakukan pembayaran agar pesanan dapat diproses.<br><br>
        Terima kasih.
    ";
} elseif ($data['jenis_bayar'] === 'DP' && $data['status_bayar'] === 'Lunas DP1') {

  $subject = 'Reminder Pelunasan Pembayaran';
  $message = "
        Yth. $nama,<br><br>
        Kami informasikan bahwa pembayaran DP pertama (50%) telah kami terima.<br><br>
        Mohon segera melakukan <b>pelunasan sisa 50%</b> sebesar:
        <b>Rp $sisa</b><br><br>
        Batas waktu pembayaran adalah <b>hari ini pukul 24.00 WIB</b>.<br><br>
        Terima kasih atas kerja samanya.
    ";
} elseif ($data['jenis_bayar'] === 'DP' && $data['status_bayar'] === 'Belum Bayar') {

  $subject = 'Reminder Pembayaran DP';
  $message = "
        Yth. $nama,<br><br>
        Ini adalah pengingat untuk melakukan pembayaran DP pertama sebesar
        <b>50% dari total pesanan</b>.<br><br>
        Total pesanan: <b>Rp $total</b><br>
        DP yang harus dibayarkan: <b>Rp $sisa</b><br><br>
        Batas pembayaran adalah <b>hari ini pukul 24.00 WIB</b>.<br><br>
        Terima kasih.
    ";
} elseif ($data['status_order'] === 'Menunggu Dikirim') {

  $subject = 'Pesanan Telah Diserahkan ke Kurir';
  $message = "
        Yth. $nama,<br><br>
        Kami informasikan bahwa pesanan Anda telah
        <b>diserahkan kepada pihak kurir</b>.<br><br>
        Mohon ditunggu, paket Anda saat ini sedang dalam perjalanan.<br><br>
        Terima kasih telah berbelanja bersama kami.
    ";
}

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

  $mail->setFrom('siapafikri045@gmail.com', 'Admin Toko');
  $mail->addAddress($data['email'], $data['nama_cust']);

  $mail->isHTML(true);
  $mail->Subject = $subject;
  $mail->Body    = $message;

  $mail->send();

  $_SESSION['flash'] = [
    'type' => 'success',
    'msg'  => 'Email berhasil dikirim ke customer'
  ];
  
} catch (Exception $e) {
  $_SESSION['flash'] = [
    'type' => 'error',
    'msg'  => 'Email gagal dikirim'
  ];
}

header('Location: ../penjualan.php');
exit;
