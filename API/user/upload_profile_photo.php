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

// Check if file uploaded
if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'message' => 'File tidak valid atau gagal diupload'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$file = $_FILES['profile_photo'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode([
        'success' => false,
        'message' => 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP'
    ]);
    exit;
}

// Validate file size (max 2MB)
if ($file['size'] > 2097152) {
    echo json_encode([
        'success' => false,
        'message' => 'Ukuran file terlalu besar. Maksimal 2MB'
    ]);
    exit;
}

try {
    // Create profiles directory if not exists
    $upload_dir = '../../uploads/profiles/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
    $target_path = $upload_dir . $filename;
    
    // Get old photo to delete
    $old_photo_sql = "SELECT profile_photo FROM users WHERE id = ?";
    $old_stmt = $conn->prepare($old_photo_sql);
    $old_stmt->bind_param("i", $user_id);
    $old_stmt->execute();
    $old_result = $old_stmt->get_result();
    $old_photo = $old_result->fetch_assoc()['profile_photo'];
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Update database
        $relative_path = 'uploads/profiles/' . $filename;
        $sql = "UPDATE users SET profile_photo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $relative_path, $user_id);
        
        if ($stmt->execute()) {
            // Delete old photo if exists
            if ($old_photo && file_exists('../../' . $old_photo)) {
                unlink('../../' . $old_photo);
            }
            
            // Update session
            $_SESSION['user_profile_photo'] = $relative_path;
            
            echo json_encode([
                'success' => true,
                'message' => 'Foto profil berhasil diupdate!',
                'photo_url' => $relative_path
            ]);
        } else {
            throw new Exception('Gagal menyimpan ke database');
        }
    } else {
        throw new Exception('Gagal mengupload file');
    }
    
} catch (Exception $e) {
    error_log("Error in upload_profile_photo.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>