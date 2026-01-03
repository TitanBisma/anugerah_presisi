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

$id     = (int)($_POST['id_jual'] ?? 0);
$alasan = trim($_POST['alasan'] ?? '');

if ($id <= 0 || $alasan === '') {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg'  => 'Data tidak valid'
    ];
    header('Location: ../penjualan.php');
    exit;
}

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

if (!$data) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg'  => 'Pesanan tidak ditemukan'
    ];
    header('Location: ../penjualan.php');
    exit;
}

/* ======================
   UPDATE STATUS
====================== */
$upd = db()->prepare("
    UPDATE tb_penjualan
    SET status_order = 'Ditolak'
    WHERE id_jual = ?
");
$upd->bind_param("i", $id);
$upd->execute();

/* ======================
   EMAIL PENOLAKAN
====================== */
$nama = htmlspecialchars($data['nama_cust']);
$barang = htmlspecialchars($data['nama_brg']);
$qty = (int)$data['qty'];

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
    $mail->addAddress($data['email'], $nama);

    $mail->isHTML(true);
    $mail->Subject = 'Pesanan Anda Ditolak';
    $mail->Body = "
        <p>Yth. <strong>{$nama}</strong>,</p>

        <p>Terima kasih telah melakukan pemesanan di <strong>CV. Anugerah Presisi</strong>.</p>

        <p>Dengan berat hati kami informasikan bahwa pesanan berikut
        <strong>tidak dapat kami proses</strong>:</p>

        <b>Detail Pesanan:</b><br>
        Nama Barang: <b>{$barang}</b><br>
        Jumlah Pesanan: <b>{$qty} pcs</b><br><br>

        <b>Alasan Penolakan:</b><br>
        <i>{$alasan}</i><br><br>

        <p>Apabila Anda memerlukan informasi lebih lanjut,
        silakan menghubungi kami.</p>

        <p>Terima kasih atas pengertiannya.</p>

        <p>Hormat kami,<br>
        <strong>Admin CV. Anugerah Presisi</strong></p>
    ";

    $mail->send();

    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => 'Pesanan berhasil ditolak & email terkirim'
    ];

} catch (Exception $e) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg'  => 'Email gagal dikirim'
    ];
}

header('Location: ../penjualan.php');
exit;
