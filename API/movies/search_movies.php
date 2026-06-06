<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

// Get search query
$query = isset($_GET['query']) ? clean_input($_GET['query']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

try {
    // Search di database (title, director, plot)
    $searchTerm = "%{$query}%";
    $sql = "SELECT * FROM movies 
            WHERE title LIKE ? 
            OR director LIKE ? 
            OR plot LIKE ?
            ORDER BY rating DESC, created_at DESC 
            LIMIT 30";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
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
                'plot' => $row['plot'] ?? ''
            ];
        }
    }
    
    echo json_encode($movies, JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    error_log("Error in search_movies.php: " . $e->getMessage());
    echo json_encode([]);
}
?>