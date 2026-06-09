<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $sql = "SELECT m.id, m.title, m.rating, m.poster, m.year, m.duration, m.genre,
                   wh.watched_at
            FROM watch_history wh
            INNER JOIN movies m ON wh.movie_id = m.id
            WHERE wh.user_id = ?
            ORDER BY wh.watched_at DESC
            LIMIT 20";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];
    while ($row = $result->fetch_assoc()) {
        $genreArray = json_decode($row['genre'], true);
        if (!is_array($genreArray)) {
            $genreArray = array_map('trim', explode(',', $row['genre']));
        }

        $history[] = [
            'id'         => (int)$row['id'],
            'title'      => $row['title'],
            'rating'     => (float)$row['rating'],
            'poster'     => $row['poster'],
            'year'       => $row['year'],
            'duration'   => $row['duration'],
            'genre'      => $genreArray,
            'watched_at' => $row['watched_at'],
        ];
    }

    echo json_encode($history, JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    error_log("Error in get_watch_history.php: " . $e->getMessage());
    echo json_encode([]);
}
?>