<?php
/**
 * Midtrans Payment Notification Handler (Webhook)
 * Endpoint ini dipanggil Midtrans secara otomatis saat status payment berubah.
 * Daftarkan URL ini di Midtrans Dashboard > Settings > Configuration > Payment Notification URL
 * URL: http://yourdomain.com/payment_notification.php
 */
require_once 'config/config.php';
require_once 'config/midtrans_config.php';

// Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Baca raw body dari Midtrans
$raw_body = file_get_contents('php://input');
$notification = json_decode($raw_body, true);

if (!$notification || !isset($notification['order_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid notification']);
    exit;
}

$order_id       = $notification['order_id'];
$status_code    = $notification['status_code'];
$gross_amount   = $notification['gross_amount'];
$signature_key  = $notification['signature_key'] ?? '';
$trans_status   = $notification['transaction_status'];
$fraud_status   = $notification['fraud_status'] ?? 'accept';

// Verifikasi signature key dari Midtrans
$expected_sig = hash('sha512', $order_id . $status_code . $gross_amount . MIDTRANS_SERVER_KEY);
if ($signature_key !== $expected_sig) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
    exit;
}

// Ambil data transaction
$sql = "SELECT t.*, sp.name as plan_name, sp.duration_days 
        FROM transactions t 
        JOIN subscription_plans sp ON t.subscription_plan_id = sp.id 
        WHERE t.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (!$transaction) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
    exit;
}

// Tentukan aksi berdasarkan status
$is_success = ($trans_status === 'settlement') || 
              ($trans_status === 'capture' && $fraud_status === 'accept');

if ($is_success && $transaction['transaction_status'] !== 'settlement') {
    // Update status transaksi
    $sql_update = "UPDATE transactions SET transaction_status = 'settlement', updated_at = NOW() WHERE order_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("s", $order_id);
    $stmt_update->execute();

    // Aktifkan subscription user
    $user_id      = $transaction['user_id'];
    $plan_id      = $transaction['subscription_plan_id'];
    $duration     = $transaction['duration_days'];
    $start_date   = date('Y-m-d');
    $end_date     = date('Y-m-d', strtotime("+{$duration} days"));

    $sql_user = "UPDATE users 
                 SET subscription_plan_id = ?, 
                     subscription_start = ?, 
                     subscription_end = ?, 
                     subscription_status = 'active' 
                 WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("issi", $plan_id, $start_date, $end_date, $user_id);
    $stmt_user->execute();

    error_log("[Midtrans Webhook] Subscription activated for user_id={$user_id}, order_id={$order_id}, until={$end_date}");

} elseif (in_array($trans_status, ['expire', 'cancel', 'deny', 'failure'])) {
    // Tandai transaksi gagal
    if (!in_array($transaction['transaction_status'], ['settlement', 'capture'])) {
        $sql_fail = "UPDATE transactions SET transaction_status = ?, updated_at = NOW() WHERE order_id = ?";
        $stmt_fail = $conn->prepare($sql_fail);
        $stmt_fail->bind_param("ss", $trans_status, $order_id);
        $stmt_fail->execute();
    }
    error_log("[Midtrans Webhook] Transaction {$trans_status} for order_id={$order_id}");

} elseif ($trans_status === 'pending') {
    $sql_pend = "UPDATE transactions SET transaction_status = 'pending', updated_at = NOW() WHERE order_id = ? AND transaction_status = 'pending'";
    $stmt_pend = $conn->prepare($sql_pend);
    $stmt_pend->bind_param("s", $order_id);
    $stmt_pend->execute();
}

http_response_code(200);
echo json_encode(['status' => 'ok']);
?>
