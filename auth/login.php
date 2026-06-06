<?php
header('Content-Type: application/json');

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$email = clean_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email dan password harus diisi']);
    exit;
}

// Query optimized dengan index - includes subscription & profile data
$sql = "SELECT u.id, u.firstName, u.lastName, u.email, u.password, u.role, u.status, u.joinDate,
               u.profile_photo, u.subscription_status, u.subscription_end,
               sp.name as subscription_plan_name
        FROM users u
        LEFT JOIN subscription_plans sp ON u.subscription_plan_id = sp.id
        WHERE u.email = ? 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Email tidak terdaftar']);
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    if (md5($password) !== $user['password']) {
        echo json_encode(['success' => false, 'message' => 'Password salah']);
        exit;
    }
}

// Set session (minimal data)
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_firstName'] = $user['firstName'];
$_SESSION['user_lastName'] = $user['lastName'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_status'] = $user['status'];
$_SESSION['user_joinDate'] = date('d/m/Y', strtotime($user['joinDate']));

// Subscription & Profile data
$_SESSION['profile_photo'] = $user['profile_photo'] ?? null;
$_SESSION['subscription_status'] = $user['subscription_status'] ?? 'none';
$_SESSION['subscription_end'] = $user['subscription_end'] ?? null;
$_SESSION['subscription_plan_name'] = $user['subscription_plan_name'] ?? null;

echo json_encode([
    'success' => true,
    'message' => 'Login berhasil',
    'user' => [
        'id' => $user['id'],
        'firstName' => $user['firstName'],
        'lastName' => $user['lastName'],
        'role' => $user['role']
    ]
]);
?>