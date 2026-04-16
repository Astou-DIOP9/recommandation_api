<?php
$result = [];

// Load .env
if (file_exists('.env')) {
    $env_content = file_get_contents('.env');
    preg_match('/DB_CONNECTION=(\w+)/', $env_content, $m);
    $result['env_db_connection'] = $m[1] ?? 'not found';
    preg_match('/DB_HOST=([\d.]+)/', $env_content, $m);
    $result['env_db_host'] = $m[1] ?? 'not found';
    preg_match('/DB_DATABASE=(\w+)/', $env_content, $m);
    $result['env_db_database'] = $m[1] ?? 'not found';
}

// Check if Laravel config says
require 'vendor/autoload.php';
try {
    $app = require 'bootstrap/app.php';
    $result['laravel_default_connection'] = config('database.default');
    $result['laravel_mysql_host'] = config('database.connections.mysql.host');
    $result['laravel_mysql_database'] = config('database.connections.mysql.database');
} catch (Exception $e) {
    $result['laravel_error'] = $e->getMessage();
}

// Try MySQL connection
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $result['mysql_connection'] = 'SUCCESS';

    // Check if database exists
    $dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    $result['mysql_databases'] = $dbs;
    $result['systemRecommandation_exists'] = in_array('systemRecommandation', $dbs);
} catch (Exception $e) {
    $result['mysql_connection'] = 'FAILED: ' . $e->getMessage();
}

file_put_contents('diagnostic.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Diagnostic written to diagnostic.json\n";
