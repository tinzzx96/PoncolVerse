<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

// Cek apakah user adalah admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $title = clean_input($_POST['title']);
    $rating = floatval($_POST['rating']);
    $year = clean_input($_POST['year']);
    $duration = clean_input($_POST['duration']);
    $trailer = clean_input($_POST['trailer']);
    $watchLink = isset($_POST['watchLink']) ? clean_input($_POST['watchLink']) : null;
    $genre = isset($_POST['genre']) ? json_encode($_POST['genre']) : json_encode([]);
    $director = isset($_POST['director']) ? clean_input($_POST['director']) : '';
    $plot = isset($_POST['plot']) ? clean_input($_POST['plot']) : '';
    
    // Validasi input
    if (empty($id) || empty($title) || empty($rating) || empty($year) || empty($duration) || empty($trailer)) {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib harus diisi']);
        exit;
    }
    
    // Ambil poster lama dari database
    $sqlGet = "SELECT poster FROM movies WHERE id = ?";
    $stmtGet = $conn->prepare($sqlGet);
    $stmtGet->bind_param("i", $id);
    $stmtGet->execute();
    $resultGet = $stmtGet->get_result();
    $oldMovie = $resultGet->fetch_assoc();
    $posterPath = $oldMovie['poster']; // Default: pakai poster lama
    
    // Upload poster baru jika ada
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $posterUpload = uploadFile($_FILES['poster'], 'poster');
        if ($posterUpload['success']) {
            // Hapus poster lama
            if (!empty($oldMovie['poster']) && file_exists('../../' . $oldMovie['poster'])) {
                unlink('../../' . $oldMovie['poster']);
            }
            $posterPath = $posterUpload['path'];
        } else {
            echo json_encode(['success' => false, 'message' => 'Upload poster gagal: ' . $posterUpload['message']]);
            exit;
        }
    }
    
    // Update film di database
    $sql = "UPDATE movies SET title=?, rating=?, poster=?, trailer=?, watchLink=?, year=?, duration=?, genre=?, director=?, plot=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare statement gagal: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("sdssssssssi", $title, $rating, $posterPath, $trailer, $watchLink, $year, $duration, $genre, $director, $plot, $id);
    
    if ($stmt->execute()) {
        // Hapus cast members lama
        $sqlDeleteCast = "DELETE FROM cast_members WHERE movie_id = ?";
        $stmtDeleteCast = $conn->prepare($sqlDeleteCast);
        if ($stmtDeleteCast) {
            $stmtDeleteCast->bind_param("i", $id);
            $stmtDeleteCast->execute();
            $stmtDeleteCast->close();
        }
        
        // Insert cast members baru/diupdate jika ada
        if (isset($_POST['actor_names']) && is_array($_POST['actor_names'])) {
            $actorNames = $_POST['actor_names'];
            $characterNames = isset($_POST['character_names']) ? $_POST['character_names'] : [];
            $existingPhotos = isset($_POST['existing_actor_photos']) ? $_POST['existing_actor_photos'] : [];
            
            foreach ($actorNames as $index => $actorName) {
                if (!empty($actorName)) {
                    $characterName = isset($characterNames[$index]) ? clean_input($characterNames[$index]) : '';
                    $actorPhotoPath = isset($existingPhotos[$index]) ? clean_input($existingPhotos[$index]) : '';
                    
                    // Prepare file upload jika ada foto baru yang diupload
                    if (isset($_FILES['actor_photos']['name'][$index]) && $_FILES['actor_photos']['error'][$index] === UPLOAD_ERR_OK) {
                        $actorFile = [
                            'name' => $_FILES['actor_photos']['name'][$index],
                            'type' => $_FILES['actor_photos']['type'][$index],
                            'tmp_name' => $_FILES['actor_photos']['tmp_name'][$index],
                            'error' => $_FILES['actor_photos']['error'][$index],
                            'size' => $_FILES['actor_photos']['size'][$index]
                        ];
                        
                        $actorUpload = uploadFile($actorFile, 'actor');
                        if ($actorUpload['success']) {
                            $actorPhotoPath = $actorUpload['path'];
                        }
                    }
                    
                    $actorNameClean = clean_input($actorName);
                    $sqlCast = "INSERT INTO cast_members (movie_id, actor_name, actor_photo, character_name) VALUES (?, ?, ?, ?)";
                    $stmtCast = $conn->prepare($sqlCast);
                    if ($stmtCast) {
                        $stmtCast->bind_param("isss", $id, $actorNameClean, $actorPhotoPath, $characterName);
                        $stmtCast->execute();
                        $stmtCast->close();
                    }
                }
            }
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Film berhasil diupdate'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate film: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>