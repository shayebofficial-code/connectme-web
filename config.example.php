<?php
/**
 * ConnectMe - Example Configuration File
 *
 * Copy this file to config/config.php and fill in your actual hosting / database / OAuth details.
 */

// Application Details
define('APP_NAME', 'ConnectMe');
define('APP_TAGLINE', 'Meet someone genuine');
define('APP_URL', 'https://yourdomain.com');
define('APP_ENV', 'production'); // Change to 'development' during testing

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'YOUR_DATABASE_NAME');
define('DB_USER', 'YOUR_DATABASE_USER');
define('DB_PASS', 'YOUR_DATABASE_PASSWORD');
define('DB_CHARSET', 'utf8mb4');

// Google OAuth 2.0 Credentials
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/auth/google-callback.php');

// Payment Gateway Settings (Razorpay)
define('ENABLE_MOCK_PAYMENT', false); // Set to true for instant test payments without keys
define('RAZORPAY_KEY_ID', 'rzp_live_YOUR_KEY_ID');
define('RAZORPAY_KEY_SECRET', 'YOUR_RAZORPAY_SECRET');
define('CURRENCY', 'INR');

// Upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/profiles/');
define('UPLOAD_URL', APP_URL . '/uploads/profiles/');
define('DEFAULT_AVATAR', 'default.jpg');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_MIME_TYPES', [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
]);

define('SESSION_NAME', 'CONNECTME_SESS');
define('SESSION_LIFETIME', 86400 * 7);
