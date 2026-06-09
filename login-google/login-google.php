<?php
require_once '../config/env_loader.php';

$client_id = env('GOOGLE_CLIENT_ID');
$redirect_uri = 'http://localhost/poncolverse/login-google/callback.php';

$params = http_build_query([
    'client_id'     => $client_id,
    'redirect_uri'  => $redirect_uri,
    'response_type' => 'code',
    'scope'         => 'email profile',
    'access_type'   => 'online',
    'prompt'        => 'select_account',
]);

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
exit;