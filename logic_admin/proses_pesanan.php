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
$stmt = $conn->prepare("SELECT * FROM tb_penjualan WHERE id_jual = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Data tidak ditemukan'];
    header('Location: ../penjualan.php');
    exit;
}

/* ======================
   TENTUKAN STATUS BARU
====================== */
$statusLama = $data['status_order'];
$statusBaru = '';
$subject    = '';
$message    = '';

$nama = htmlspecialchars($data['nama_cust']);

switch ($statusLama) {
    case 'Lunas Pembayaran':
        $statusBaru = 'Dalam Pengerjaan';
        $subject = 'Pesanan Anda Sedang Diproses';
        $message = "
        Yth. Bapak/Ibu <b>$nama</b>,<br><br>
        Kami informasikan bahwa pesanan Anda saat ini sedang dalam <b>tahap pengerjaan</b>.<br><br>
        Tim kami sedang memproses pesanan sesuai dengan spesifikasi yang telah disepakati.
        Kami akan menghubungi Anda kembali apabila pesanan telah selesai dan siap dikirim.<br><br>
        Terima kasih atas kesabaran dan kepercayaan Anda.<br><br>
        Hormat kami,<br>
        <b>Admin Presisi</b>
        ";
        break;

    case 'Dalam Pengerjaan':
        $statusBaru = 'Barang Sudah Siap';
        $subject = 'Pesanan Telah Siap Dikirim';
        $message = "
        Yth. Bapak/Ibu <b>$nama</b>,<br><br>
        Dengan senang hati kami informasikan bahwa pesanan Anda telah <b>selesai diproduksi</b> dan siap untuk dikirim.<br><br>
        Kami akan segera mengatur proses pengiriman dan menginformasikan detail pengiriman selanjutnya.<br><br>
        Terima kasih atas kepercayaan Anda kepada <b>Presisi</b>.<br><br>
        Hormat kami,<br>
        <b>Admin Presisi</b>
        ";
        break;

    case 'Barang Sudah Siap':
        $statusBaru = 'Menunggu Dikirim';
        $subject = 'Pesanan Siap Dikirim';
        $message = "
            Yth. <b>$nama</b>,<br><br>
            Pesanan Anda telah <b>siap dikirim</b>.
            Kami sedang menyiapkan proses pengiriman ke alamat tujuan.<br><br>
            Mohon ditunggu informasi selanjutnya.
            <br><br>Hormat kami,<br>
            <b>Tim Presisi</b>
        ";
        break;

    default:
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Status tidak dapat diproses'];
        header('Location: ../penjualan.php');
        exit;
}

/* ======================
   UPDATE STATUS
====================== */
$upd = $conn->prepare("UPDATE tb_penjualan SET status_order = ? WHERE id_jual = ?");
$upd->bind_param("si", $statusBaru, $id);
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
    $mail->addAddress($data['email'], $data['nama_cust']);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $message;
    $mail->send();
} catch (Exception $e) {
    // email gagal → status tetap naik
}

/* ======================
   FLASH & REDIRECT
====================== */
$_SESSION['flash'] = [
    'type' => 'success',
    'msg'  => 'Pesanan berhasil diproses & email dikirim'
];

header('Location: ../penjualan.php');
exit;
