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
    SELECT 
        p.*,
        b.nama_brg
    FROM tb_penjualan p
    LEFT JOIN tb_barang b ON p.id_brg = b.id_brg
    WHERE p.id_jual = ?
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

$modeTransaksi = '';

if ($order['jenis_bayar'] === 'DP') {
    if ($order['status_bayar'] === 'Belum Bayar') {
        $modeTransaksi = 'DP1';
    } elseif ($order['status_bayar'] === 'Lunas DP1') {
        $modeTransaksi = 'PELUNASAN';
    }
} else {
    $modeTransaksi = 'FULL';
}


$statusBayarBaru = '';
$statusOrderBaru = 'Menunggu Pengiriman';
$subject = '';
$body = '';

$total = (int)$order['harga_total'];
$dp    = $total * 0.5;
$sisa  = $total - $dp;

/* Default */
$nominalBayar   = $total;
$keterangan     = 'Pembayaran Pesanan (Lunas)';
$statusKwitansi = 'Lunas';

/* Jika DP */
switch ($modeTransaksi) {
    case 'DP1':
        $nominalBayar   = $dp;
        $keterangan     = 'Pembayaran Down Payment (DP) 50%';
        $statusKwitansi = 'DP 50% Dibayar';
        break;

    case 'PELUNASAN':
        $nominalBayar   = $sisa;
        $keterangan     = 'Pelunasan Pembayaran Pesanan';
        $statusKwitansi = 'Lunas';
        break;

    case 'FULL':
    default:
        $nominalBayar   = $total;
        $keterangan     = 'Pembayaran Pesanan (Lunas)';
        $statusKwitansi = 'Lunas';
        break;
}



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

        <p>Kami mengucapkan terima kasih telah melakukan pembayaran DP pesanan di <strong>CV. Anugerah Presisi</strong>.</p>

        <p>Kami mengonfirmasi bahwa pembayaran Anda telah <strong>berhasil kami terima</strong> dengan rincian sebagai berikut:</p>

       <b>Detail Pesanan:</b><br>
        Nama Barang: <b>{$order['nama_brg']}</b><br>
        Total Pesanan: <b>{$order['qty']} pcs</b><br>
        DP yang sudah terbayarkan (50%): <b>Rp. $dp</b><br>
        Sisa Pembayaran: <b>Rp. $sisa</b><br>
        Harga Total: <b>Rp $total</b><br><br>


        <p>Saat ini pesanan Anda telah masuk ke tahap pengerjaan awal dan akan segera kami proses sesuai prosedur.</p>

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
        <p>Kami mengucapkan terima kasih atas pelunasan pembayaran Full DP pesanan Anda di <strong>CV. Anugerah Presisi</strong>.</p>

        <p>Dengan ini kami konfirmasikan bahwa pembayaran pesanan Anda dengan rincian berikut dianggap lunas: </p> <br>
        <b>Detail Pesanan:</b><br>
        Nama Barang: <b>{$order['nama_brg']}</b><br>
        Total Pesanan: <b>{$order['qty']} pcs</b><br>
        DP yang sudah terbayarkan saat ini (Full): <b>Rp. $total</b><br>
        Harga Total: <b>Rp. $total</b><br><br>

        <p>Berhubung Anda telah melakukan pelunasan terhadap pesanan ini. Maka selanjutnya pesanan Anda akan segera masuk ke tahap <strong>Finishing</strong>.
        Kami akan menginformasikan kembali apabila proses telah selesai dan barang siap dikirim.</p>

        <p>Sebagai bukti pembayaran, kami lampirkan <strong>kwitansi</strong> pada email ini.</p>

        <p>Terima kasih atas kepercayaan dan kerja sama Anda.</p>
        <p>Hormat kami,<br>
            <strong>Admin CV. Anugerah Presisi</strong></p>
        ";
    }
} else {
    // BAYAR FULL
    $statusBayarBaru = 'Lunas Full';
    $statusOrderBaru = 'Lunas Pembayaran';

    $subject = 'Konfirmasi Pembayaran Berhasil';
    $body = "
    <p>Yth. <strong>{$nama}</strong>,</p>
    <p>Kami mengucapkan terima kasih telah melakukan pembayaran DP pesanan di <strong>CV. Anugerah Presisi</strong>.</p>
    <p>Kami mengonfirmasi bahwa pembayaran Anda telah <strong>berhasil kami terima</strong> dengan rincian sebagai berikut:</p>
    
    <b>Detail Pesanan:</b><br>
        Nama Barang: <b>{$order['nama_brg']}</b><br>
        Total Pesanan: <b>{$order['qty']} pcs</b><br>
        Harga Total: <b>Rp $total</b><br><br>
    <p>Pesanan Anda akan segera masuk ke tahap <strong>Pengerjaan</strong>.
    Kami akan menginformasikan kembali apabila proses telah selesai dan barang siap dikirim.</p>

    <p>Sebagai bukti pembayaran, kami lampirkan <strong>kwitansi</strong> pada email ini.</p>

    <p>Terima kasih atas kepercayaan dan kerja sama Anda.</p>
    <p>Hormat kami,<br>
        <strong>Admin CV. Anugerah Presisi</strong></p>
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
require_once __DIR__ . '/kwitansi/kwitansi_template.php';

$kwitansiData = [
    'id_jual'    => $id_jual,
    'nama'       => $nama,
    'barang'     => $order['nama_brg'] ?? 'Barang tidak tersedia',
    'qty'        => $order['qty'],
    'tanggal'    => date('d-m-Y'),
    'nominal'    => $nominalBayar,
    'total'      => $total,
    'keterangan' => $keterangan,
    'status'     => $statusKwitansi
];

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml(kwitansiHTML($kwitansiData));
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/* Folder kwitansi */
$dir = __DIR__ . '/../assets/kwitansi';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$kwitansiPath = $dir . "/kwitansi_{$id_jual}.pdf";
file_put_contents($kwitansiPath, $dompdf->output());


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
    $mail->addAttachment($kwitansiPath, 'Kwitansi_Pembayaran.pdf');


    $mail->setFrom('siapafikri045@gmail.com', 'Admin Presisi');
    $mail->addAddress($email, $nama);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    $mail->send();
} catch (Exception $e) {
    // kalau email gagal, status tetap update (opsional)
}

if (file_exists($kwitansiPath)) {
    unlink($kwitansiPath);
}


/* ======================
   REDIRECT
====================== */
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
