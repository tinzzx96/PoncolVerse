<?php
require_once '../config/config.php';
require_once '../config/env_loader.php';

$client_id     = env('GOOGLE_CLIENT_ID');
$client_secret = env('GOOGLE_CLIENT_SECRET');
$redirect_uri  = 'http://localhost/poncolverse/login-google/callback.php';

// Jika ada error dari Google
if (isset($_GET['error'])) {
    header('Location: ../index.php?google_error=access_denied');
    exit;
}

// Harus ada code
if (!isset($_GET['code'])) {
    header('Location: ../index.php');
    exit;
}

$code = $_GET['code'];

// ===== STEP 1: Tukar code dengan access token =====
$token_url  = 'https://oauth2.googleapis.com/token';
$token_data = [
    'code'          => $code,
    'client_id'     => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri'  => $redirect_uri,
    'grant_type'    => 'authorization_code',
];

$ch = curl_init($token_url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($token_data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
]);
$token_response = curl_exec($ch);
$curl_error     = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    error_log('Google OAuth cURL error: ' . $curl_error);
    header('Location: ../index.php?google_error=connection_failed');
    exit;
}

$token_json = json_decode($token_response, true);
if (!isset($token_json['access_token'])) {
    error_log('Google OAuth token error: ' . $token_response);
    header('Location: ../index.php?google_error=token_failed');
    exit;
}

$access_token = $token_json['access_token'];

// ===== STEP 2: Ambil data user dari Google =====
$userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
$ch = curl_init($userinfo_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $access_token],
]);
$userinfo_response = curl_exec($ch);
curl_close($ch);

$google_user = json_decode($userinfo_response, true);

if (!isset($google_user['email'])) {
    header('Location: ../index.php?google_error=userinfo_failed');
    exit;
}

$google_email   = $google_user['email'];
$google_name    = $google_user['name'] ?? '';
$google_picture = $google_user['picture'] ?? '';
$google_id      = $google_user['id'] ?? '';

// Pisahkan nama depan & belakang
$name_parts = explode(' ', trim($google_name), 2);
$firstName  = $name_parts[0] ?? 'Google';
$lastName   = $name_parts[1] ?? 'User';

// ===== STEP 3: Cek user di database =====
$sql  = "SELECT u.id, u.firstName, u.lastName, u.email, u.role, u.status, u.joinDate,
                u.profile_photo, u.subscription_status, u.subscription_end,
                sp.name as subscription_plan_name
         FROM users u
         LEFT JOIN subscription_plans sp ON u.subscription_plan_id = sp.id
         WHERE u.email = ?
         LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $google_email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user) {
    // ===== USER SUDAH ADA: langsung login =====
    $_SESSION['user_id']        = $user['id'];
    $_SESSION['user_email']     = $user['email'];
    $_SESSION['user_firstName'] = $user['firstName'];
    $_SESSION['user_lastName']  = $user['lastName'];
    $_SESSION['user_role']      = $user['role'];
    $_SESSION['user_status']    = $user['status'];
    $_SESSION['user_joinDate']  = date('d/m/Y', strtotime($user['joinDate']));

    $_SESSION['profile_photo']        = $user['profile_photo'] ?? $google_picture;
    $_SESSION['subscription_status']  = $user['subscription_status'] ?? 'none';
    $_SESSION['subscription_end']     = $user['subscription_end'] ?? null;
    $_SESSION['subscription_plan_name'] = $user['subscription_plan_name'] ?? null;

} else {
    // ===== USER BARU: daftarkan otomatis =====

    // Cek apakah email sudah ada (double check)
    $joinDate        = date('Y-m-d');
    $dummyPassword   = password_hash('google_oauth_' . $google_id . '_' . time(), PASSWORD_DEFAULT);
    $recoveryEmail   = $google_email; // pakai email yang sama sebagai recovery

    $sql_insert = "INSERT INTO users 
                   (firstName, lastName, email, password, recoveryEmail, role, status, joinDate, profile_photo)
                   VALUES (?, ?, ?, ?, ?, 'user', 'Penonton', ?, ?)";
    $stmt_ins = $conn->prepare($sql_insert);
    $stmt_ins->bind_param(
        'sssssss',
        $firstName,
        $lastName,
        $google_email,
        $dummyPassword,
        $recoveryEmail,
        $joinDate,
        $google_picture
    );

    if (!$stmt_ins->execute()) {
        error_log('Google OAuth register error: ' . $conn->error);
        header('Location: ../index.php?google_error=register_failed');
        exit;
    }

    $new_id = $conn->insert_id;

    $_SESSION['user_id']        = $new_id;
    $_SESSION['user_email']     = $google_email;
    $_SESSION['user_firstName'] = $firstName;
    $_SESSION['user_lastName']  = $lastName;
    $_SESSION['user_role']      = 'user';
    $_SESSION['user_status']    = 'Penonton';
    $_SESSION['user_joinDate']  = date('d/m/Y');

    $_SESSION['profile_photo']          = $google_picture;
    $_SESSION['subscription_status']    = 'none';
    $_SESSION['subscription_end']       = null;
    $_SESSION['subscription_plan_name'] = null;
}

// Redirect ke homepage
header('Location: ../index.php?google_login=success');
exit;