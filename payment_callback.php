<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/mailer.php';

$serverKey = getenv('MIDTRANS_SERVER_KEY');
$payload   = file_get_contents('php://input');
$data      = json_decode($payload, true);

if (!$data) {
    http_response_code(400);
    exit('Invalid payload');
}

// Verifikasi signature
$signatureKey = hash('sha512',
    $data['order_id'] .
    $data['status_code'] .
    $data['gross_amount'] .
    $serverKey
);

if ($signatureKey !== $data['signature_key']) {
    http_response_code(403);
    exit('Invalid signature');
}

$kode              = $data['order_id'];
$transactionStatus = $data['transaction_status'];
$fraudStatus       = $data['fraud_status'] ?? 'accept';

if ($transactionStatus === 'capture' && $fraudStatus === 'accept') {
    $statusBaru = 'diproses';
} elseif ($transactionStatus === 'settlement') {
    $statusBaru = 'diproses';
} elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
    $statusBaru = 'dibatalkan';
} else {
    http_response_code(200);
    exit('Ignored');
}

// Update status pesanan di DB
$stmt = $pdo->prepare("UPDATE pesanan SET status=? WHERE kode_pesanan=?");
$stmt->execute([$statusBaru, $kode]);

// Kirim notif Telegram kalau berhasil bayar
if ($statusBaru === 'diproses') {
    $row = $pdo->prepare("SELECT nama_penerima, total_harga FROM pesanan WHERE kode_pesanan=?");
    $row->execute([$kode]);
    $pesanan = $row->fetch();
    if ($pesanan) {
        kirimNotifPembayaran($kode, $pesanan['nama_penerima'], $pesanan['total_harga'], 'Lunas - Sedang Diproses');
    }
}

http_response_code(200);
echo 'OK';
