<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_URL', 'http://localhost');
define('ADMIN_URL', SITE_URL . '/php');

// Security
define('HASH_ALGO', PASSWORD_DEFAULT);

// Pagination
define('RECORDS_PER_PAGE', 10);

// File upload settings
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>