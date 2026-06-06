<?php
// Direct cURL test to Midtrans
$server_key = 'Mid-server-DpI1fOFjDp1l6CYFNcu1wDMf'; // GANTI DENGAN SERVER KEY LU!

$order_id = 'TEST-CURL-' . time();
$gross_amount = 100000;

$params = [
    'transaction_details' => [
        'order_id' => $order_id,
        'gross_amount' => $gross_amount
    ],
    'customer_details' => [
        'first_name' => 'John',
        'email' => 'john@example.com',
        'phone' => '08123456789'
    ],
    'item_details' => [
        [
            'id' => 'ITEM1',
            'price' => $gross_amount,
            'quantity' => 1,
            'name' => 'Test Item'
        ]
    ]
];

echo "<h2>Direct cURL Test to Midtrans</h2>";
echo "<pre>Order ID: $order_id\nAmount: $gross_amount</pre>";

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://api.sandbox.midtrans.com/v2/snap/transactions',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($params),
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($server_key . ':')
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_VERBOSE => true
]);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curl_error = curl_error($curl);

curl_close($curl);

echo "<h3>Response:</h3>";
echo "<pre>HTTP Code: $http_code</pre>";

if ($curl_error) {
    echo "<pre style='background:red;color:white;padding:10px;'>cURL Error: $curl_error</pre>";
}

echo "<pre style='background:#f5f5f5;padding:10px;'>";
echo htmlspecialchars($response);
echo "</pre>";

$result = json_decode($response, true);

if ($http_code == 201 && isset($result['token'])) {
    echo "<div style='background:green;color:white;padding:20px;'>";
    echo "<h2>✅ SUCCESS!</h2>";
    echo "<p>Token: " . $result['token'] . "</p>";
    echo "<p><a href='" . $result['redirect_url'] . "' target='_blank'>Open Payment Page</a></p>";
    echo "</div>";
} else {
    echo "<div style='background:red;color:white;padding:20px;'>";
    echo "<h2>❌ FAILED!</h2>";
    echo "<p>HTTP Code: $http_code</p>";
    if (isset($result['error_messages'])) {
        echo "<p>Error: " . implode(', ', $result['error_messages']) . "</p>";
    }
    echo "</div>";
}
?>
```

**Ganti Server Key di line 3**, terus test:
```
http://localhost/poncolverse/test_curl_direct.php