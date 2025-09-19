<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $bio = trim($_POST['bio']);

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields except bio are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            // Insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, bio) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $hashedPassword, $bio])) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Social Media Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <h1><a href="index.php" style="color: white; text-decoration: none;">🏆 CodeSocial</a></h1>
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="login.php">Login</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <h2>Create Account</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                    <br><a href="login.php">Login now</a>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required 
               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
    </div>

    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required 
               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
    </div>

    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>

    <div class="form-group">
        <label for="bio">Bio (optional):</label>
        <textarea id="bio" name="bio" placeholder="Tell us about yourself..."><?= isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : '' ?></textarea>
    </div>

    <div class="form-group">
        <label for="codeforces_handle">Codeforces Handle (optional):</label>
        <input type="text" id="codeforces_handle" name="codeforces_handle" 
               placeholder="e.g., tourist, jiangly"
               value="<?= isset($_POST['codeforces_handle']) ? htmlspecialchars($_POST['codeforces_handle']) : '' ?>">
    </div>

    <div class="form-group">
        <label for="profile_pic">Profile Picture (optional):</label>
        <div class="file-upload">
            <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
            <label for="profile_pic" class="file-upload-label">
                📷 Choose Profile Picture (Max 5MB)
            </label>
        </div>
    </div>

    <button type="submit" class="btn">Register</button>
    <a href="login.php" class="btn btn-secondary">Already have an account?</a>
</form>
        </div>
    </div>
</body>
</html>
<?php
require_once 'config.php';
require_once 'upload_handler.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $bio = trim($_POST['bio']);
    $codeforcesHandle = trim($_POST['codeforces_handle']);

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Username, email, and password are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            $profilePicPath = null;
            
            // Handle profile picture upload
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadProfilePicture($_FILES['profile_pic']);
                if ($uploadResult['success']) {
                    $profilePicPath = $uploadResult['path'];
                } else {
                    $error = $uploadResult['message'];
                }
            }
            
            if (empty($error)) {
                // Insert new user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, bio, profile_pic, codeforces_handle) VALUES (?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$username, $email, $hashedPassword, $bio, $profilePicPath, $codeforcesHandle])) {
                    $success = 'Registration successful! You can now login.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}
?>
