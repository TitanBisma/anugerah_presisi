<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;


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

        $subject = 'Konfirmasi Pembayaran Pesanan Anda';

        $body = "
        <p>Yth. <strong>{$nama}</strong>,</p>

        <p>Terima kasih telah melakukan pembayaran pesanan di <strong>Presisi</strong>.</p>

        <p>Kami mengonfirmasi bahwa pembayaran Anda telah <strong>berhasil kami terima</strong> dengan rincian sebagai berikut:</p>

        <ul>
            <li><strong>Nomor Pesanan</strong> : #{$id_jual}</li>
            <li><strong>Jenis Pembayaran</strong> : {$order['jenis_bayar']}</li>
            <li><strong>Status Pembayaran</strong> : {$statusBayarBaru}</li>
        </ul>

        <p>Saat ini pesanan Anda telah masuk ke tahap selanjutnya dan akan segera kami proses sesuai prosedur.</p>

        <p>Apabila Anda memiliki pertanyaan lebih lanjut, jangan ragu untuk menghubungi kami.</p>

        <p>Terima kasih atas kepercayaan Anda kepada <strong>Presisi</strong>.</p>

        <p>Hormat kami,<br>
        <strong>Tim CV. Anugerah Presisi</strong></p>
    ";
    }

    // DP tahap 2 (PELUNASAN)
    elseif ($order['status_bayar'] === 'Lunas DP1') {

        $statusBayarBaru = 'Lunas Full';
        $statusOrderBaru = 'Lunas Pembayaran';

        $subject = 'Pelunasan Pembayaran Pesanan Berhasil';

        $body = "
        <p>Yth. <strong>{$nama}</strong>,</p>

        <p>Kami mengucapkan terima kasih atas pelunasan pembayaran pesanan Anda.</p>

        <p>Dengan ini kami konfirmasikan bahwa pembayaran pesanan dengan nomor
        <strong>#{$id_jual}</strong> telah <strong>lunas sepenuhnya</strong>.</p>

        <p>Pesanan Anda akan segera masuk ke tahap <strong>pengerjaan</strong>.
        Kami akan menginformasikan kembali apabila proses telah selesai dan barang siap dikirim.</p>

        <p>Sebagai bukti pembayaran, kami lampirkan <strong>kwitansi resmi</strong> pada email ini.</p>

        <p>Terima kasih atas kepercayaan dan kerja sama Anda.</p>

        <p>Hormat kami,<br>
        <strong>Tim Presisi</strong></p>
        ";
    }
    
} else {
    // BAYAR FULL
    $statusBayarBaru = 'Lunas Full';
    $statusOrderBaru = 'Lunas Pembayaran';

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

// Data Invoice
require_once __DIR__ . '/../logic_admin/invoice_template.php';

/* Tentukan jenis invoice */
$jenisInvoice = 'Pembayaran Lunas';

if ($order['jenis_bayar'] === 'DP') {
    if ($statusBayarBaru === 'Lunas DP1') {
        $jenisInvoice = 'Pembayaran DP 50%';
    } else {
        $jenisInvoice = 'Pelunasan Pembayaran';
    }
}

/* Data invoice */
$invoiceData = [
    'nama'   => $nama,
    'email'  => $email,
    'tgl'    => date('d-m-Y'),
    'barang' => 'Pesanan #' . $id_jual,
    'qty'    => 1,
    'harga'  => $order['harga_total'],
    'total'  => $order['harga_total'],
    'status' => $jenisInvoice
];

$htmlInvoice = invoiceHTML($invoiceData);

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml(invoiceHTML($invoiceData));
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/* pastikan folder ada */
$dir = __DIR__ . '/../assets/kwitansi';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$invoicePath = $dir . "/kwitansi_{$id_jual}.pdf";
file_put_contents($invoicePath, $dompdf->output());


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
    $mail->addAttachment($invoicePath, 'Kwitansi_Pembayaran.pdf');

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
