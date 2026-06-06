<?php
require_once 'config/midtrans_config.php';

// Test data
$order_id = 'TEST-' . time();
$gross_amount = 100000;

$customer_details = [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com',
    'phone' => '08123456789'
];

$item_details = [
    [
        'id' => 'ITEM1',
        'price' => 100000,
        'quantity' => 1,
        'name' => 'Test Item'
    ]
];

echo "<h2>Testing Midtrans Connection...</h2>";
echo "<pre>";
echo "Order ID: $order_id\n";
echo "Amount: Rp " . number_format($gross_amount, 0, ',', '.') . "\n";
echo "Server Key: " . substr(MIDTRANS_SERVER_KEY, 0, 20) . "...\n";
echo "API URL: " . MIDTRANS_API_URL . "\n\n";
echo "</pre>";

// ✅ TEST 1: Simple request
echo "<h3>Test 1: Creating Snap Token...</h3>";
$result = createMidtransToken($order_id, $gross_amount, $customer_details, $item_details);

echo "<pre>";
print_r($result);
echo "</pre>";

if ($result['success']) {
    echo "<div style='background:green;color:white;padding:20px;'>";
    echo "<h2>✅ SUCCESS!</h2>";
    echo "<p>Token: " . $result['token'] . "</p>";
    echo "<p><a href='" . $result['redirect_url'] . "' target='_blank'>Open Payment Page</a></p>";
    echo "</div>";
} else {
    echo "<div style='background:red;color:white;padding:20px;'>";
    echo "<h2>❌ FAILED!</h2>";
    echo "<p>Error: " . $result['message'] . "</p>";
    echo "<p>HTTP Code: " . $result['http_code'] . "</p>";
    echo "</div>";
    
    // ✅ Troubleshooting hints
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    
    if ($result['http_code'] == 404) {
        echo "<li><strong>HTTP 404:</strong> Payment method belum aktif di dashboard!</li>";
        echo "<li>Solusi: Aktifin Credit Card atau Bank Transfer di Settings → Snap Preferences</li>";
        echo "<li>Atau edit config, hapus 'enabled_payments' biar auto pake yang aktif</li>";
    } elseif ($result['http_code'] == 401) {
        echo "<li><strong>HTTP 401:</strong> Server Key salah!</li>";
        echo "<li>Cek lagi Server Key di config/midtrans_config.php</li>";
    } elseif ($result['http_code'] == 400) {
        echo "<li><strong>HTTP 400:</strong> Parameter salah!</li>";
        echo "<li>Cek struktur data yang dikirim</li>";
    }
    
    echo "</ul>";
}

// Check error log
echo "<h3>Recent Error Log:</h3>";
echo "<pre style='background:#f5f5f5;padding:10px;max-height:300px;overflow:auto;'>";

$error_log = 'C:/xampp/apache/logs/error.log';
if (file_exists($error_log)) {
    $log_content = file_get_contents($error_log);
    $lines = explode("\n", $log_content);
    $recent_logs = array_slice($lines, -30);
    
    foreach ($recent_logs as $line) {
        if (strpos($line, 'MIDTRANS') !== false || strpos($line, 'HTTP Code') !== false) {
            echo htmlspecialchars($line) . "\n";
        }
    }
} else {
    echo "Error log not found at: $error_log";
}

echo "</pre>";
?>
```

---

## 🎯 **EXPECTED BEHAVIOR:**

### **Scenario A: Kalo Payment Methods Udah Aktif**
```
✅ SUCCESS!
Token: abc123-xyz789
HTTP Code: 201
```

### **Scenario B: Kalo Masih 404**
```
❌ FAILED!
HTTP Code: 404
Error: Not Found: Endpoint salah atau payment method belum aktif