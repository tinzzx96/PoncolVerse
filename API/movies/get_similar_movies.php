<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

$movie_id = isset($_GET['movie_id']) ? intval($_GET['movie_id']) : 0;
$genre    = isset($_GET['genre']) ? $_GET['genre'] : '';

if (!$movie_id || !$genre) {
    echo json_encode([]);
    exit;
}

try {
    // Decode genre string ke array
    $genres = json_decode($genre, true);
    if (!is_array($genres)) {
        $genres = array_map('trim', explode(',', $genre));
    }

    if (empty($genres)) {
        echo json_encode([]);
        exit;
    }

    // Buat LIKE conditions untuk setiap genre
    $conditions = [];
    $params     = [];
    $types      = '';

    foreach ($genres as $g) {
        $conditions[] = "genre LIKE ?";
        $params[]     = '%' . $g . '%';
        $types       .= 's';
    }

    // Exclude film saat ini, ambil max 6
    $where   = implode(' OR ', $conditions);
    $params[] = $movie_id;
    $types   .= 'i';

    $sql = "SELECT id, title, rating, poster, year, duration, genre
            FROM movies
            WHERE ({$where}) AND id != ?
            ORDER BY rating DESC
            LIMIT 6";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $movies = [];
    while ($row = $result->fetch_assoc()) {
        $genreArray = json_decode($row['genre'], true);
        if (!is_array($genreArray)) {
            $genreArray = array_map('trim', explode(',', $row['genre']));
        }

        $movies[] = [
            'id'     => (int)$row['id'],
            'title'  => $row['title'],
            'rating' => (float)$row['rating'],
            'poster' => $row['poster'],
            'year'   => $row['year'],
            'genre'  => $genreArray,
        ];
    }

    echo json_encode($movies, JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    error_log("Error in get_similar_movies.php: " . $e->getMessage());
    echo json_encode([]);
}
?>