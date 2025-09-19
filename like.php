<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = intval($_POST['post_id']);
    
    if ($postId > 0) {
        // Check if user already liked this post
        $stmt = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            // Unlike - remove the like
            $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$postId, $_SESSION['user_id']]);
            echo json_encode(['success' => true, 'action' => 'unliked']);
        } else {
            // Like - add the like
            $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
            $stmt->execute([$postId, $_SESSION['user_id']]);
            echo json_encode(['success' => true, 'action' => 'liked']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>