<?php
session_start();
require_once __DIR__ . '/../config/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg' => 'ID pengiriman tidak valid'
    ];
    header('Location: ../pengiriman.php');
    exit;
}

/* CEK STATUS PENGIRIMAN */
$stmt = $conn->prepare("
    SELECT status_kirim 
    FROM tb_pengiriman 
    WHERE id_kirim = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg' => 'Data pengiriman tidak ditemukan'
    ];
    header('Location: ../pengiriman.php');
    exit;
}

/* ❌ BLOK JIKA BELUM SUDH TIBA */
if ($data['status_kirim'] !== 'Sudah Tiba') {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg' => 'Pengiriman belum selesai, tidak dapat dihapus'
    ];
    header('Location: ../pengiriman.php');
    exit;
}

/* ✅ HAPUS JIKA STATUS = SUDAH TIBA */
$del = $conn->prepare("
    DELETE FROM tb_pengiriman 
    WHERE id_kirim = ?
");
$del->bind_param('i', $id);

if ($del->execute()) {
    $_SESSION['flash'] = [
        'type' => 'success',
        'msg' => 'Data pengiriman berhasil dihapus'
    ];
} else {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg' => 'Gagal menghapus data pengiriman'
    ];
}

header('Location: ../pengiriman.php');
exit;
