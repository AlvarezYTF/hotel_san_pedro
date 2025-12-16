<?php
/**
 * PHP Information File
 * 
 * This file displays PHP configuration information.
 * IMPORTANT: Delete this file after verifying your PHP configuration in production!
 * 
 * Usage: Access via browser at https://your-domain.com/phpinfo.php
 * Security: Remove this file immediately after checking PHP settings
 */

// Security: Only allow access from localhost or specific IP
// Remove this check if you need to access from your IP
$allowedIPs = ['127.0.0.1', '::1'];
$clientIP = $_SERVER['REMOTE_ADDR'] ?? '';

// Uncomment and add your IP for remote access:
// $allowedIPs[] = 'YOUR_IP_ADDRESS';

if (!in_array($clientIP, $allowedIPs)) {
    http_response_code(403);
    die('Access denied. This file should be removed in production.');
}

phpinfo();


