<?php
use Dompdf\Dompdf;

function generateInvoicePDF(array $data, int $idJual): string
{
    require_once __DIR__ . '/invoice_template.php';

    $dompdf = new Dompdf();
    $dompdf->loadHtml(invoiceHTML($data));
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dir = __DIR__ . '/../../storage/invoice/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $path = $dir . "invoice_{$idJual}.pdf";
    file_put_contents($path, $dompdf->output());

    return $path;
}
