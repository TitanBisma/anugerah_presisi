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

  $subject = 'Pengingat Pembayaran Pesanan Anda';
  $message = "
Yth. Bapak/Ibu <b>$nama</b>,<br><br>

Terima kasih telah melakukan pemesanan di <b>Presisi</b>.<br><br>

Kami ingin mengingatkan bahwa hingga saat ini pembayaran untuk pesanan Anda masih <b>belum kami terima</b>.<br><br>

<b>Detail Pembayaran:</b><br>
Total yang harus dibayarkan: <b>Rp $total</b><br>
Batas waktu pembayaran: <b>Hari ini pukul 24.00 WIB</b><br><br>

Mohon kesediaannya untuk segera melakukan pembayaran agar pesanan dapat segera kami proses.<br><br>

Apabila pembayaran telah dilakukan, abaikan email ini.<br><br>

Terima kasih atas kepercayaan Anda kepada kami.<br><br>

Hormat kami,<br>
<b>Admin Presisi</b>
";
} elseif ($data['jenis_bayar'] === 'DP' && $data['status_bayar'] === 'Lunas DP1') {

  $subject = 'Pengingat Pelunasan Pembayaran Pesanan';
  $message = "
Yth. Bapak/Ibu <b>$nama</b>,<br><br>

Terima kasih, pembayaran <b>DP pertama (50%)</b> untuk pesanan Anda telah kami terima dengan baik.<br><br>

Untuk melanjutkan proses pesanan, kami informasikan bahwa masih terdapat sisa pembayaran yang perlu dilunasi dengan rincian berikut:<br><br>

<b>Detail Pelunasan:</b><br>
Sisa pembayaran (50%): <b>Rp $sisa</b><br>
Batas waktu pelunasan: <b>Hari ini pukul 24.00 WIB</b><br><br>

Mohon kesediaannya untuk segera melakukan pelunasan agar proses pesanan dapat dilanjutkan tanpa kendala.<br><br>

Terima kasih atas kerja sama dan kepercayaan Anda kepada kami.<br><br>

Hormat kami,<br>
<b>Admin Presisi</b>
";
} elseif ($data['jenis_bayar'] === 'DP' && $data['status_bayar'] === 'Belum Bayar') {

  $subject = 'Pengingat Pembayaran DP Pesanan';
  $message = "
Yth. Bapak/Ibu <b>$nama</b>,<br><br>

Terima kasih telah melakukan pemesanan di <b>Presisi</b>.<br><br>

Sebagai informasi, pesanan Anda menggunakan metode pembayaran <b>Down Payment (DP)</b>.
Saat ini pembayaran DP pertama masih <b>belum kami terima</b>.<br><br>

<b>Detail Pembayaran:</b><br>
Total pesanan: <b>Rp $total</b><br>
DP yang harus dibayarkan (50%): <b>Rp $sisa</b><br>
Batas waktu pembayaran: <b>Hari ini pukul 24.00 WIB</b><br><br>

Mohon kesediaannya untuk melakukan pembayaran DP agar pesanan dapat segera kami proses.<br><br>

Terima kasih atas perhatian dan kerja samanya.<br><br>

Hormat kami,<br>
<b>Admin Presisi</b>
";
} elseif ($data['status_order'] === 'Lunas Pembayaran') {

  $subject = 'Konfirmasi Pembayaran Pesanan';
  $message = "
Yth. Bapak/Ibu <b>$nama</b>,<br><br>

Terima kasih atas pembayaran yang telah Anda lakukan.<br><br>

Dengan ini kami informasikan bahwa <b>pembayaran pesanan Anda telah kami terima sepenuhnya</b>.<br><br>

Saat ini pesanan Anda akan segera masuk ke tahap proses pengerjaan.
Kami akan terus memberikan informasi terkait perkembangan pesanan Anda.<br><br>

Terima kasih atas kepercayaan Anda kepada <b>Presisi</b>.<br><br>

Hormat kami,<br>
<b>Admin Presisi</b>
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
require_once __DIR__ . '/invoice_template.php';

try {
  $mail = new PHPMailer(true);
  $mail->isSMTP();
  $mail->Host       = 'smtp.gmail.com';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'siapafikri045@gmail.com';
  $mail->Password   = 'gvsv ximh bxwp xfbq';
  $mail->SMTPSecure = 'tls';
  $mail->Port       = 587;
  $html = invoiceHTML($invoiceData);
  $mail->addAttachment($invoicePath, 'Kwitansi_Pembayaran.pdf');


  $mail->setFrom('siapafikri045@gmail.com', 'Admin Toko');
  $mail->addAddress($data['email'], $data['nama_cust']);

  $mail->isHTML(true);
  $mail->Subject = $subject;
  $mail->Body    = $message;

  $mail->send();
if ($data['status_order'] === 'Lunas Pembayaran') {
    $upd = db()->prepare("
        UPDATE tb_penjualan 
        SET status_order = 'Dalam Pengerjaan'
        WHERE id_jual = ?
    ");
    $upd->bind_param("i", $id);
    $upd->execute();
}

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
