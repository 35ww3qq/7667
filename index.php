<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/security.php';

$security = new Security();

// Check if user is already logged in
if (check_auth()) {
    // Redirect based on user role
    if (is_admin()) {
        header('Location: panel/admin/dashboard.php');
    } else {
        header('Location: panel/customer/dashboard.php');
    }
    exit;
}

// Redirect to login page
header('Location: login.php');
exit;