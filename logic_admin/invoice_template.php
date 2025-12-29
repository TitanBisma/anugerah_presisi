<?php
function invoiceHTML($data)
{
    // Tentukan judul kwitansi
    if (strpos($data['status'], 'DP') !== false) {
        $judul = 'KWITANSI PEMBAYARAN DP';
    } elseif (strpos($data['status'], 'Pelunasan') !== false) {
        $judul = 'KWITANSI PELUNASAN';
    } else {
        $judul = 'KWITANSI PEMBAYARAN';
    }

    return '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; }
            h2 { color: #1e40af; text-align:center; }
            table { width:100%; border-collapse: collapse; margin-top:15px; }
            td, th { border:1px solid #ccc; padding:8px; }
            th { background:#f1f5f9; }
        </style>
    </head>
    <body>

        <h2>'.$judul.'</h2>

        <p>
            <b>Nama:</b> '.$data['nama'].'<br>
            <b>Email:</b> '.$data['email'].'<br>
            <b>Tanggal:</b> '.$data['tgl'].'
        </p>

        <table>
            <tr>
                <th>Barang</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Total</th>
            </tr>
            <tr>
                <td>'.$data['barang'].'</td>
                <td>'.$data['qty'].'</td>
                <td>Rp '.number_format($data['harga'],0,",",".").'</td>
                <td>Rp '.number_format($data['total'],0,",",".").'</td>
            </tr>
        </table>

        <p><b>Status:</b> '.$data['status'].'</p>

        <p style="margin-top:30px; text-align:right;">
            Hormat kami,<br><br>
            <b>CV. Anugerah Presisi</b>
        </p>

    </body>
    </html>';
}