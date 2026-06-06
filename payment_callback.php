<?php
require_once 'config/config.php';
require_once 'config/midtrans_config.php';

// Midtrans bisa kirim order_id via GET atau POST
$order_id = $_GET['order_id'] ?? $_POST['order_id'] ?? null;

// Jika tidak ada order_id, coba ambil dari transaction_id
if (!$order_id) {
    $order_id = $_GET['transaction_id'] ?? $_POST['transaction_id'] ?? null;
}

if (!$order_id) {
    header('Location: index.php');
    exit;
}

// Ambil data transaction dari DB
$sql = "SELECT t.*, sp.name as plan_name, sp.duration_days 
        FROM transactions t 
        JOIN subscription_plans sp ON t.subscription_plan_id = sp.id 
        WHERE t.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$transaction = $result->fetch_assoc();

if (!$transaction) {
    header('Location: index.php');
    exit;
}

// Verifikasi status ke Midtrans API (lebih reliable dari GET param)
$midtrans_status = getMidtransTransactionStatus($order_id);
$transaction_status = $midtrans_status['transaction_status'] ?? 'unknown';
$fraud_status = $midtrans_status['fraud_status'] ?? 'accept';

// Tentukan apakah payment berhasil
$is_success = ($transaction_status === 'settlement') || 
              ($transaction_status === 'capture' && $fraud_status === 'accept') ||
              ($transaction_status === 'capture' && $fraud_status === 'challenge');

// Fallback: jika Midtrans API tidak bisa diakses, percaya GET param
if (!$midtrans_status) {
    $status_param = $_GET['status'] ?? 'unknown';
    $is_success = ($status_param === 'success');
    $transaction_status = $is_success ? 'settlement' : $status_param;
}

$start_date = '';
$end_date = '';

