<?php
/**
 * Configuration file
 * Contains application constants and settings
 */

// Error reporting settings
ini_set('display_errors', 1); // Set to 0 in production
ini_set('display_startup_errors', 1); // Set to 0 in production
error_reporting(E_ALL);

// Application constants
define('APP_NAME', 'Exam Reviewer');
define('APP_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:5000'));
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Session settings
if (!headers_sent()) {
    session_start();
}

// Timezone settings
date_default_timezone_set('UTC');

// Define user roles
define('ROLE_ADMIN', 'admin');
define('ROLE_USER', 'user');
?>
