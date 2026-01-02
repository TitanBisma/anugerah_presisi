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
if ($id <= 0) die('Invalid ID');

/* ======================
   AMBIL DATA PESANAN
====================== */
$stmt = db()->prepare("
    SELECT p.*, b.nama_brg
    FROM tb_penjualan p
    JOIN tb_barang b ON p.id_brg = b.id_brg
    WHERE p.id_jual = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) die('Data tidak ditemukan');

$nama  = htmlspecialchars($data['nama_cust']);
$totalAngka = (int)$data['harga_total'];
$qty   = (int)$data['qty'];

$dpAngka   = $totalAngka * 0.5;
$sisaAngka = $totalAngka - $dpAngka;

$total = number_format($totalAngka, 0, ',', '.');
$dp    = number_format($dpAngka, 0, ',', '.');
$sisa  = number_format($sisaAngka, 0, ',', '.');


/* ======================
   UPDATE STATUS ORDER
====================== */
$upd = db()->prepare("
    UPDATE tb_penjualan
    SET status_order = 'Menunggu Pembayaran'
    WHERE id_jual = ?
");
$upd->bind_param("i", $id);
$upd->execute();

/* ======================
   DATA INVOICE
====================== */
$invoiceData = [
    'nama'    => $data['nama_cust'],
    'email'   => $data['email'],
    'produk'  => $data['nama_brg'],
    'qty'     => $data['qty'],
    'harga'   => number_format($data['harga_satuan'], 0, ',', '.'),
    'total'   => number_format($data['harga_total'], 0, ',', '.'),
    'tanggal' => date('d-m-Y')
];

/* ======================
   GENERATE PDF INVOICE
====================== */
require_once __DIR__ . '/invoice/invoice_pdf.php';
$invoicePath = generateInvoicePDF($invoiceData, $id);

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

    $mail->setFrom('siapafikri045@gmail.com', 'CV Anugerah Presisi');
    $mail->addAddress($data['email'], $data['nama_cust']);
    $mail->addAttachment($invoicePath, 'Invoice_Pesanan_' . $id . '.pdf');

    $mail->isHTML(true);

    $subject = '';
    $message = '';

    /* ======================
   TENTUKAN JENIS EMAIL
====================== */

    // 🔹 PEMBAYARAN DP
    if ($data['jenis_bayar'] === 'DP') {

        $subject = 'Invoice Pembayaran DP Pesanan Anda';

        $message = "
    <p>Yth. <strong>{$nama}</strong>,</p>

    <p>Terima kasih telah melakukan pemesanan di <strong>CV. Anugerah Presisi</strong>.</p>

    <p>Pesanan Anda telah <strong>kami terima dan setujui</strong>.  
    Berdasarkan metode pembayaran yang Anda pilih, pembayaran dilakukan dengan sistem
    <strong>Down Payment (DP) 50%</strong>.</p>

    <b>Detail Pesanan:</b><br>
    Nama Barang: <b>{$data['nama_brg']}</b><br>
    Jumlah Pesanan: <b>{$qty} pcs</b><br>
    Harga Total: <b>Rp {$total}</b><br>
    DP yang Harus Dibayarkan (50%): <b>Rp {$dp}</b><br><br>

    <p>Mohon kesediaannya untuk melakukan pembayaran DP agar pesanan dapat segera kami proses.</p>

    <p>Sebagai referensi, kami lampirkan <strong>invoice resmi</strong> pada email ini.</p>

    <p>Terima kasih atas kepercayaan Anda.</p>

    <p>Hormat kami,<br>
    <strong>Admin CV. Anugerah Presisi</strong></p>
    ";
    }
    // 🔹 PEMBAYARAN FULL
    else {

        $subject = 'Invoice Pembayaran Pesanan Anda';

        $message = "
    <p>Yth. <strong>{$nama}</strong>,</p>

    <p>Terima kasih telah melakukan pemesanan di <strong>CV. Anugerah Presisi</strong>.</p>

    <p>Pesanan Anda telah <strong>kami terima dan setujui</strong>.  
    Silakan melakukan pembayaran penuh sesuai detail berikut:</p>

    <b>Detail Pesanan:</b><br>
    Nama Barang: <b>{$data['nama_brg']}</b><br>
    Jumlah Pesanan: <b>{$qty} pcs</b><br>
    Harga Total: <b>Rp {$total}</b><br><br>

    <p>Invoice resmi kami lampirkan sebagai bukti dan referensi pembayaran.</p>

    <p>Terima kasih atas kepercayaan Anda.</p>

    <p>Hormat kami,<br>
    <strong>Admin CV. Anugerah Presisi</strong></p>
    ";
    }

    $mail->Subject = $subject;
    $mail->Body    = $message;

    $mail->send();

    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => 'Pesanan berhasil di-ACC & invoice terkirim'
    ];
} catch (Exception $e) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg'  => 'Email gagal dikirim'
    ];
}

/* hapus file */
if (file_exists($invoicePath)) {
    unlink($invoicePath);
}

header('Location: ../penjualan.php');
exit;
