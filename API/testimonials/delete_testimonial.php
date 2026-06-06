<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

if (!isset($_POST['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID tidak ditemukan'
    ]);
    exit;
}

$id = intval($_POST['id']);

try {
    $sql = "DELETE FROM website_testimonials WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Testimonial berhasil dihapus dari database'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Testimonial tidak ditemukan'
            ]);
        }
    } else {
        throw new Exception('Failed to execute: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log("Error in delete_testimonial.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>