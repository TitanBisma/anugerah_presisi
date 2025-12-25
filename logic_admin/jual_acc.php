<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/invoice_template.php';

use PHPMailer\PHPMailer\PHPMailer;
use Dompdf\Dompdf;

$id = (int)($_GET['id'] ?? 0);
if (!$id) die('Invalid ID');

/* Ambil data */
$q = db()->query("
    SELECT p.*, b.nama_brg 
    FROM tb_penjualan p
    JOIN tb_barang b ON p.id_brg=b.id_brg
    WHERE p.id_jual=$id
");
$data = $q->fetch_assoc();

/* Update status */
db()->query("
    UPDATE tb_penjualan
    SET status_order='Menunggu Pembayaran'
    WHERE id_jual=$id
");

/* ===== GENERATE PDF ===== */
$dompdf = new Dompdf();
$html = invoiceHTML([
    'nama'   => $data['nama_cust'],
    'email'  => $data['email'],
    'tgl'    => date('d-m-Y'),
    'barang' => $data['nama_brg'],
    'qty'    => $data['qty'],
    'harga'  => $data['harga_satuan'],
    'total'  => $data['harga_total'],
    'status' => 'Pesanan Diterima'
]);

$dompdf->loadHtml($html);
$dompdf->render();

$pdfPath = __DIR__ . '/../invoices/invoice_'.$id.'.pdf';
file_put_contents($pdfPath, $dompdf->output());

/* ===== KIRIM EMAIL ===== */
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
$mail->addAttachment($pdfPath);

$mail->Subject = 'Pesanan Anda Telah Diterima';
$mail->Body    = "Pesanan Anda telah kami terima.\nInvoice terlampir.";

$mail->send();

header('Location: ../penjualan.php');
