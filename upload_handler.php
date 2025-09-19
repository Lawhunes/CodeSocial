<?php
require_once 'config.php';

function uploadProfilePicture($file) {
    $uploadDir = 'uploads/';
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error.'];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size too large. Maximum 5MB allowed.'];
    }
    
    // Check file type
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, and GIF allowed.'];
    }
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('profile_') . '.' . $extension;
    $targetPath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $targetPath];
    } else {
        return ['success' => false, 'message' => 'Failed to save file.'];
    }
}

function getCodeforcesUserInfo($handle) {
    if (empty($handle)) {
        return null;
    }
    
    $url = "https://codeforces.com/api/user.info?handles=" . urlencode($handle);
    
    // Use cURL for better error handling
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        
        if ($data && $data['status'] === 'OK' && !empty($data['result'])) {
            $userInfo = $data['result'][0];
            
            // Extract relevant information
            return [
                'handle' => $userInfo['handle'] ?? $handle,
                'rating' => $userInfo['rating'] ?? 0,
                'maxRating' => $userInfo['maxRating'] ?? 0,
                'rank' => $userInfo['rank'] ?? 'Unrated',
                'maxRank' => $userInfo['maxRank'] ?? 'Unrated',
                'contribution' => $userInfo['contribution'] ?? 0,
                'lastOnlineTimeSeconds' => $userInfo['lastOnlineTimeSeconds'] ?? 0
            ];
        }
    }
    
    return null;
}

function getRankColor($rating) {
    if ($rating >= 3000) return '#ff0000'; // Legendary Grandmaster
    if ($rating >= 2600) return '#ff0000'; // International Grandmaster  
    if ($rating >= 2400) return '#ff8c00'; // Grandmaster
    if ($rating >= 2300) return '#ff8c00'; // International Master
    if ($rating >= 2100) return '#ff8c00'; // Master
    if ($rating >= 1900) return '#aa00aa'; // Candidate Master
    if ($rating >= 1600) return '#0000ff'; // Expert
    if ($rating >= 1400) return '#00aaaa'; // Specialist
    if ($rating >= 1200) return '#008000'; // Pupil
    return '#808080'; // Newbie
}
?>