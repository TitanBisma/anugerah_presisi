<?php
session_start();
require_once __DIR__ . '/../config/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Validasi parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ../penjualan.php');
    exit;
}

$id_jual = (int) $_GET['id'];

// Ambil data pesanan
$query = "SELECT status_order FROM tb_penjualan WHERE id_jual = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_jual);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($result)) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg'  => 'Data pesanan tidak ditemukan'
    ];
    header('Location: ../penjualan.php');
    exit;
}

// Validasi status harus SELESAI
if ($row['status_order'] !== 'Selesai') {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg'  => 'Pesanan hanya bisa dihapus jika statusnya Selesai'
    ];
    header('Location: ../penjualan.php');
    exit;
}

// Hapus pesanan
$delete = "DELETE FROM tb_penjualan WHERE id_jual = ?";
$stmtDel = mysqli_prepare($conn, $delete);
mysqli_stmt_bind_param($stmtDel, "i", $id_jual);

if (mysqli_stmt_execute($stmtDel)) {
    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => 'Pesanan berhasil dihapus'
    ];
} else {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg'  => 'Gagal menghapus pesanan'
    ];
}

header('Location: ../penjualan.php');
exit;
