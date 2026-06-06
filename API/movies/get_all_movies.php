<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

try {
    // Ambil SEMUA film (newest first, exclude yang sudah ada di popular)
    // Semua film = Latest upload, semua rating
    $sql = "SELECT * FROM movies 
            ORDER BY created_at DESC, id DESC 
            LIMIT 50";
    
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
    error_log("Error in get_all_movies.php: " . $e->getMessage());
    echo json_encode([]);
}
?>