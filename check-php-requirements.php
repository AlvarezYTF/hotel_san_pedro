<?php
/**
 * PHP Requirements Checker for Laravel 12
 * 
 * This script verifies that all PHP requirements are met for Laravel 12
 * Run this script before deploying to Hostinger
 * 
 * Usage: php check-php-requirements.php
 */

function checkPhpRequirement(string $requirement, callable $check, string $message): array
{
    $result = $check();
    return [
        'requirement' => $requirement,
        'status' => $result,
        'message' => $message,
    ];
}

function formatBytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

echo "========================================\n";
echo "Laravel 12 PHP Requirements Checker\n";
echo "========================================\n\n";

$checks = [];

// PHP Version Check
$checks[] = checkPhpRequirement(
    'PHP Version >= 8.2',
    fn() => version_compare(PHP_VERSION, '8.2.0', '>='),
    'Current version: ' . PHP_VERSION
);

// Required Extensions
$requiredExtensions = [
    'pdo',
    'pdo_mysql',
    'mbstring',
    'openssl',
    'json',
    'tokenizer',
    'xml',
    'ctype',
    'fileinfo',
    'curl',
    'zip',
    'gd',
    'intl',
];

foreach ($requiredExtensions as $extension) {
    $checks[] = checkPhpRequirement(
        "Extension: $extension",
        fn() => extension_loaded($extension),
        $extension . ' extension'
    );
}

// Memory Limit Check
$memoryLimit = ini_get('memory_limit');
$memoryLimitBytes = return_bytes($memoryLimit);
$checks[] = checkPhpRequirement(
    'Memory Limit >= 128M',
    fn() => $memoryLimitBytes >= 134217728,
    'Current: ' . $memoryLimit . ' (' . formatBytes($memoryLimitBytes) . ')'
);

// Upload Max Filesize
$uploadMax = ini_get('upload_max_filesize');
$uploadMaxBytes = return_bytes($uploadMax);
$checks[] = checkPhpRequirement(
    'Upload Max Filesize >= 20M',
    fn() => $uploadMaxBytes >= 20971520,
    'Current: ' . $uploadMax
);

// Post Max Size
$postMax = ini_get('post_max_size');
$postMaxBytes = return_bytes($postMax);
$checks[] = checkPhpRequirement(
    'Post Max Size >= 20M',
    fn() => $postMaxBytes >= 20971520,
    'Current: ' . $postMax
);

// Max Execution Time
$maxExecutionTime = ini_get('max_execution_time');
$checks[] = checkPhpRequirement(
    'Max Execution Time >= 60',
    fn() => (int)$maxExecutionTime >= 60 || $maxExecutionTime === '0',
    'Current: ' . ($maxExecutionTime === '0' ? 'Unlimited' : $maxExecutionTime . 's')
);

// Function to convert ini size to bytes
function return_bytes(string $val): int
{
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    $val = (int)$val;
    
    switch ($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    
    return $val;
}

// Display Results
$allPassed = true;
foreach ($checks as $check) {
    $status = $check['status'] ? '✓ PASS' : '✗ FAIL';
    $color = $check['status'] ? "\033[32m" : "\033[31m";
    $reset = "\033[0m";
    
    echo sprintf(
        "%s%-30s %s%s %s\n",
        $color,
        $check['requirement'],
        $status,
        $reset,
        $check['message']
    );
    
    if (!$check['status']) {
        $allPassed = false;
    }
}

echo "\n========================================\n";
if ($allPassed) {
    echo "\033[32mAll requirements met! ✓\033[0m\n";
    echo "Your PHP configuration is ready for Laravel 12\n";
} else {
    echo "\033[31mSome requirements are not met! ✗\033[0m\n";
    echo "Please configure PHP settings in Hostinger panel or .user.ini file\n";
}
echo "========================================\n";

exit($allPassed ? 0 : 1);


