<?php
// Tek başlangıç noktası
session_start();

// Doğru yol tanımlamaları
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/includes/config.php';
require_once ROOT_PATH . '/includes/database.php';
require_once ROOT_PATH . '/includes/security.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/auth.php';

// Global DB instance
$db = Database::getInstance()->getConnection();

// Global security instance  
$security = new Security();

// Error handler functions
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno]: $errstr in $errfile on line $errline");
    return true;
}

function customExceptionHandler($exception) {
    error_log("Exception: " . $exception->getMessage() . "\n" . $exception->getTraceAsString());
    http_response_code(500);
    
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        die("A system error occurred: " . htmlspecialchars($exception->getMessage()));
    } else {
        die("A system error occurred. Please try again later.");
    }
}

// Error handling
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');
?>