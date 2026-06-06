<?php
// Load Environment Variables if not already loaded
require_once __DIR__ . '/env_loader.php';

// Midtrans Configuration
define('MIDTRANS_SERVER_KEY', env('MIDTRANS_SERVER_KEY', 'xxx'));
define('MIDTRANS_CLIENT_KEY', env('MIDTRANS_CLIENT_KEY', 'xxx'));
define('MIDTRANS_IS_PRODUCTION', env('MIDTRANS_IS_PRODUCTION', false));
define('MIDTRANS_IS_SANITIZED', env('MIDTRANS_IS_SANITIZED', true));
define('MIDTRANS_IS_3DS', env('MIDTRANS_IS_3DS', true));

// Midtrans API URL
if (MIDTRANS_IS_PRODUCTION) {
    define('MIDTRANS_SNAP_URL', 'https://app.midtrans.com/snap/snap.js');
    define('MIDTRANS_API_URL', 'https://api.midtrans.com');
} else {
    define('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/snap.js');
    define('MIDTRANS_API_URL', 'https://api.sandbox.midtrans.com');
}

function createMidtransToken($order_id, $gross_amount, $customer_details, $item_details) {
    
    // Build params with proper structure
    $params = [
        'transaction_details' => [
            'order_id' => $order_id,
            'gross_amount' => (int)$gross_amount
        ],
        'item_details' => $item_details,
        'enabled_payments' => [
            'credit_card', 'gopay', 'shopeepay', 'other_qris',
            'bca_va', 'bni_va', 'bri_va', 'permata_va', 
            'echannel', 'other_va', 'indomaret', 'alfamart'
        ],
        'credit_card' => [
            'secure' => true,
            'bank' => 'bca',
            'installment' => [
                'required' => false
            ]
        ],
        'callbacks' => [
            'finish' => 'http://localhost/poncolverse/payment_callback.php?status=success'
        ]
    ];
    
    // Add customer details ONCE
    if (!empty($customer_details)) {
        $params['customer_details'] = [
            'first_name' => $customer_details['first_name'] ?? 'Customer',
            'last_name' => $customer_details['last_name'] ?? '',
            'email' => $customer_details['email'] ?? 'customer@example.com',
            'phone' => $customer_details['phone'] ?? '08123456789'
        ];
        
        // Add billing address if needed
        $params['customer_details']['billing_address'] = [
            'first_name' => $customer_details['first_name'] ?? 'Customer',
            'last_name' => $customer_details['last_name'] ?? '',
            'address' => $customer_details['address'] ?? 'Jl. Midtrans No. 1',
            'city' => $customer_details['city'] ?? 'Jakarta',
            'postal_code' => $customer_details['postal_code'] ?? '12345',
            'country_code' => 'IDN'
        ];
    }
    
    // Initialize cURL
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => MIDTRANS_API_URL . '/snap/v1/transactions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_VERBOSE => true
    ]);
    
    // Execute request
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);
    
    // Debug logging
    error_log("=== MIDTRANS API REQUEST ===");
    error_log("URL: " . MIDTRANS_API_URL . '/snap/v1/transactions');
    error_log("Order ID: " . $order_id);
    error_log("Amount: " . $gross_amount);
    error_log("Params: " . json_encode($params));
    error_log("HTTP Code: " . $http_code);
    error_log("Response: " . $response);
    if ($curl_error) {
        error_log("cURL Error: " . $curl_error);
    }
    error_log("=== END ===");
    
    // Handle cURL errors
    if ($curl_error) {
        return [
            'success' => false,
            'message' => 'Connection error: ' . $curl_error,
            'error_type' => 'curl_error'
        ];
    }
    
    // Decode response
    $result = json_decode($response, true);
    
    // Handle success
    if ($http_code == 201 && isset($result['token'])) {
        return [
            'success' => true,
            'token' => $result['token'],
            'redirect_url' => $result['redirect_url'] ?? ''
        ];
    }
    
    // Handle errors
    $error_message = 'Failed to create payment token';
    
    if (isset($result['error_messages']) && is_array($result['error_messages'])) {
        $error_message = implode(', ', $result['error_messages']);
    } elseif (isset($result['message'])) {
        $error_message = $result['message'];
    } elseif (isset($result['error'])) {
        $error_message = $result['error'];
    }
    
    // Add specific error messages based on HTTP code
    switch ($http_code) {
        case 400:
            $error_message = 'Bad Request: ' . $error_message;
            break;
        case 401:
            $error_message = 'Unauthorized: Server Key salah atau tidak valid';
            break;
        case 402:
            $error_message = 'Payment Required: Merchant belum aktif';
            break;
        case 403:
            $error_message = 'Forbidden: Akses ditolak';
            break;
        case 404:
            $error_message = 'Not Found: Endpoint salah atau payment method belum aktif di dashboard';
            break;
        case 500:
            $error_message = 'Server Error: Midtrans server sedang bermasalah';
            break;
        case 503:
            $error_message = 'Service Unavailable: Midtrans maintenance';
            break;
    }
    
    return [
        'success' => false,
        'message' => $error_message,
        'http_code' => $http_code,
        'raw_response' => $response,
        'error_type' => 'api_error'
    ];
}

function verifyMidtransSignature($order_id, $status_code, $gross_amount, $signature_key) {
    $hash = hash('sha512', $order_id . $status_code . $gross_amount . MIDTRANS_SERVER_KEY);
    return $signature_key === $hash;
}

function getMidtransTransactionStatus($order_id) {
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => MIDTRANS_API_URL . '/v2/' . $order_id . '/status',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($http_code == 200) {
        return json_decode($response, true);
    }
    
    return null;
}
?>