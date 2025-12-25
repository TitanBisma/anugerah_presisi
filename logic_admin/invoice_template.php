<?php
function invoiceHTML($data)
{
    return '
    <html>
    <style>
        body { font-family: Arial; }
        h2 { color: #1e40af; }
        table { width:100%; border-collapse: collapse; }
        td, th { border:1px solid #ccc; padding:8px; }
    </style>
    <body>
        <h2>INVOICE PEMESANAN</h2>

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
    </body>
    </html>';
}
