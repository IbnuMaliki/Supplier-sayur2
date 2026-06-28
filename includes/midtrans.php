<?php
function getMidtransSnapToken($kode, $total, $nama, $hp, $email, $items) {
    $serverKey = getenv('MIDTRANS_SERVER_KEY');
    $isProduction = getenv('MIDTRANS_IS_PRODUCTION') === 'true';
    $baseUrl = $isProduction
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

    $itemDetails = [];
    foreach ($items as $item) {
        $h = $item['jumlah'] >= $item['min_grosir'] ? $item['harga_grosir'] : $item['harga'];
        $itemDetails[] = [
            'id'       => (string)$item['produk_id'],
            'price'    => (int)$h,
            'quantity' => (int)$item['jumlah'],
            'name'     => substr($item['nama'], 0, 50),
        ];
    }

    $payload = [
        'transaction_details' => [
            'order_id'     => $kode,
            'gross_amount' => (int)$total,
        ],
        'customer_details' => [
            'first_name' => $nama,
            'phone'      => $hp,
            'email'      => $email ?: 'customer@suppliersayur.com',
        ],
        'item_details'   => $itemDetails,
        'enabled_payments' => ['qris', 'gopay', 'shopeepay'],
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => $baseUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($serverKey . ':'),
        ],
    ]);
    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);
    return $data['token'] ?? null;
}