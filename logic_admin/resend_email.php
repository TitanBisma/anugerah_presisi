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
$stmt = db()->prepare("
    SELECT 
        p.*,
        b.nama_brg
    FROM tb_penjualan p
    JOIN tb_barang b ON p.id_brg = b.id_brg
    WHERE p.id_jual = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$namaBarang = htmlspecialchars($data['nama_brg']);



if (!$data) {
  $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Data tidak ditemukan'];
  header('Location: ../penjualan.php');
  exit;
}


/* ======================
   HITUNG NILAI PEMBAYARAN
====================== */
$totalAngka = (int)$data['harga_total'];
$dpAngka    = $totalAngka * 0.5;
$sisaAngka  = $totalAngka - $dpAngka;

$total = number_format($totalAngka, 0, ',', '.');
$dp    = number_format($dpAngka, 0, ',', '.');
$sisa  = number_format($sisaAngka, 0, ',', '.');
$qty = (int)$data['qty'];
$namaBarang = $data['nama_produk'] ?? 'Produk';


/* ======================
   DATA UNTUK INVOICE
====================== */
$invoiceData = [
  'nama'       => $data['nama_cust'],
  'email'      => $data['email'],
  'produk'     => $data['nama_brg'],
  'qty'        => $data['qty'],
  'harga'      => number_format($data['harga_satuan'], 0, ',', '.'),
  'total'      => number_format($totalAngka, 0, ',', '.'),
  'dp'         => number_format($dpAngka, 0, ',', '.'),
  'sisa'       => number_format($sisaAngka, 0, ',', '.'),
  'jenis_bayar' => $data['jenis_bayar'],
  'tanggal'    => date('d-m-Y')
];



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

// 1️⃣ DP - BELUM BAYAR
if ($data['jenis_bayar'] === 'DP' && $data['status_bayar'] === 'Belum Bayar') {

  $subject = 'Pengingat Pembayaran DP Pesanan';
  $message = "
Yth. Bapak/Ibu <b>$nama</b>,<br><br>

Terima kasih telah melakukan pemesanan $namaBarang di <b>CV. Anugerah Presisi</b>.<br><br>

Berdasarkan data pesanan kami, metode pembayaran yang Anda pilih adalah
<b>Down Payment (DP)</b> sebesar <b>50%</b> dari total nilai pesanan.<br><br>

<b>Detail Pesanan:</b><br>
Nama Barang: <b>$namaBarang</b><br>
Total Pesanan: <b>$qty pcs</b><br>
DP yang Harus Dibayarkan (50%): <b>Rp. $dp</b><br>
Harga Total: <b>Rp $total</b><br><br>

Mohon kesediaannya untuk segera melakukan pembayaran DP agar proses pesanan dapat kami lanjutkan.<br><br>

Sebagai referensi, kami lampirkan invoice resmi pada email ini.<br><br>

Terima kasih atas perhatian dan kerja sama Anda.<br><br>

Hormat kami,<br>
<b>Admin Presisi</b>
";

  // 2️⃣ DP - SUDAH BAYAR (PELUNASAN)
} elseif ($data['jenis_bayar'] === 'DP' && $data['status_bayar'] === 'Lunas DP1') {

  $subject = 'Pengingat Pelunasan Pembayaran Pesanan';
  $message = "
Yth. Bapak/Ibu <b>$nama</b>,<br><br>

Terima kasih, pembayaran <b>DP sebesar 50%</b> untuk pesanan Anda telah kami terima dengan baik.<br><br>

Untuk menyelesaikan proses pesanan ke tahap akhir, berikut kami sampaikan sisa pembayaran yang perlu dilunasi:<br><br>

<b>Rincian Pelunasan:</b><br>
Nama Barang: <b>$namaBarang</b><br>
Total Pesanan: <b>$qty pcs</b>
Sisa Pembayaran (50%): <b>Rp $sisa</b><br>
Harga Total: <b>Rp $total</b><br><br>

Mohon kesediaannya untuk melakukan pelunasan dengan batas waktu hari ini <b>pukul 24.00 WIB</b>, agar pesanan dapat segera kami lanjutkan ke tahap berikutnya.<br><br>

Invoice terbaru kami lampirkan sebagai bukti dan referensi pembayaran.<br><br>

Terima kasih atas kepercayaan Anda kepada <b>Presisi</b>.<br><br>

Hormat kami,<br>
<b>Admin Presisi</b>
";

  // 3️⃣ FULL PAYMENT - BELUM BAYAR
} elseif ($data['status_bayar'] === 'Belum Bayar') {

  $subject = 'Pengingat Pembayaran Pesanan Anda';
  $message = "
Yth. Bapak/Ibu <b>$nama</b>,<br><br> 
Terima kasih telah melakukan pemesanan di <b>Presisi</b>.<br><br> 

Kami ingin mengingatkan bahwa hingga saat ini pembayaran untuk pesanan Anda masih <b>belum kami terima</b>.<br><br> 

<b>Detail Pesanan:</b><br>
Nama Barang: <b>$namaBarang</b><br>
Total Pesanan: <b>$qty pcs</b>
Harga Total: <b>Rp $total</b><br> 
Batas waktu pembayaran: <b>Hari ini pukul 24.00 WIB</b><br><br> 

Mohon kesediaannya untuk segera melakukan pembayaran agar 
pesanan dapat segera kami proses.<br><br> 

Apabila pembayaran telah dilakukan, abaikan email ini.<br><br> 

Terima kasih atas kepercayaan Anda kepada kami.<br><br> 

Hormat kami,<br> 
<b>Admin Presisi</b>
";
}

/* ======================
   KIRIM EMAIL
====================== */
require_once __DIR__ . '/invoice/invoice_pdf.php';
$invoicePath = generateInvoicePDF($invoiceData, $id);


try {
  $mail = new PHPMailer(true);
  $mail->isSMTP();
  $mail->Host       = 'smtp.gmail.com';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'siapafikri045@gmail.com';
  $mail->Password   = 'gvsv ximh bxwp xfbq';
  $mail->SMTPSecure = 'tls';
  $mail->Port       = 587;
  $mail->addAttachment(
    $invoicePath,
    'Invoice_Pesanan_' . $id . '.pdf'
  );



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

if (file_exists($invoicePath)) {
  unlink($invoicePath);
}

$_SESSION['flash'] = [
    'type' => 'success',
    'msg'  => 'Pembayaran berhasil dikonfirmasi & email terkirim'
];

$_SESSION['flash'] = [
    'type' => 'success',
    'msg'  => 'Pembayaran berhasil dikonfirmasi & email terkirim'
];


header('Location: ../penjualan.php');
exit;
