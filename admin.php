<?php
session_start();
if (empty($_SESSION['user_id'])) {
    // optional: kirim flash juga
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Silakan login terlebih dahulu'];
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | CV. Anugerah Presisi </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0b1c2d;
            color: #ffffff;
        }
        .header {
            background-color: #081624;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.4);
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            color: #e3f2fd;
        }
        .logout {
            color: #90caf9;
            text-decoration: none;
            font-size: 14px;
        }
        .hero {
            position: relative;
            background: linear-gradient(rgba(11,28,45,0.85), rgba(11,28,45,0.85)),
                        url('assets/images/admin-banner.jpg') center/cover no-repeat;
            padding: 80px 40px;
            text-align: center;
        }
        .hero h2 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #bbdefb;
        }
        .hero p {
            font-size: 16px;
            color: #cfd8dc;
        }
        .container {
            padding: 40px;
            max-width: 1200px;
            margin: auto;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
        }
        .card {
            background-color: #102a43;
            border-radius: 12px;
            padding: 30px 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.6);
        }
        .card h3 {
            margin-top: 0;
            color: #90caf9;
            font-size: 20px;
        }
        .card p {
            font-size: 14px;
            color: #cfd8dc;
            margin-bottom: 25px;
        }
        .card a {
            display: inline-block;
            padding: 10px 18px;
            background-color: #1e88e5;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }
        .card a:hover {
            background-color: #1565c0;
        }
        .banner {
            width: 100%;
            background-color: #081624;
        }
        .banner img {
            width: 100%;
            height: 320px;
            object-fit: cover;
            display: block;
        }
        
        footer {
            background-color: #081624;
            text-align: center;
            padding: 15px;
            font-size: 13px;
            color: #90a4ae;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Dashboard Admin – CV. Anugerah Presisi</h1>
    <a class="logout" href="logout.php">Logout</a>
</div>

<section class="hero">
    <h2>Selamat datang di Dashboard Admin</h2>
    <p>Masuk menu yang disediakan di bawah ini untuk Mengelola data barang, penjualan, dan pengiriman secara terintegrasi</p>
</section>

<div class="container">
    <div class="menu-grid">
        <div class="card">
            <h3>Manajemen Katalog Barang</h3>
            <p>Kelola data barang manufaktur besi, stok, spesifikasi produk, dan harga.</p>
            <a href="katalog.php">Masuk Menu</a>
        </div>
        <div class="card">
            <h3>Manajemen Penjualan</h3>
            <p>Kelola transaksi penjualan, data pelanggan, dan status pembayaran.</p>
            <a href="penjualan.php">Masuk Menu</a>
        </div>
        <div class="card">
            <h3>Manajemen Pengiriman</h3>
            <p>Kelola proses pengiriman, nomor resi, dan status distribusi barang.</p>
            <a href="pengiriman.php">Masuk Menu</a>
        </div>
    </div>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> CV. Anuegerah Presisi
</footer>

</body>
</html>
