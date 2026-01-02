<?php
function invoiceHTML(array $d): string
{
    return "
<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<style>
body { font-family: Arial; font-size: 12px; }
h2 { text-align: center; }
table { width: 100%; border-collapse: collapse; }
td, th { border: 1px solid #333; padding: 6px; }
</style>
</head>
<body>

<h2>INVOICE PEMBAYARAN</h2>

<p>
<b>Nama:</b> {$d['nama']}<br>
<b>Email:</b> {$d['email']}<br>
<b>Tanggal:</b> {$d['tanggal']}
</p>

<table>
<tr>
<th>Produk</th>
<th>Jumlah Barang</th>
<th>Harga per Barang</th>
<th>Harga Total</th>
</tr>
<tr>
<td>{$d['produk']}</td>
<td>{$d['qty']}</td>
<td>Rp {$d['harga']}</td>
<td>Rp {$d['total']}</td>
</tr>
</table>

<p><b>Harga Total: Rp {$d['total']}</b></p>

</body>
</html>
";
}
