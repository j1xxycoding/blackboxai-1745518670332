<?php
// Database configuration
define('DB_DIR', __DIR__ . '/../db');
define('DB_PATH', DB_DIR . '/lynce.sqlite');

// Site configuration
define('SITE_NAME', 'LYNCE');
define('SITE_URL', 'http://localhost:8000');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/products/');
define('UPLOAD_URL', SITE_URL . '/assets/uploads/products/');

// Session configuration
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
