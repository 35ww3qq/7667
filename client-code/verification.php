<?php
// This file should be placed in the client's website root directory
// It will verify the ownership of the website

$verification_code = $_GET['code'] ?? '';

if (empty($verification_code)) {
    http_response_code(400);
    echo 'Verification code is required';
    exit;
}

// Create verification file
$file_path = __DIR__ . '/backlink-verify.txt';
file_put_contents($file_path, $verification_code);

if (file_exists($file_path)) {
    echo 'Verification file created successfully. You can now verify your site ownership.';
} else {
    http_response_code(500);
    echo 'Failed to create verification file';
}