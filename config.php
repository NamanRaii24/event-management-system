<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'event_management_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application configuration
define('SITE_NAME', 'AEG College Event Portal');
define('SITE_URL', 'http://localhost/event_management_system');

// Security configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// Password requirements
define('MIN_PASSWORD_LENGTH', 8);
define('REQUIRE_SPECIAL_CHARS', true);
define('REQUIRE_NUMBERS', true);
define('REQUIRE_UPPERCASE', true);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
?> 