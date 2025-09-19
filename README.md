# CodeSocial
We made this project for out DATABASE course CSC430
🏆 CodeSocial - A Social Media Platform for Coders
CodeSocial is a full-featured social media web application built with PHP and MySQL. It's designed as a community hub for coders and competitive programmers, allowing them to share posts, interact with each other, and showcase their competitive programming achievements directly on their profiles via a real-time Codeforces API integration.
✨ Features
👤 User Authentication: Secure user registration, login, and logout functionality.
📝 Post Management: Create, view, and interact with posts on a central feed.
💬 Social Interaction: Like posts and write comments to engage with content.
🖼️ Customizable User Profiles: Users can edit their bio, upload a custom profile picture, and link their Codeforces handle.
🏅 Codeforces Stats Integration: Automatically fetches and displays a user's Codeforces rating, rank, and contribution on their profile page.
🎨 Responsive Design: A clean, modern, and responsive user interface inspired by the dark theme of platforms like Codeforces.
🛠️ Technology Stack
Backend: PHP
Frontend: HTML, CSS, JavaScript (for AJAX functionality)
Database: MySQL
Server Environment: Apache (run locally via XAMPP/WAMP/MAMP)
API: Codeforces API for user statistics.
🚀 Getting Started
Follow these instructions to get a copy of the project up and running on your local machine for development and testing purposes.
Prerequisites
You will need a local server environment that supports PHP and MySQL. We recommend using XAMPP as it's a free and easy-to-install package that includes everything you need.
XAMPP (or any equivalent like WAMP, MAMP)
Installation Steps
Clone the repository:

Move the project to your server directory:
Place the entire cloned project folder inside the htdocs directory of your XAMPP installation (e.g., C:/xampp/htdocs/codesocial).
Database Setup:
Start the Apache and MySQL modules in your XAMPP Control Panel.
Open your web browser and navigate to http://localhost/phpmyadmin/.
Create a new database named social_media_db.
Select the new database and go to the SQL tab.
Copy and execute the following SQL schema to create all the necessary tables:
--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `codeforces_handle` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `posts`
--
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `comments`
--
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `likes`
--
CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_id_user_id` (`post_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



Configure the Application:
Open the config.php file. The default database credentials are set for a standard XAMPP installation (root with no password). If your setup is different, update these variables:
$host = 'localhost';
$dbname = 'social_media_db';
$username = 'root';
$password = '';


Run the Application:
Open your web browser and navigate to http://localhost/codesocial/ (or the name you gave the project folder).
You should now see the homepage. You can register a new account and start using the platform!
🔐 Security Features
Security is a core aspect of this application. The following measures have been implemented to protect against common web vulnerabilities:
SQL Injection Prevention: All database queries are executed using PDO Prepared Statements, which separates SQL logic from user data, making it immune to SQL injection attacks.
Cross-Site Scripting (XSS) Prevention: All user-generated content is sanitized using the htmlspecialchars() function before being rendered in the HTML, preventing malicious scripts from being executed.
Secure Password Storage: User passwords are never stored in plain text. They are securely hashed using PHP's password_hash() function and verified with password_verify().
Secure File Uploads: Profile picture uploads are validated on the server-side to ensure that only specific image types (e.g., jpeg, png) and sizes (max 5MB) are permitted.
📂 File Structure
Here is a brief overview of the key files in the project:
/
├── uploads/              # Directory where user profile pictures are stored
├── config.php            # Database connection, session management, helper functions
├── index.php             # Main homepage and post feed
├── login.php             # User login page
├── register.php          # User registration page
├── logout.php            # Handles user logout
├── profile.php           # Displays user profiles and their posts
├── edit_profile.php      # Page for users to edit their profile
├── create_post.php       # Page for creating a new post
├── like.php              # Background script to handle post likes (AJAX)
├── comment.php           # Background script to handle new comments
├── upload_handler.php    # Contains functions for file uploads and the Codeforces API
└── style.css             # All CSS styles for the application

