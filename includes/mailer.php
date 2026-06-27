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
    $pesan = "🥬 *PESANAN BARU MASUK\!*\n\n"
           . "📋 *Kode:* $kode\n"
           . "👤 *Nama:* $namaPenerima\n"
           . "📱 *HP:* $noHp\n"
           . "📍 *Alamat:* $alamat\n"
           . "📝 *Catatan:* $catatan\n"
           . "💳 *Metode:* $metode\n\n"
           . "*Detail Produk:*\n$itemList\n"
           . "💰 *Total: Rp $totalFmt*";

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
            'parse_mode' => 'MarkdownV2',
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
        ],
    ]);
    curl_exec($curl);
    curl_close($curl);
}
