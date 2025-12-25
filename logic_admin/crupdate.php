<?php
session_start();

if (empty($_SESSION['user_id'])) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Silakan login terlebih dahulu'];
    header('Location: login.php');
    exit;
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/config.php';

/* ======================
   KONFIG UPLOAD
====================== */
$UPLOAD_DIR  = $_SERVER['DOCUMENT_ROOT'] . '/presisi/assets/upload/';
$PUBLIC_PATH = '/presisi/assets/upload/';

if (!is_dir($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR, 0777, true);
}

/* ======================
   TAMBAH DATA
====================== */
if (isset($_POST['action']) && $_POST['action'] === 'create') {

    $nama_brg  = trim($_POST['nama_brg']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga     = (int)$_POST['harga'];
    $pathFoto  = '';

    if (!empty($_FILES['foto']['name'])) {

        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allow = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allow)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Format foto tidak valid'];
            header("Location: ../katalog.php");
            exit;
        }

        $namaFile = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $UPLOAD_DIR . $namaFile);

        $pathFoto = $PUBLIC_PATH . $namaFile;
    }

    $stmt = db()->prepare("
        INSERT INTO tb_barang (nama_brg, urlfoto, deskripsi, harga)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("sssi", $nama_brg, $pathFoto, $deskripsi, $harga);
    $stmt->execute();

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Data berhasil disimpan'];
    header("Location: ../katalog.php");
    exit;
}

/* ======================
   HAPUS SEMUA (VALIDASI PESANAN)
====================== */
if (isset($_GET['action']) && $_GET['action'] === 'delete_all') {

    // Cek apakah ada pesanan aktif
    $cek = db()->query("
        SELECT COUNT(*) AS total 
        FROM tb_penjualan 
        WHERE status_order NOT IN ('Selesai', 'Ditolak')
    ");
    $row = $cek->fetch_assoc();

    if ($row['total'] > 0) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => 'Tidak bisa hapus semua. Masih ada pesanan yang aktif'
        ];
        header("Location: ../katalog.php");
        exit;
    }

    // Hapus file fisik
    $res = db()->query("SELECT urlfoto FROM tb_barang WHERE urlfoto != ''");
    while ($row = $res->fetch_assoc()) {
        $file = $_SERVER['DOCUMENT_ROOT'] . $row['urlfoto'];
        if (file_exists($file)) unlink($file);
    }

    // Hapus data
    db()->query("DELETE FROM tb_barang");

    // Reset AUTO_INCREMENT
    db()->query("ALTER TABLE tb_barang AUTO_INCREMENT = 1");

    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => 'Semua data barang berhasil dihapus'
    ];
    header("Location: ../katalog.php");
    exit;
}


/* ======================
   HAPUS PER ITEM (VALIDASI PESANAN)
====================== */
if (isset($_GET['action']) && $_GET['action'] === 'delete') {

    $id = (int)$_GET['id'];

    // Cek apakah barang masih dipakai pesanan aktif
    $cek = db()->prepare("
        SELECT COUNT(*) AS total 
        FROM tb_penjualan 
        WHERE id_brg = ? 
        AND status_order NOT IN ('Selesai', 'Ditolak')
    ");
    $cek->bind_param("i", $id);
    $cek->execute();
    $result = $cek->get_result()->fetch_assoc();

    if ($result['total'] > 0) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => 'Barang tidak bisa dihapus karena masih memiliki pesanan aktif'
        ];
        header("Location: ../katalog.php");
        exit;
    }

    // Hapus foto
    $res = db()->query("SELECT urlfoto FROM tb_barang WHERE id_brg=$id");
    if ($row = $res->fetch_assoc()) {
        if ($row['urlfoto']) {
            $file = $_SERVER['DOCUMENT_ROOT'] . $row['urlfoto'];
            if (file_exists($file)) unlink($file);
        }
    }

    db()->query("DELETE FROM tb_barang WHERE id_brg=$id");

    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => 'Barang berhasil dihapus'
    ];
    header("Location: ../katalog.php");
    exit;
}


/* ======================
   UPDATE DATA
====================== */
if (isset($_POST['action']) && $_POST['action'] === 'update') {

    $id_brg    = (int)$_POST['id_brg'];
    $nama_brg  = trim($_POST['nama_brg']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga     = (int)$_POST['harga'];
    $pathFoto  = $_POST['foto_lama'];

    if (!empty($_FILES['foto']['name'])) {

        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allow = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allow)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Format foto tidak valid'];
            header("Location: ../katalog.php");
            exit;
        }

        $namaFile = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $UPLOAD_DIR . $namaFile);

        // hapus foto lama
        if ($pathFoto && file_exists($_SERVER['DOCUMENT_ROOT'] . $pathFoto)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $pathFoto);
        }

        $pathFoto = $PUBLIC_PATH . $namaFile;
    }

    $stmt = db()->prepare("
        UPDATE tb_barang
        SET nama_brg=?, urlfoto=?, deskripsi=?, harga=?
        WHERE id_brg=?
    ");
    $stmt->bind_param("sssii", $nama_brg, $pathFoto, $deskripsi, $harga, $id_brg);
    $stmt->execute();

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Data berhasil diperbarui'];
    header("Location: ../katalog.php");
    exit;
}
