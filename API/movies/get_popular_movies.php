<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

try {
    // Ambil 14 film POPULER (rating tertinggi + yang paling banyak di-watchlist)
    // FILM POPULER = Rating tinggi DAN banyak yang nonton
    $sql = "SELECT m.*, 
            (SELECT COUNT(*) FROM watchlist w WHERE w.movie_id = m.id) as watchlist_count
            FROM movies m 
            WHERE m.rating >= 7.0
            ORDER BY m.rating DESC, watchlist_count DESC, m.created_at DESC 
            LIMIT 14";
    
    $result = $conn->query($sql);
    
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
                'plot' => $row['plot'] ?? ''
            ];
        }
    }
    
    echo json_encode($movies, JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    error_log("Error in get_popular_movies.php: " . $e->getMessage());
    echo json_encode([]);
}
?>