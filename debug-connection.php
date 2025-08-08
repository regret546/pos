<?php
/**
 * Database Connection Debug Tool
 * Upload this to Hostinger to debug the connection issue
 * DELETE after fixing the issue!
 */

echo "<h1>🔍 Hostinger Database Connection Debug</h1>";
echo "<p><strong>Server:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . "</p>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

echo "<h2>1. Environment Detection</h2>";
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
echo "<p><strong>Host detected:</strong> $host</p>";

if (strpos($host, 'jodur.tech') !== false) {
    echo "<p style='color: green;'>✅ Production environment detected correctly</p>";
} else {
    echo "<p style='color: red;'>❌ Environment detection failed - should be production</p>";
}

echo "<h2>2. Configuration Files Check</h2>";

// Check if config files exist
$configFile = __DIR__ . '/config/config.php';
$secretsFile = __DIR__ . '/config/production-secrets.php';

echo "<p><strong>Config file:</strong> " . ($configFile) . " - " . (file_exists($configFile) ? '✅ Exists' : '❌ Missing') . "</p>";
echo "<p><strong>Secrets file:</strong> " . ($secretsFile) . " - " . (file_exists($secretsFile) ? '✅ Exists' : '❌ Missing') . "</p>";

if (!file_exists($secretsFile)) {
    echo "<div style='background: #ffeeee; padding: 10px; border: 1px solid red;'>";
    echo "<h3>❌ PROBLEM FOUND: Missing production-secrets.php</h3>";
    echo "<p>You need to create: <code>config/production-secrets.php</code></p>";
    echo "<p>Content should be:</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>";
    echo "&lt;?php\n";
    echo "define('PRODUCTION_DB_PASSWORD', 'YOUR_REAL_PASSWORD_HERE');\n";
    echo "?&gt;";
    echo "</pre>";
    echo "</div>";
}

echo "<h2>3. Database Configuration Test</h2>";

// Test loading config
try {
    if (file_exists($configFile)) {
        require_once $configFile;
        echo "<p style='color: green;'>✅ Config file loaded successfully</p>";
        
        // Get database config
        $dbConfig = Config::getDatabaseConfig();
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";
        echo "<tr><td>Host</td><td>" . $dbConfig['host'] . "</td><td>✅</td></tr>";
        echo "<tr><td>Database</td><td>" . $dbConfig['database'] . "</td><td>✅</td></tr>";
        echo "<tr><td>Username</td><td>" . $dbConfig['username'] . "</td><td>✅</td></tr>";
        echo "<tr><td>Password</td><td>" . (empty($dbConfig['password']) ? '❌ EMPTY' : '✅ SET (' . strlen($dbConfig['password']) . ' chars)') . "</td><td>" . (empty($dbConfig['password']) ? '❌' : '✅') . "</td></tr>";
        echo "<tr><td>Environment</td><td>" . $dbConfig['environment'] . "</td><td>✅</td></tr>";
        echo "</table>";
        
        if (empty($dbConfig['password'])) {
            echo "<div style='background: #ffeeee; padding: 10px; border: 1px solid red; margin-top: 10px;'>";
            echo "<h3>❌ PROBLEM FOUND: Empty Password</h3>";
            echo "<p>The password is empty. This means:</p>";
            echo "<ul>";
            echo "<li>The <code>config/production-secrets.php</code> file doesn't exist, OR</li>";
            echo "<li>The file exists but doesn't define <code>PRODUCTION_DB_PASSWORD</code>, OR</li>";
            echo "<li>The constant is defined but empty</li>";
            echo "</ul>";
            echo "</div>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Config file not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error loading config: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Direct Database Connection Test</h2>";

// Test direct connection with hardcoded values
echo "<h3>4a. Test with your actual credentials</h3>";
echo "<p><em>You need to manually enter your Hostinger database password here for testing</em></p>";

// Get credentials from Hostinger
$testHost = 'localhost';
$testDb = 'u735263260_pos';
$testUser = 'u735263260_jkduran1998';
$testPassword = ''; // ⚠️ USER: Enter your actual Hostinger DB password here for testing

echo "<p><strong>Testing with:</strong></p>";
echo "<ul>";
echo "<li>Host: $testHost</li>";
echo "<li>Database: $testDb</li>";
echo "<li>Username: $testUser</li>";
echo "<li>Password: " . (empty($testPassword) ? '❌ ENTER YOUR PASSWORD ABOVE' : '✅ Set') . "</li>";
echo "</ul>";

if (!empty($testPassword)) {
    try {
        $dsn = "mysql:host=$testHost;dbname=$testDb;charset=utf8";
        $pdo = new PDO($dsn, $testUser, $testPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p style='color: green; font-size: 18px;'>✅ <strong>SUCCESS! Database connection works!</strong></p>";
        
        // Test query
        $stmt = $pdo->prepare("SELECT 1 as test");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result['test'] == 1) {
            echo "<p style='color: green;'>✅ Database query test successful!</p>";
        }
        
        echo "<div style='background: #eeffee; padding: 10px; border: 1px solid green;'>";
        echo "<h3>✅ SOLUTION:</h3>";
        echo "<p>Your database credentials work! Now create the production secrets file:</p>";
        echo "<p><strong>File:</strong> <code>config/production-secrets.php</code></p>";
        echo "<p><strong>Content:</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px;'>";
        echo "&lt;?php\n";
        echo "define('PRODUCTION_DB_PASSWORD', '$testPassword');\n";
        echo "?&gt;";
        echo "</pre>";
        echo "</div>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ <strong>Connection failed:</strong> " . $e->getMessage() . "</p>";
        
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();
        
        if (strpos($errorMessage, 'Access denied') !== false) {
            echo "<div style='background: #ffeeee; padding: 10px; border: 1px solid red;'>";
            echo "<h3>❌ ACCESS DENIED SOLUTIONS:</h3>";
            echo "<ol>";
            echo "<li><strong>Check password:</strong> Get the correct password from Hostinger Database panel</li>";
            echo "<li><strong>Check username:</strong> Verify it's exactly: u735263260_jkduran1998</li>";
            echo "<li><strong>Check database name:</strong> Verify it's exactly: u735263260_pos</li>";
            echo "<li><strong>Check user permissions:</strong> User must have access to this database</li>";
            echo "<li><strong>Reset password:</strong> In Hostinger, reset the database user password</li>";
            echo "</ol>";
            echo "</div>";
        } elseif (strpos($errorMessage, 'Unknown database') !== false) {
            echo "<div style='background: #ffeeee; padding: 10px; border: 1px solid red;'>";
            echo "<h3>❌ DATABASE NOT FOUND:</h3>";
            echo "<p>The database 'u735263260_pos' doesn't exist. You need to:</p>";
            echo "<ol>";
            echo "<li>Create the database in Hostinger panel</li>";
            echo "<li>Import your SQL file</li>";
            echo "</ol>";
            echo "</div>";
        }
    }
} else {
    echo "<p style='color: orange;'>⚠️ Enter your Hostinger database password in the code above to test the connection</p>";
}

echo "<hr>";
echo "<h2>🎯 Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Get your database password</strong> from Hostinger control panel</li>";
echo "<li><strong>Test the connection</strong> by entering the password in this file and refreshing</li>";
echo "<li><strong>Create production-secrets.php</strong> once the connection works</li>";
echo "<li><strong>Delete this debug file</strong> after fixing the issue</li>";
echo "</ol>";

echo "<p style='color: red; font-weight: bold;'>🚨 DELETE THIS FILE AFTER FIXING THE ISSUE!</p>";
?> 