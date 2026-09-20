<?php
/**
 * ConnectMe - Configuration File
 *
 * Supports both local constants and environment variables (Render / Docker / Heroku).
 */

// Application Details
define('APP_NAME', getenv('APP_NAME') ?: 'ConnectMe');
define('APP_TAGLINE', 'Meet someone genuine');
define('APP_URL', getenv('APP_URL') ?: ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000')));
define('APP_ENV', getenv('APP_ENV') ?: 'development'); // 'development' or 'production'

// Database Configuration (Environment variables supported for Render / Cloud)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'defaultdb');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// Google OAuth 2.0 Credentials
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', APP_URL . '/auth/google-callback.php');

// Payment Gateway Settings (Razorpay India & Mock Mode)
define('ENABLE_MOCK_PAYMENT', getenv('ENABLE_MOCK_PAYMENT') !== false ? filter_var(getenv('ENABLE_MOCK_PAYMENT'), FILTER_VALIDATE_BOOLEAN) : true);
define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_YOUR_KEY_ID');
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: 'YOUR_RAZORPAY_SECRET');
define('CURRENCY', 'INR');

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/profiles/');
define('UPLOAD_URL', APP_URL . '/uploads/profiles/');
define('DEFAULT_AVATAR', 'default.jpg');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_MIME_TYPES', [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
]);

// Security & Session Settings
define('SESSION_NAME', 'CONNECTME_SESS');
define('SESSION_LIFETIME', 86400 * 7); // 7 days

// Error Handling Configuration
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
