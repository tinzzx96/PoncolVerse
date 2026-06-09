<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

// Hanya user login yang dicatat
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if (!isset($_POST['movie_id'])) {
    echo json_encode(['success' => false, 'message' => 'Movie ID missing']);
    exit;
}

$user_id  = $_SESSION['user_id'];
$movie_id = intval($_POST['movie_id']);

try {
    // INSERT OR UPDATE watched_at jika sudah pernah nonton
    $sql = "INSERT INTO watch_history (user_id, movie_id, watched_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE watched_at = NOW()";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $movie_id);
    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Error in record_watch.php: " . $e->getMessage());
    echo json_encode(['success' => false]);
}
?>