<?php
require_once 'includes/auth.php';
require_once 'includes/security.php';

session_start();
session_destroy();
header('Location: login.php');
exit;