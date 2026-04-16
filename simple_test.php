<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$msg = "START\n";
$msg .= "TIME: " . date('Y-m-d H:i:s') . "\n";
$msg .= "cwd: " . getcwd() . "\n";
$msg .= "php: " . phpversion() . "\n";

try {
    $msg .= "Reading .env...\n";
    if (file_exists('.env')) {
        $msg .= ".env exists, size: " . filesize('.env') . "\n";
        $content = file_get_contents('.env');
        if (strpos($content, 'DB_CONNECTION') !== false) {
            $msg .= "DB_CONNECTION found in .env\n";
        }
    } else {
        $msg .= ".env DOES NOT EXIST\n";
    }
} catch (Throwable $e) {
    $msg .= "EXCEPTION: " . $e->getMessage() . "\n";
}

// Always write, even if exception
file_put_contents('simple_test.log', $msg);
echo $msg;
