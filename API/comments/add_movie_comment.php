<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Anda harus login terlebih dahulu'
    ]);
    exit;
}

// Validate input
if (!isset($_POST['movie_id']) || !isset($_POST['comment'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap'
    ]);
    exit;
}

$movie_id   = intval($_POST['movie_id']);
$user_id    = $_SESSION['user_id'];
$comment    = clean_input($_POST['comment']);
$rating     = isset($_POST['rating']) && !empty($_POST['rating']) ? floatval($_POST['rating']) : null;
$is_spoiler = isset($_POST['is_spoiler']) && $_POST['is_spoiler'] == '1' ? 1 : 0;

// Get user name
$user_name = $_SESSION['user_firstName'] . ' ' . $_SESSION['user_lastName'];

// Insert comment
try {
    $sql = "INSERT INTO movie_comments (movie_id, user_id, user_name, comment, rating, is_spoiler, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissdi", $movie_id, $user_id, $user_name, $comment, $rating, $is_spoiler);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success'    => true,
            'message'    => 'Komentar berhasil ditambahkan',
            'comment_id' => $conn->insert_id
        ]);
    } else {
        throw new Exception('Failed to insert comment');
    }
    
} catch (Exception $e) {
    error_log("Error adding comment: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan komentar. Silakan coba lagi.'
    ]);
}
?>