if ($is_success && $transaction['transaction_status'] !== 'settlement') {
    // Update transaction status
    $sql_update = "UPDATE transactions SET transaction_status = 'settlement', updated_at = NOW() WHERE order_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("s", $order_id);
    $stmt_update->execute();
    
    // Activate subscription
    $user_id = $transaction['user_id'];
    $plan_id = $transaction['subscription_plan_id'];
    $duration_days = $transaction['duration_days'];
    
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime("+{$duration_days} days"));
    
    $sql_user = "UPDATE users 
                 SET subscription_plan_id = ?, 
                     subscription_start = ?, 
                     subscription_end = ?, 
                     subscription_status = 'active' 
                 WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("issi", $plan_id, $start_date, $end_date, $user_id);
    $stmt_user->execute();
    
    // Update session supaya tampil langsung tanpa re-login
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
        $_SESSION['subscription_status'] = 'active';
        $_SESSION['subscription_end'] = $end_date;
        $_SESSION['subscription_plan_name'] = $transaction['plan_name'];
    }
    
    $message = 'Pembayaran berhasil! Subscription Anda sudah aktif.';
    $status_icon = 'fa-check-circle';
    $status_color = '#00ff88';

} elseif ($is_success && $transaction['transaction_status'] === 'settlement') {
    // Already processed, just read existing data
    $end_date = $transaction['subscription_end'] ?? '';
    
    // Re-fetch from DB for accurate subscription_end
    $sql_re = "SELECT subscription_end, subscription_status, subscription_plan_id FROM users WHERE id = ?";
    $stmt_re = $conn->prepare($sql_re);
    $stmt_re->bind_param("i", $transaction['user_id']);
    $stmt_re->execute();
    $user_data = $stmt_re->get_result()->fetch_assoc();
    $end_date = $user_data['subscription_end'] ?? $end_date;
    
    // Refresh session
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $transaction['user_id']) {
        $_SESSION['subscription_status'] = 'active';
        $_SESSION['subscription_end'] = $end_date;
        $_SESSION['subscription_plan_name'] = $transaction['plan_name'];
    }
    
    $message = 'Pembayaran berhasil! Subscription Anda sudah aktif.';
    $status_icon = 'fa-check-circle';
    $status_color = '#00ff88';

} elseif ($transaction_status === 'pending') {
    $message = 'Pembayaran Anda sedang diproses. Silakan selesaikan pembayaran.';
    $status_icon = 'fa-clock';
    $status_color = '#ff9800';
} else {
    $message = 'Pembayaran gagal atau dibatalkan. Silakan coba lagi.';
    $status_icon = 'fa-times-circle';
    $status_color = '#ff003c';
    
    // Update transaction status jika gagal
    if (!in_array($transaction['transaction_status'], ['settlement', 'capture'])) {
        $fail_status = $transaction_status === 'expire' ? 'expire' : 'failure';
        $sql_fail = "UPDATE transactions SET transaction_status = ?, updated_at = NOW() WHERE order_id = ?";
        $stmt_fail = $conn->prepare($sql_fail);
        $stmt_fail->bind_param("ss", $fail_status, $order_id);
        $stmt_fail->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Status Pembayaran - PoncolVerse</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0a0a0a 0%, #1a0a0f 100%);
      color: #fff;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
    }
    .result-container {
      background: linear-gradient(145deg, #1a1a1a, #0f0f0f);
      border: 2px solid rgba(255, 0, 60, 0.3);
      border-radius: 20px;
      padding: 3rem;
      max-width: 500px;
      width: 100%;
      text-align: center;
    }
    .status-icon {
      width: 100px;
      height: 100px;
      background: rgba(255, 0, 60, 0.1);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 2rem;
      font-size: 3rem;
      color: <?php echo $status_color; ?>;
    }
    h1 {
      font-family: 'Orbitron', sans-serif;
      font-size: 2rem;
      margin-bottom: 1rem;
    }
    .message {
      font-size: 1.1rem;
      color: #ddd;
      margin: 2rem 0;
      line-height: 1.6;
    }
    .order-details {
      background: rgba(255, 0, 60, 0.05);
      border: 1px solid rgba(255, 0, 60, 0.2);
      border-radius: 15px;
      padding: 1.5rem;
      margin: 2rem 0;
      text-align: left;
    }
    .detail-row {
      display: flex;
      justify-content: space-between;
      padding: 0.5rem 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { color: #aaa; }
    .detail-value { font-weight: 600; }
    .back-btn {
      display: inline-block;
      padding: 1rem 2rem;
      background: linear-gradient(135deg, #ff003c, #ff4d7a);
      border: none;
      border-radius: 50px;
      color: white;
      font-weight: 700;
      text-decoration: none;
      transition: all 0.3s;
      margin-top: 2rem;
    }
    .back-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(255, 0, 60, 0.5);
    }
  </style>
</head>
<body>
  <div class="result-container">
    <div class="status-icon">
      <i class="fas <?php echo $status_icon; ?>"></i>
    </div>
    
    <h1><?php echo $status === 'success' ? 'Pembayaran Berhasil!' : ($status === 'pending' ? 'Menunggu Pembayaran' : 'Pembayaran Gagal'); ?></h1>
    
    <p class="message"><?php echo $message; ?></p>
    
    <div class="order-details">
      <div class="detail-row">
        <span class="detail-label">Order ID:</span>
        <span class="detail-value"><?php echo $order_id; ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Paket:</span>
        <span class="detail-value"><?php echo $transaction['plan_name']; ?></span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Total:</span>
        <span class="detail-value">Rp<?php echo number_format($transaction['gross_amount'], 0, ',', '.'); ?></span>
      </div>
      <?php if ($status === 'success'): ?>
      <div class="detail-row">
        <span class="detail-label">Berlaku Hingga:</span>
        <span class="detail-value"><?php echo date('d M Y', strtotime($end_date)); ?></span>
      </div>
      <?php endif; ?>
    </div>
    
    <a href="index.php" class="back-btn">
      <i class="fas fa-home"></i> Kembali ke Beranda
    </a>
  </div>
</body>
</html>