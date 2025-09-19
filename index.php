<?php
require_once 'config.php';

$stmt = $pdo->query("
    SELECT p.*, u.username, u.profile_pic,
           COUNT(DISTINCT l.id) as like_count,
           COUNT(DISTINCT c.id) as comment_count
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    LEFT JOIN likes l ON p.id = l.post_id 
    LEFT JOIN comments c ON p.id = c.post_id 
    GROUP BY p.id 
    ORDER BY p.created_at DESC
");
$posts = $stmt->fetchAll();
?>  

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <h1><a href="index.php" style="color: white; text-decoration: none;">🏆 CodeSocial</a></h1>
            <nav class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <a href="profile.php">My Profile</a>
                    <a href="create_post.php">Create Post</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if (!isLoggedIn()): ?>
            <div class="form-container">
                <h2>Welcome to CodeSocial</h2>
                <p>Please <a href="login.php">login</a> or <a href="register.php">register</a> to start posting and interacting!</p>
            </div>
            <?php else: ?>
                
                <div class="posts">
            <?php if (empty($posts)): ?>
                <div class="form-container">
                    <h2>No posts yet!</h2>
                    <p>Be the first to create a post!</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <div class="post-header">
                           <!-- Replace the post-author div in index.php -->
<div class="post-author">
    <img src="<?= !empty($post['profile_pic']) ? htmlspecialchars($post['profile_pic']) : 'https://via.placeholder.com/32x32/3f51b5/white?text=' . strtoupper(substr($post['username'], 0, 1)) ?>" 
         alt="Profile" style="width: 32px; height: 32px; border-radius: 50%; border: 2px solid #3f51b5; margin-right: 8px;">
    <a href="profile.php?user=<?= $post['username'] ?>" style="text-decoration: none; color: #64b5f6;">
        <?= htmlspecialchars($post['username']) ?>
    </a>
</div>
<div class="post-date"><?= date('M j, Y g:i A', strtotime($post['created_at'])) ?></div>
</div>
                        <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                        <div class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
                        
                        <div class="post-actions">
                            <?php if (isLoggedIn()): ?>
                                <?php
                                // Check if current user liked this post
                                $likeCheck = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
                                $likeCheck->execute([$post['id'], $_SESSION['user_id']]);
                                $hasLiked = $likeCheck->fetch();
                                ?>
                                <button class="like-btn <?= $hasLiked ? 'liked' : '' ?>" 
                                onclick="toggleLike(<?= $post['id'] ?>)">
                                    ❤️ <?= $post['like_count'] ?> Likes
                                </button>
                            <?php else: ?>
                                <span>❤️ <?= $post['like_count'] ?> Likes</span>
                            <?php endif; ?>
                            <span>💬 <?= $post['comment_count'] ?> Comments</span>
                        </div>

                        <!-- Comments -->
                        <div class="comments">
                            <?php
                            $commentStmt = $pdo->prepare("
                                SELECT c.*, u.username 
                                FROM comments c 
                                JOIN users u ON c.user_id = u.id 
                                WHERE c.post_id = ? 
                                ORDER BY c.created_at ASC
                            ");
                            $commentStmt->execute([$post['id']]);
                            $comments = $commentStmt->fetchAll();
                            ?>

                            <?php foreach ($comments as $comment): ?>
                                <div class="comment">
                                    <div class="comment-author">👤 <?= htmlspecialchars($comment['username']) ?></div>
                                    <div class="comment-content"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (isLoggedIn()): ?>
                                <form method="POST" action="comment.php" class="comment-form">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <textarea name="content" placeholder="Write a comment..." required></textarea>
                                    <button type="submit" class="btn">Comment</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleLike(postId) {
            fetch('like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'post_id=' + postId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                location.reload();
            }
        });
    }
</script>
    <?php endif; ?>
</body>
</html>