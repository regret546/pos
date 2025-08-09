<?php
/**
 * Diagnostic file for Hostinger deployment
 * Upload this to public_html and visit it in your browser to check:
 * 1. Environment variables are properly set
 * 2. Database connection works
 * 3. Users table exists with proper data
 * 
 * DELETE THIS FILE after testing for security!
 */

session_start();
echo "<h2>Hostinger Deployment Diagnostics</h2>";
echo "<pre>";

echo "=== ENVIRONMENT VARIABLES ===\n";
echo "DB_HOST: ".(getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? $_SERVER['REDIRECT_DB_HOST'] ?? '(missing)')."\n";
echo "DB_NAME: ".(getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? ($_SERVER['DB_NAME'] ?? ($_SERVER['REDIRECT_DB_NAME'] ?? '(missing)'))))."\n";
echo "DB_USER: ".(getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? ($_SERVER['DB_USER'] ?? ($_SERVER['REDIRECT_DB_USER'] ?? '(missing)'))))."\n";

// Fix logic for DB_PASS: show [SET] if any source is set, otherwise (missing)
$db_pass = getenv('DB_PASS');
if ($db_pass !== false && $db_pass !== '') {
    echo "DB_PASS: [SET]\n";
} elseif (!empty($_ENV['DB_PASS'])) {
    echo "DB_PASS: [SET]\n";
} elseif (!empty($_SERVER['DB_PASS'])) {
    echo "DB_PASS: [SET]\n";
} elseif (!empty($_SERVER['REDIRECT_DB_PASS'])) {
    echo "DB_PASS: [SET]\n";
} else {
    echo "DB_PASS: (missing)\n";
}

echo "APP_ENV: ".(getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? ($_SERVER['APP_ENV'] ?? ($_SERVER['REDIRECT_APP_ENV'] ?? '(not set)'))))."\n";

echo "\n=== DATABASE CONNECTION TEST ===\n";

require_once __DIR__."/models/connection.php";

try {
    $pdo = Connection::connect();
    echo "✓ Database connection: SUCCESS\n";
    
    // Test users table
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "✓ Users table exists with {$userCount} records\n";
    
    if ($userCount > 0) {
        echo "\n=== USER ACCOUNTS ===\n";
        $users = $pdo->query("SELECT id, name, user, profile, status, lastLogin FROM users ORDER BY id")->fetchAll();
        foreach ($users as $user) {
            $statusText = $user['status'] == 1 ? 'ACTIVE' : 'INACTIVE';
            echo "ID:{$user['id']} | User:{$user['user']} | Name:{$user['name']} | Profile:{$user['profile']} | Status:{$statusText} | Last:{$user['lastLogin']}\n";
        }
        
        echo "\n=== PASSWORD HASH TEST (for user 'admin') ===\n";
        $adminUser = $pdo->query("SELECT user, password FROM users WHERE user = 'admin' LIMIT 1")->fetch();
        if ($adminUser) {
            echo "✓ Admin user found\n";
            echo "Stored hash: {$adminUser['password']}\n";
            
            // Test password hashing with the same salt
            $testPassword = 'admin'; // Common default password
            $hashedTest = crypt($testPassword, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
            echo "Test hash for 'admin': {$hashedTest}\n";
            echo "Hashes match: " . ($adminUser['password'] === $hashedTest ? 'YES' : 'NO') . "\n";
        } else {
            echo "⚠ No 'admin' user found\n";
        }
    }
    
    echo "\n=== SESSION TEST ===\n";
    $_SESSION['test'] = 'working';
    echo "✓ Sessions are working\n";
    
    echo "\n=== PHP CONFIGURATION ===\n";
    echo "PHP Version: " . phpversion() . "\n";
    echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'Available' : 'Missing') . "\n";
    echo "Session started: " . (session_status() === PHP_SESSION_ACTIVE ? 'Yes' : 'No') . "\n";
    
} catch (Throwable $e) {
    echo "✗ Database connection FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "\n⚠ This looks like wrong DB credentials. Check your .htaccess SetEnv values.\n";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "\n⚠ Database name is wrong or doesn't exist.\n";
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "\n⚠ Cannot connect to DB host. Check DB_HOST value.\n";
    }
}

echo "\n=== NEXT STEPS ===\n";
echo "1. If environment variables show '(missing)', update your .htaccess with SetEnv directives\n";
echo "2. If DB connection fails, verify your Hostinger database credentials\n";
echo "3. If no users exist, import your users table from local database\n";
echo "4. Try logging in with the credentials shown above\n";
echo "5. DELETE THIS FILE after testing!\n";

echo "</pre>";
?>
