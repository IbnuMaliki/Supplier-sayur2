<?php
function kirimNotifWhatsApp($kode, $namaPenerima, $noHp, $alamat, $catatan, $metode, $total, $items) {
    $itemList = '';
    foreach ($items as $item) {
        $h = $item['jumlah'] >= $item['min_grosir'] ? $item['harga_grosir'] : $item['harga'];
        $sub = number_format($h * $item['jumlah'], 0, ',', '.');
        $itemList .= "- {$item['nama']} x{$item['jumlah']} = Rp {$sub}\n";
    }
    $totalFmt = number_format($total, 0, ',', '.');
    $catatan  = $catatan ?: '-';

    $pesan = "🥬 <b>PESANAN BARU MASUK!</b>\n\n"
           . "📋 <b>Kode:</b> $kode\n"
           . "👤 <b>Nama:</b> $namaPenerima\n"
           . "📱 <b>HP:</b> $noHp\n"
           . "📍 <b>Alamat:</b> $alamat\n"
           . "📝 <b>Catatan:</b> $catatan\n"
           . "💳 <b>Metode:</b> $metode\n\n"
           . "<b>Detail Produk:</b>\n$itemList\n"
           . "💰 <b>Total: Rp $totalFmt</b>";

    $token  = getenv('TELEGRAM_BOT_TOKEN');
    $chatId = getenv('TELEGRAM_CHAT_ID');

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => "https://api.telegram.org/bot{$token}/sendMessage",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode([
            'chat_id'    => $chatId,
            'text'       => $pesan,
            'parse_mode' => 'HTML',
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
        ],
    ]);
    curl_exec($curl);
    curl_close($curl);
}

function kirimNotifPembayaran($kode, $namaPenerima, $total, $status) {
    $totalFmt = number_format($total, 0, ',', '.');
    $pesan = "✅ <b>PEMBAYARAN DITERIMA!</b>\n\n"
           . "📋 <b>Kode:</b> $kode\n"
           . "👤 <b>Nama:</b> $namaPenerima\n"
           . "💰 <b>Total:</b> Rp $totalFmt\n"
           . "📌 <b>Status:</b> $status";

    $token  = getenv('TELEGRAM_BOT_TOKEN');
    $chatId = getenv('TELEGRAM_CHAT_ID');

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => "https://api.telegram.org/bot{$token}/sendMessage",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode([
            'chat_id'    => $chatId,
            'text'       => $pesan,
            'parse_mode' => 'HTML',
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    ]);
    curl_exec($curl);
    curl_close($curl);
}