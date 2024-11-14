<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'sinneonatolog_wp1'); 
define('DB_PASS', 'Mnbvcxz89@@');
define('DB_NAME', 'sinneonatolog_wp1');

// Security settings
define('HASH_SALT', 'your-unique-salt-here');
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 1800); // 30 minutes
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 3600); // 1 hour

// Site settings
define('SITE_URL', 'http://localhost/okb');
define('SITE_NAME', 'Backlink Management System');

// Email settings
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@example.com');
define('SMTP_PASS', 'your-password');
define('SMTP_FROM', 'noreply@example.com');
define('SMTP_FROM_NAME', 'Backlink System');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default timezone
date_default_timezone_set('Europe/Istanbul');