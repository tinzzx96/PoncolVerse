<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = [];

// ===== TOTAL STATS =====
$data['total_movies']       = $conn->query("SELECT COUNT(*) as c FROM movies")->fetch_assoc()['c'];
$data['total_users']        = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'user'")->fetch_assoc()['c'];
$data['total_transactions'] = $conn->query("SELECT COUNT(*) as c FROM transactions WHERE transaction_status = 'settlement'")->fetch_assoc()['c'];
$data['total_revenue']      = (float)$conn->query("SELECT COALESCE(SUM(gross_amount),0) as s FROM transactions WHERE transaction_status = 'settlement'")->fetch_assoc()['s'];
$data['active_subscribers'] = $conn->query("SELECT COUNT(*) as c FROM users WHERE subscription_status = 'active'")->fetch_assoc()['c'];
$data['total_watchlist']    = $conn->query("SELECT COUNT(*) as c FROM watchlist")->fetch_assoc()['c'];
$data['total_comments']     = $conn->query("SELECT COUNT(*) as c FROM movie_comments")->fetch_assoc()['c'];
$data['pending_testimonials'] = $conn->query("SELECT COUNT(*) as c FROM website_testimonials WHERE is_approved = 0")->fetch_assoc()['c'];

// ===== NEW USERS (last 7 days) =====
$data['new_users_7d'] = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['c'];

// ===== REVENUE LAST 6 MONTHS =====
$revenue_chart = [];
for ($i = 5; $i >= 0; $i--) {
    $month_label = date('M Y', strtotime("-{$i} months"));
    $month_start = date('Y-m-01', strtotime("-{$i} months"));
    $month_end   = date('Y-m-t', strtotime("-{$i} months"));
    $rev = $conn->query("SELECT COALESCE(SUM(gross_amount),0) as s FROM transactions WHERE transaction_status='settlement' AND created_at BETWEEN '{$month_start}' AND '{$month_end} 23:59:59'")->fetch_assoc()['s'];
    $revenue_chart[] = ['month' => $month_label, 'revenue' => (float)$rev];
}
$data['revenue_chart'] = $revenue_chart;

// ===== TOP 5 MOST WATCHLISTED MOVIES =====
$top_movies_res = $conn->query(
    "SELECT m.title, m.poster, m.rating, COUNT(w.id) as count
     FROM watchlist w
     JOIN movies m ON w.movie_id = m.id
     GROUP BY w.movie_id
     ORDER BY count DESC
     LIMIT 5"
);
$data['top_watchlisted'] = [];
while ($row = $top_movies_res->fetch_assoc()) {
    $data['top_watchlisted'][] = $row;
}

// ===== RECENT TRANSACTIONS (5 latest) =====
$txn_res = $conn->query(
    "SELECT t.order_id, t.gross_amount, t.created_at,
            u.firstName, u.lastName,
            sp.name as plan_name
     FROM transactions t
     JOIN users u ON t.user_id = u.id
     JOIN subscription_plans sp ON t.subscription_plan_id = sp.id
     WHERE t.transaction_status = 'settlement'
     ORDER BY t.created_at DESC
     LIMIT 5"
);
$data['recent_transactions'] = [];
while ($row = $txn_res->fetch_assoc()) {
    $data['recent_transactions'][] = $row;
}

// ===== SUBSCRIPTION PLAN DISTRIBUTION =====
$plan_dist_res = $conn->query(
    "SELECT sp.name, COUNT(u.id) as count
     FROM users u
     JOIN subscription_plans sp ON u.subscription_plan_id = sp.id
     WHERE u.subscription_status = 'active'
     GROUP BY u.subscription_plan_id"
);
$data['plan_distribution'] = [];
while ($row = $plan_dist_res->fetch_assoc()) {
    $data['plan_distribution'][] = $row;
}

// ===== USERS EXPIRING SOON (next 7 days) =====
$data['expiring_soon'] = $conn->query(
    "SELECT COUNT(*) as c FROM users 
     WHERE subscription_status = 'active' 
     AND subscription_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
)->fetch_assoc()['c'];

echo json_encode($data, JSON_UNESCAPED_SLASHES);
?>