<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get all movies in user's watchlist
    $sql = "SELECT m.*, w.added_at 
            FROM watchlist w
            INNER JOIN movies m ON w.movie_id = m.id
            WHERE w.user_id = ?
            ORDER BY w.added_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $movies = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Parse genre
            $genreArray = json_decode($row['genre'], true);
            if (!is_array($genreArray)) {
                $genreArray = array_map('trim', explode(',', $row['genre']));
            }
            
            $movies[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'rating' => (float)$row['rating'],
                'poster' => $row['poster'],
                'trailer' => $row['trailer'],
                'watchLink' => $row['watchLink'] ?? '',
                'year' => $row['year'],
                'duration' => $row['duration'],
                'genre' => $genreArray,
                'director' => $row['director'] ?? '',
                'plot' => $row['plot'] ?? '',
                'added_at' => $row['added_at']
            ];
        }
    }
    
    echo json_encode($movies, JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    error_log("Error in get_watchlist.php: " . $e->getMessage());
    echo json_encode([]);
}
?>