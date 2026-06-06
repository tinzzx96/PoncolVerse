<?php
require_once '../../config/config.php';

header('Content-Type: application/json');
header('Cache-Control: public, max-age=3600'); // Cache 1 jam

// Path cache file
$cache_file = '../../cache/pricing_plans.json';
$cache_time = 3600; // 1 jam

// Cek apakah cache masih valid
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
    // Return cached data
    echo file_get_contents($cache_file);
    exit;
}

// Kalau cache expired/tidak ada, fetch dari database
try {
    $sql = "SELECT id, name, price, duration_days, features, max_profiles, video_quality, concurrent_streams 
            FROM subscription_plans 
            ORDER BY price ASC";
    
    $result = $conn->query($sql);
    
    $plans = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $plans[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'price' => (float)$row['price'],
                'duration_days' => (int)$row['duration_days'],
                'features' => $row['features'],
                'max_profiles' => (int)$row['max_profiles'],
                'video_quality' => $row['video_quality'],
                'concurrent_streams' => (int)$row['concurrent_streams']
            ];
        }
    }
    
    $json = json_encode($plans, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    
    // Save to cache
    if (!file_exists(dirname($cache_file))) {
        mkdir(dirname($cache_file), 0755, true);
    }
    file_put_contents($cache_file, $json);
    
    echo $json;
    
} catch (Exception $e) {
    error_log("Error in get_plans.php: " . $e->getMessage());
    
    // Fallback ke cache lama kalau ada error
    if (file_exists($cache_file)) {
        echo file_get_contents($cache_file);
    } else {
        echo json_encode([]);
    }
}
?>