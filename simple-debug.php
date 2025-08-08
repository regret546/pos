<?php
/**
 * Simple Standalone Database Debug Tool
 * This doesn't use the existing system to avoid login redirects
 */

// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Simple Database Connection Test</h1>";
echo "<p><strong>Server:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . "</p>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

echo "<h2>1. Environment Check</h2>";
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
echo "<p><strong>Domain:</strong> $host</p>";

if (strpos($host, 'jodur.tech') !== false) {
    echo "<p style='color: green;'>✅ Running on Hostinger (Production)</p>";
    $environment = 'production';
} else {
    echo "<p style='color: blue;'>ℹ️ Running on Local Development</p>";
    $environment = 'development';
}

echo "<h2>2. File Check</h2>";
$files = [
    'config/config.php' => file_exists('config/config.php'),
    'config/production-secrets.php' => file_exists('config/production-secrets.php'),
    'models/connection.php' => file_exists('models/connection.php')
];

foreach ($files as $file => $exists) {
    echo "<p><strong>$file:</strong> " . ($exists ? '✅ Exists' : '❌ Missing') . "</p>";
}

echo "<h2>3. Direct Database Test</h2>";

// Hardcoded credentials for testing
$testHost = 'localhost';
$testDb = 'u735263260_pos';
$testUser = 'u735263260_jkduran1998';

// ⚠️ ENTER YOUR HOSTINGER DATABASE PASSWORD HERE
$testPassword = ''; // <<< PUT YOUR PASSWORD HERE

echo "<p><strong>Database Credentials:</strong></p>";
echo "<ul>";
echo "<li>Host: $testHost</li>";
echo "<li>Database: $testDb</li>";
echo "<li>Username: $testUser</li>";
echo "<li>Password: " . (empty($testPassword) ? '❌ NOT SET - ENTER ABOVE' : '✅ Set (' . strlen($testPassword) . ' characters)') . "</li>";
echo "</ul>";

if (empty($testPassword)) {
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>⚠️ ACTION REQUIRED</h3>";
    echo "<p><strong>You need to:</strong></p>";
    echo "<ol>";
    echo "<li>Go to your <strong>Hostinger Control Panel</strong></li>";
    echo "<li>Navigate to <strong>Databases → MySQL Databases</strong></li>";
    echo "<li>Find database: <code>u735263260_pos</code></li>";
    echo "<li>Get the password for user: <code>u735263260_jkduran1998</code></li>";
    echo "<li>Edit this file and put the password in line: <code>\$testPassword = 'YOUR_PASSWORD';</code></li>";
    echo "<li>Refresh this page</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<h3>Testing Connection...</h3>";
    
    try {
        $dsn = "mysql:host=$testHost;dbname=$testDb;charset=utf8";
        echo "<p>Attempting connection with DSN: <code>$dsn</code></p>";
        
        $pdo = new PDO($dsn, $testUser, $testPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3 style='color: #155724;'>✅ SUCCESS!</h3>";
        echo "<p><strong>Database connection successful!</strong></p>";
        
        // Test a simple query
        $stmt = $pdo->prepare("SELECT 1 as test, NOW() as current_time");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo "<p>✅ Query test successful</p>";
            echo "<p>Server time: " . $result['current_time'] . "</p>";
        }
        
        echo "<h4>Next Step: Create Production Secrets File</h4>";
        echo "<p>Create file: <code>config/production-secrets.php</code></p>";
        echo "<p>Content:</p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px;'>";
        echo "&lt;?php\n";
        echo "define('PRODUCTION_DB_PASSWORD', '$testPassword');\n";
        echo "?&gt;";
        echo "</pre>";
        echo "</div>";
        
    } catch (PDOException $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3 style='color: #721c24;'>❌ CONNECTION FAILED</h3>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
        
        $errorMsg = $e->getMessage();
        
        if (strpos($errorMsg, 'Access denied') !== false) {
            echo "<h4>🔧 Solutions for Access Denied:</h4>";
            echo "<ol>";
            echo "<li><strong>Wrong password:</strong> Double-check your Hostinger database password</li>";
            echo "<li><strong>Wrong username:</strong> Verify it's exactly: <code>u735263260_jkduran1998</code></li>";
            echo "<li><strong>User doesn't exist:</strong> Check if the user exists in Hostinger panel</li>";
            echo "<li><strong>No permissions:</strong> User might not have access to this database</li>";
            echo "<li><strong>Reset password:</strong> Try resetting the database user password in Hostinger</li>";
            echo "</ol>";
        } elseif (strpos($errorMsg, 'Unknown database') !== false) {
            echo "<h4>🔧 Solutions for Unknown Database:</h4>";
            echo "<ol>";
            echo "<li><strong>Database doesn't exist:</strong> Create database <code>u735263260_pos</code> in Hostinger</li>";
            echo "<li><strong>Wrong database name:</strong> Check the exact name in Hostinger panel</li>";
            echo "<li><strong>Import database:</strong> Import your SQL file after creating the database</li>";
            echo "</ol>";
        } else {
            echo "<h4>🔧 General Solutions:</h4>";
            echo "<ol>";
            echo "<li>Check your Hostinger database panel for correct credentials</li>";
            echo "<li>Verify the database exists and is active</li>";
            echo "<li>Contact Hostinger support if the issue persists</li>";
            echo "</ol>";
        }
        echo "</div>";
    }
}

echo "<hr>";
echo "<h2>📋 Summary</h2>";
echo "<p><strong>Current Status:</strong> " . (empty($testPassword) ? 'Waiting for password' : 'Testing connection') . "</p>";
echo "<p><strong>Environment:</strong> $environment</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Get database password from Hostinger</li>";
echo "<li>Update \$testPassword in this file</li>";
echo "<li>Refresh to test connection</li>";
echo "<li>Create production-secrets.php when test succeeds</li>";
echo "<li>Delete this file when done</li>";
echo "</ol>";

echo "<div style='background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; margin-top: 20px;'>";
echo "<p><strong>⚠️ SECURITY WARNING:</strong> Delete this file after fixing the connection issue!</p>";
echo "</div>";
?> 