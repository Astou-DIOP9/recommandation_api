<?php
// Step 1: Basic test
file_put_contents('test_step1.txt', "STEP1: PHP executed\n");

// Step 2: Check .env
$env_content = @file_get_contents('.env');
if ($env_content) {
    file_put_contents('test_step2.txt', "STEP2: .env found, size=" . strlen($env_content) . "\n");
    if (strpos($env_content, 'DB_CONNECTION=mysql') !== false) {
        file_put_contents('test_step3.txt', "STEP3: DB_CONNECTION=mysql FOUND\n");
    } else {
        file_put_contents('test_step3.txt', "STEP3: ERROR - DB_CONNECTION=mysql NOT FOUND\n");
    }
} else {
    file_put_contents('test_step2.txt', "STEP2: ERROR - .env not found\n");
}

// Step 3: Try to load Laravel
try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require __DIR__ . '/bootstrap/app.php';

    $default_conn = config('database.default');
    file_put_contents('test_step4.txt', "STEP4: Laravel loaded. Default connection: $default_conn\n");

    // Try to get MySQL connection
    try {
        $conn = config('database.connections.mysql');
        file_put_contents('test_step5.txt', "STEP5: MySQL config found. Host=" . $conn['host'] . " DB=" . $conn['database'] . "\n");
    } catch (Exception $e) {
        file_put_contents('test_step5.txt', "STEP5: ERROR getting MySQL config: " . $e->getMessage() . "\n");
    }
} catch (Exception $e) {
    file_put_contents('test_step4.txt', "STEP4: ERROR loading Laravel: " . $e->getMessage() . "\n");
}

// Step 4: Test MySQL connection
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    file_put_contents('test_step6.txt', "STEP6: MySQL connection SUCCESS\n");

    $stmt = $pdo->query('SHOW DATABASES');
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('systemRecommandation', $dbs)) {
        file_put_contents('test_step7.txt', "STEP7: Database systemRecommandation EXISTS\n");
    } else {
        file_put_contents('test_step7.txt', "STEP7: ERROR - systemRecommandation database NOT FOUND. Databases: " . implode(', ', $dbs) . "\n");
    }
} catch (Exception $e) {
    file_put_contents('test_step6.txt', "STEP6: ERROR MySQL connection: " . $e->getMessage() . "\n");
}

file_put_contents('test_done.txt', "ALL TESTS COMPLETED\n");
echo "Done\n";
