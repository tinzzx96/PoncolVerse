<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── GET: semua film + status featured ──
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_all') {
    $sql = "SELECT m.id, m.title, m.rating, m.poster, m.year,
                   IF(f.movie_id IS NOT NULL, 1, 0) as is_featured,
                   f.sort_order
            FROM movies m
            LEFT JOIN featured_movies f ON m.id = f.movie_id
            ORDER BY is_featured DESC, m.rating DESC";
    $result = $conn->query($sql);
    $movies = [];
    while ($row = $result->fetch_assoc()) {
        $movies[] = [
            'id'          => (int)$row['id'],
            'title'       => $row['title'],
            'rating'      => (float)$row['rating'],
            'poster'      => $row['poster'],
            'year'        => $row['year'],
            'is_featured' => (int)$row['is_featured'],
            'sort_order'  => (int)($row['sort_order'] ?? 0),
        ];
    }
    echo json_encode(['success' => true, 'movies' => $movies]);
    exit;
}

// ── POST: add featured ──
if ($action === 'add') {
    $movie_id = intval($_POST['movie_id'] ?? 0);
    if (!$movie_id) {
        echo json_encode(['success' => false, 'message' => 'Movie ID invalid']);
        exit;
    }

    // Cek limit 20
    $count = $conn->query("SELECT COUNT(*) as c FROM featured_movies")->fetch_assoc()['c'];
    if ($count >= 20) {
        echo json_encode(['success' => false, 'message' => 'Maksimal 20 film populer']);
        exit;
    }

    $max_order = $conn->query("SELECT COALESCE(MAX(sort_order),0)+1 as n FROM featured_movies")->fetch_assoc()['n'];
    $stmt = $conn->prepare("INSERT IGNORE INTO featured_movies (movie_id, sort_order) VALUES (?, ?)");
    $stmt->bind_param("ii", $movie_id, $max_order);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Film ditambahkan ke Film Populer']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Film sudah ada di Film Populer']);
    }
    exit;
}

// ── POST: remove featured ──
if ($action === 'remove') {
    $movie_id = intval($_POST['movie_id'] ?? 0);
    if (!$movie_id) {
        echo json_encode(['success' => false, 'message' => 'Movie ID invalid']);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM featured_movies WHERE movie_id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Film dihapus dari Film Populer']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Film tidak ditemukan']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action tidak dikenal']);
?>