<?php
$tmpfile = sys_get_temp_dir() . '/laravel_test_' . time() . '.txt';
$msg = "TEST OUTPUT\n";
$msg .= "User: " . get_current_user() . "\n";
$msg .= "CWD: " . getcwd() . "\n";
$msg .= "PHP: " . phpversion() . "\n";
$msg .= "Temp dir: " . sys_get_temp_dir() . "\n";

// Try to list workspace files
$workspace_files = glob('c:\\Users\\seckm\\recommandationApi\\*');
$msg .= "Workspace files count: " . count($workspace_files) . "\n";
$msg .= "First 5: " . implode(', ', array_slice(array_map('basename', $workspace_files), 0, 5)) . "\n";

// Check .env
if (file_exists('.env')) {
    $msg .= ".env exists: YES\n";
} elseif (file_exists('c:\\Users\\seckm\\recommandationApi\\.env')) {
    $msg .= ".env (absolute) exists: YES\n";
} else {
    $msg .= ".env NOT FOUND\n";
}

// Try MySQL
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $msg .= "MySQL: SUCCESS\n";
    $dbs = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
    $msg .= "Databases: " . implode(', ', $dbs) . "\n";
} catch (Exception $e) {
    $msg .= "MySQL: FAILED - " . $e->getMessage() . "\n";
}

file_put_contents($tmpfile, $msg);
echo "Result written to: " . $tmpfile . "\nContent:\n" . $msg;
