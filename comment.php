<?php
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = intval($_POST['post_id']);
    $content = trim($_POST['content']);

    if (!empty($content) && $postId > 0) {
        // Verify post exists
        $postCheck = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
        $postCheck->execute([$postId]);
        
        if ($postCheck->fetch()) {
            // Insert comment
            $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$postId, $_SESSION['user_id'], $content]);
        }
    }
}

// Redirect back to home page
header('Location: index.php');
exit();
?>