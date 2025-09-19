<?php
require_once 'config.php';
require_once 'upload_handler.php';
requireLogin();

$error = '';
$success = '';

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio']);
    $codeforcesHandle = trim($_POST['codeforces_handle']);
    $profilePicPath = $currentUser['profile_pic'];

    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadProfilePicture($_FILES['profile_pic']);
        if ($uploadResult['success']) {
            // Delete old profile picture
            if ($currentUser['profile_pic'] && file_exists($currentUser['profile_pic'])) {
                unlink($currentUser['profile_pic']);
            }
            $profilePicPath = $uploadResult['path'];
        } else {
            $error = $uploadResult['message'];
        }
    }

    if (empty($error)) {
        // Update user profile
        $stmt = $pdo->prepare("UPDATE users SET bio = ?, profile_pic = ?, codeforces_handle = ? WHERE id = ?");
        
        if ($stmt->execute([$bio, $profilePicPath, $codeforcesHandle, $_SESSION['user_id']])) {
            $success = 'Profile updated successfully!';
            // Refresh current user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $currentUser = $stmt->fetch();
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - CodeSocial</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <h1><a href="index.php" style="color: white; text-decoration: none;">🏆 CodeSocial</a></h1>
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="profile.php">My Profile</a>
                <a href="create_post.php">Create Post</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <h2>✏️ Edit Profile</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                    <br><a href="profile.php">View Profile</a>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Current Profile Picture:</label>
                    <div style="text-align: center; margin-bottom: 15px;">
                        <img src="<?= $currentUser['profile_pic'] ? htmlspecialchars($currentUser['profile_pic']) : 'https://via.placeholder.com/100x100/3f51b5/white?text=' . strtoupper(substr($currentUser['username'], 0, 1)) ?>" 
                             alt="Current Profile" style="width: 100px; height: 100px; border-radius: 50%; border: 3px solid #3f51b5;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="profile_pic">Change Profile Picture:</label>
                    <div class="file-upload">
                        <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                        <label for="profile_pic" class="file-upload-label">
                            📷 Choose New Profile Picture (Max 5MB)
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="bio">Bio:</label>
                    <textarea id="bio" name="bio" placeholder="Tell us about yourself..."><?= htmlspecialchars($currentUser['bio']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="codeforces_handle">Codeforces Handle:</label>
                    <input type="text" id="codeforces_handle" name="codeforces_handle" 
                           placeholder="e.g., tourist, jiangly"
                           value="<?= htmlspecialchars($currentUser['codeforces_handle']) ?>">
                    <small style="color: #999; display: block; margin-top: 5px;">
                        Enter your Codeforces handle to display your rating and statistics
                    </small>
                </div>

                <button type="submit" class="btn btn-success">Update Profile</button>
                <a href="profile.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <script>
    // Preview selected image
    document.getElementById('profile_pic').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const currentImg = document.querySelector('img[alt="Current Profile"]');
                currentImg.src = e.target.result;
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
    </script>
</body>
</html>