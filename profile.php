<?php
require_once 'config.php';
require_once 'upload_handler.php';

// Get user info - either logged in user or specified user
$username = isset($_GET['user']) ? $_GET['user'] : (isset($_SESSION['username']) ? $_SESSION['username'] : '');

if (empty($username)) {
    header('Location: login.php');
    exit();
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: index.php');
    exit();
}

$isOwnProfile = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id'];

// Get Codeforces stats if handle exists
$cfStats = null;
if (!empty($user['codeforces_handle'])) {
    $cfStats = getCodeforcesUserInfo($user['codeforces_handle']);
}

// Get user's posts
$stmt = $pdo->prepare("
    SELECT p.*, COUNT(DISTINCT l.id) as like_count, COUNT(DISTINCT c.id) as comment_count
    FROM posts p 
    LEFT JOIN likes l ON p.id = l.post_id 
    LEFT JOIN comments c ON p.id = c.post_id 
    WHERE p.user_id = ? 
    GROUP BY p.id 
    ORDER BY p.created_at DESC
");
$stmt->execute([$user['id']]);
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['username']) ?>'s Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <h1><a href="index.php" style="color: white; text-decoration: none;">🏆 CodeSocial</a></h1>
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <?php if (isLoggedIn()): ?>
                    <?php if ($isOwnProfile): ?>
                        <a href="create_post.php">Create Post</a>
                        <a href="edit_profile.php">Edit Profile</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="profile-info">
            <div class="profile-header">
                <div class="profile-pic-container">
                    <img src="<?= $user['profile_pic'] ? htmlspecialchars($user['profile_pic']) : 'https://via.placeholder.com/120x120/3f51b5/white?text=' . strtoupper(substr($user['username'], 0, 1)) ?>" 
                         alt="Profile Picture" class="profile-pic">
                </div>
                <div class="profile-details">
                    <h2><?= htmlspecialchars($user['username']) ?></h2>
                    <p class="profile-bio">
                        <?= $user['bio'] ? nl2br(htmlspecialchars($user['bio'])) : 'No bio available.' ?>
                    </p>
                    <div class="profile-stats">
                        <div class="stat-card">
                            <div class="stat-value"><?= count($posts) ?></div>
                            <div class="stat-label">Posts</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?= date('M Y', strtotime($user['created_at'])) ?></div>
                            <div class="stat-label">Member Since</div>
                        </div>
                        <?php if (!empty($user['codeforces_handle'])): ?>
                        <div class="stat-card">
                            <div class="stat-value"><?= htmlspecialchars($user['codeforces_handle']) ?></div>
                            <div class="stat-label">CF Handle</div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($cfStats): ?>
            <div class="cf-stats">
                <h3>🏅 Codeforces Statistics</h3>
                <div class="cf-rating">
                    <div class="rating-current" style="color: <?= getRankColor($cfStats['rating']) ?>">
                        <?= $cfStats['rating'] ?>
                    </div>
                    <div class="rating-max">
                        Max: <?= $cfStats['maxRating'] ?> 
                        <span style="color: <?= getRankColor($cfStats['maxRating']) ?>">
                            (<?= htmlspecialchars($cfStats['maxRank']) ?>)
                        </span>
                    </div>
                </div>
                <div class="cf-rank" style="color: <?= getRankColor($cfStats['rating']) ?>">
                    <?= htmlspecialchars($cfStats['rank']) ?>
                </div>
                <div style="text-align: center; color: #bbb; font-size: 14px;">
                    Contribution: <?= $cfStats['contribution'] ?>
                </div>
            </div>
            <?php elseif (!empty($user['codeforces_handle'])): ?>
            <div class="alert alert-info">
                <span class="loading"></span> Loading Codeforces stats for <?= htmlspecialchars($user['codeforces_handle']) ?>...
                <br><small>If this persists, the handle might be invalid or CF API is unavailable.</small>
            </div>
            <?php endif; ?>
        </div>

        <div class="posts">
            <h3><?= $isOwnProfile ? 'My Posts' : htmlspecialchars($user['username']) . "'s Posts" ?></h3>
            
            <?php if (empty($posts)): ?>
                <div class="form-container">
                    <p><?= $isOwnProfile ? 'You haven\'t created any posts yet.' : 'This user hasn\'t posted anything yet.' ?></p>
                    <?php if ($isOwnProfile): ?>
                        <a href="create_post.php" class="btn">Create Your First Post</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <div class="post-header">
                            <div class="post-author">
                                <img src="<?= $user['profile_pic'] ? htmlspecialchars($user['profile_pic']) : 'https://via.placeholder.com/32x32/3f51b5/white?text=' . strtoupper(substr($user['username'], 0, 1)) ?>" 
                                     alt="Profile">
                                <span><?= htmlspecialchars($user['username']) ?></span>
                            </div>
                            <div class="post-date"><?= date('M j, Y g:i A', strtotime($post['created_at'])) ?></div>
                        </div>
                        <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                        <div class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
                        
                        <div class="post-actions">
                            <span>❤️ <?= $post['like_count'] ?> Likes</span>
                            <span>💬 <?= $post['comment_count'] ?> Comments</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>