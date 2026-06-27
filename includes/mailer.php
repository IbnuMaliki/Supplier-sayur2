<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

function kirimEmailPesananBaru($kode, $namaPenerima, $noHp, $alamat, $catatan, $metode, $total, $items) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('MAIL_USERNAME');
        $mail->Password   = getenv('MAIL_PASSWORD');
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(getenv('MAIL_USERNAME'), 'Supplier Sayur Azam Heri');
        $mail->addAddress(getenv('MAIL_USERNAME'), 'Admin');

        $mail->isHTML(true);
        $mail->Subject = "Pesanan Baru Masuk - $kode";

        $itemRows = '';
        foreach ($items as $item) {
            $h = $item['jumlah'] >= $item['min_grosir'] ? $item['harga_grosir'] : $item['harga'];
            $sub = number_format($h * $item['jumlah'], 0, ',', '.');
            $itemRows .= "<tr>
                <td style='padding:8px;border:1px solid #e2e8f0;'>{$item['nama']}</td>
                <td style='padding:8px;border:1px solid #e2e8f0;text-align:center;'>{$item['jumlah']}</td>
                <td style='padding:8px;border:1px solid #e2e8f0;text-align:right;'>Rp {$sub}</td>
            </tr>";
        }

        $totalFmt = number_format($total, 0, ',', '.');
        $catatanTeks = $catatan ?: '-';

        $mail->Body = "
        <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
            <div style='background:#16a34a;padding:20px;text-align:center;border-radius:8px 8px 0 0;'>
                <h2 style='color:white;margin:0;'>Pesanan Baru Masuk!</h2>
            </div>
            <div style='background:#f8fafc;padding:24px;border:1px solid #e2e8f0;'>
                <table style='width:100%;border-collapse:collapse;margin-bottom:16px;'>
                    <tr><td style='padding:8px;color:#64748b;width:40%;'>Kode Pesanan</td><td style='padding:8px;font-weight:700;color:#16a34a;'>$kode</td></tr>
                    <tr><td style='padding:8px;color:#64748b;'>Nama Penerima</td><td style='padding:8px;'>$namaPenerima</td></tr>
                    <tr><td style='padding:8px;color:#64748b;'>No. HP</td><td style='padding:8px;'>$noHp</td></tr>
                    <tr><td style='padding:8px;color:#64748b;'>Alamat</td><td style='padding:8px;'>$alamat</td></tr>
                    <tr><td style='padding:8px;color:#64748b;'>Catatan</td><td style='padding:8px;'>$catatanTeks</td></tr>
                    <tr><td style='padding:8px;color:#64748b;'>Metode Bayar</td><td style='padding:8px;'>$metode</td></tr>
                </table>
                <table style='width:100%;border-collapse:collapse;'>
                    <thead>
                        <tr style='background:#16a34a;color:white;'>
                            <th style='padding:8px;border:1px solid #15803d;text-align:left;'>Produk</th>
                            <th style='padding:8px;border:1px solid #15803d;text-align:center;'>Qty</th>
                            <th style='padding:8px;border:1px solid #15803d;text-align:right;'>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>$itemRows</tbody>
                    <tfoot>
                        <tr style='background:#f0fdf4;font-weight:700;'>
                            <td colspan='2' style='padding:10px;border:1px solid #e2e8f0;'>Total</td>
                            <td style='padding:10px;border:1px solid #e2e8f0;text-align:right;color:#16a34a;'>Rp $totalFmt</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div style='background:#e2e8f0;padding:12px;text-align:center;border-radius:0 0 8px 8px;font-size:12px;color:#64748b;'>
                Supplier Sayur Azam Heri — Notifikasi Otomatis
            </div>
        </div>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Gagal kirim email: " . $mail->ErrorInfo);
    }
}
