<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

$host = 'localhost';
$dbname = 'pos_sys';
$username = 'db_admin';
$password = 'Pa07xyav!';

echo "<pre>";
echo "Testing connection with:\n";
echo "Host: $host\n";
echo "Database: $dbname\n";
echo "Username: $username\n";
echo "Password: ********\n\n";

try {
    echo "Attempting PDO connection...\n";
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname",
        $username,
        $password
    );

    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected successfully!\n\n";

    // Test database queries
    echo "Testing basic queries:\n";

    // Test 1: Check if users table exists and count records
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Users in database: " . $result['count'] . "\n";

    // Test 2: Check database character set
    $stmt = $conn->query("SHOW VARIABLES LIKE 'character_set_database'");
    $charset = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Database character set: " . $charset['Value'] . "\n";

    // Test 3: Get MySQL version
    $stmt = $conn->query("SELECT VERSION() as version");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "MySQL version: " . $version['version'] . "\n";
} catch (PDOException $e) {
    echo "Connection failed!\n";
    echo "Error message: " . $e->getMessage() . "\n";

    // Additional error information
    echo "\nDetailed error information:\n";
    echo "Error code: " . $e->getCode() . "\n";

    // Check if host is reachable
    echo "\nChecking if host is reachable:\n";
    if (function_exists('fsockopen')) {
        $port = 3306;
        $timeout = 5;
        $sock = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if ($sock) {
            echo "Host $host is reachable on port $port\n";
            fclose($sock);
        } else {
            echo "Could not reach host $host on port $port\n";
            echo "Error ($errno): $errstr\n";
        }
    } else {
        echo "Cannot check host reachability - fsockopen function not available\n";
    }
}
echo "</pre>";
