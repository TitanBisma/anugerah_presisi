<?php
function kwitansiHTML(array $data)
{
    $nominal = number_format($data['nominal'], 0, ',', '.');
    $total   = number_format($data['total'], 0, ',', '.');


    return "
<!DOCTYPE html>
<html lang='id'>
<head>
<meta charset='UTF-8'>
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        color: #000;
    }
    .header {
        text-align: center;
        margin-bottom: 20px;
    }
    .header h2 {
        margin: 0;
        font-size: 20px;
        text-transform: uppercase;
    }
    .header p {
        margin: 2px 0;
        font-size: 11px;
    }
    .content {
        margin-top: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    table td {
        padding: 6px;
        vertical-align: top;
    }
    .border td {
        border: 1px solid #000;
    }
    .footer {
        margin-top: 40px;
        text-align: right;
    }
</style>
</head>

<body>

<div class='header'>
    <h2>Kwitansi Pembayaran</h2>
    <p>CV. Anugerah Presisi</p>
    <p>Tanggal: {$data['tanggal']}</p>
</div>

<div class='content'>
    <table>
        <tr>
            <td width='30%'>No. Kwitansi</td>
            <td width='70%'>: KW-{$data['id_jual']}</td>
        </tr>
        <tr>
            <td>Telah Terima Dari</td>
            <td>: <strong>{$data['nama']}</strong></td>
        </tr>
        <tr>
            <td>Untuk Pembayaran</td>
            <td>: {$data['keterangan']}</td>
        </tr>
        <tr>
            <td>Nomor Pesanan</td>
            <td>: #{$data['id_jual']}</td>
        </tr>
    </table>

    <table class='border'>   
    <tr>
        <td width='40%'>Nama Barang:</td>
        <td width='60%'><strong>{$data['barang']}</strong></td>
    </tr>

    <tr>
        <td>Jumlah:</td>
        <td>{$data['qty']} unit</td>
    </tr>

    <tr>
        <td><strong>Jumlah Dibayarkan</strong></td>
        <td><strong>Rp {$nominal}</strong></td>
    </tr>

    <tr>
        <td>Total Nilai Pesanan</td>
        <td>Rp {$total}</td>
    </tr>

    <tr>
        <td>Status Pembayaran</td>
        <td><strong>{$data['status']}</strong></td>
    </tr>
</table>

</div>

<div class='footer'>
    <p>Hormat Kami,</p>
    <br><br>
    <strong>Admin Presisi</strong>
</div>

</body>
</html>
";
}
