<?php
require_once 'includes/init.php';
require_once 'includes/auth.php';

// Log the logout action
logout();

// Redirect to login page
header('Location: login.php');
exit;