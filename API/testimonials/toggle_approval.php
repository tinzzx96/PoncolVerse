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

if (!isset($_POST['id']) || !isset($_POST['is_approved'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap'
    ]);
    exit;
}

$id = intval($_POST['id']);
$is_approved = intval($_POST['is_approved']);

try {
    // Check if trying to approve and already have 10 approved
    if ($is_approved == 1) {
        $count_sql = "SELECT COUNT(*) as count FROM website_testimonials WHERE is_approved = 1";
        $count_result = $conn->query($count_sql);
        if (!$count_result) {
            throw new Exception('Database query failed');
        }
        $count = $count_result->fetch_assoc()['count'];
        
        if ($count >= 10) {
            echo json_encode([
                'success' => false,
                'message' => 'Maksimum 10 testimonial sudah dipilih. Batalkan approval beberapa testimonial terlebih dahulu.'
            ]);
            exit;
        }
    }
    
    // Update approval status
    $sql = "UPDATE website_testimonials SET is_approved = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    $stmt->bind_param("ii", $is_approved, $id);
    
    if ($stmt->execute()) {
        $message = $is_approved == 1 ? 'Testimonial disetujui dan akan ditampilkan di homepage' : 'Approval dibatalkan, testimonial tidak akan ditampilkan';
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        throw new Exception('Failed to execute: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log("Error in toggle_approval.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>