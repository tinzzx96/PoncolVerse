<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

try {
    $sql = "SELECT m.id, m.title, m.rating, m.poster, m.trailer,
                   m.watchLink, m.year, m.duration, m.genre,
                   m.director, m.plot
            FROM featured_movies f
            INNER JOIN movies m ON f.movie_id = m.id
            ORDER BY f.sort_order ASC, f.added_at ASC";

    $result = $conn->query($sql);
    $movies = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $genreArray = json_decode($row['genre'], true);
            if (!is_array($genreArray)) {
                $genreArray = array_map('trim', explode(',', $row['genre']));
            }
            $movies[] = [
                'id'        => (int)$row['id'],
                'title'     => $row['title'],
                'rating'    => (float)$row['rating'],
                'poster'    => $row['poster'],
                'trailer'   => $row['trailer'],
                'watchLink' => $row['watchLink'] ?? '',
                'year'      => $row['year'],
                'duration'  => $row['duration'],
                'genre'     => $genreArray,
                'director'  => $row['director'] ?? '',
                'plot'      => $row['plot'] ?? '',
            ];
        }
    }

    echo json_encode($movies, JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    error_log("Error in get_featured_movies.php: " . $e->getMessage());
    echo json_encode([]);
}
?>