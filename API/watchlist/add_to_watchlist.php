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

// Get movie_id
if (!isset($_POST['movie_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Movie ID tidak ditemukan'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$movie_id = intval($_POST['movie_id']);

try {
    // Check if already in watchlist
    $check_sql = "SELECT id FROM watchlist WHERE user_id = ? AND movie_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $movie_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Already in watchlist, remove it (toggle)
        $delete_sql = "DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $user_id, $movie_id);
        
        if ($delete_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'action' => 'removed',
                'message' => 'Film dihapus dari watchlist'
            ]);
        } else {
            throw new Exception('Gagal menghapus dari watchlist');
        }
    } else {
        // Not in watchlist, add it
        $insert_sql = "INSERT INTO watchlist (user_id, movie_id, added_at) VALUES (?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $user_id, $movie_id);
        
        if ($insert_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'action' => 'added',
                'message' => 'Film ditambahkan ke watchlist'
            ]);
        } else {
            throw new Exception('Gagal menambahkan ke watchlist');
        }
    }
    
} catch (Exception $e) {
    error_log("Error in add_to_watchlist.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan. Silakan coba lagi.'
    ]);
}
?>