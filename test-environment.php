<?php
/**
 * Environment Test File
 * Use this to verify your environment detection and database configuration
 * Delete this file after testing
 */

require_once 'config/config.php';
require_once 'models/connection.php';

echo "<h1>POS System Environment Test</h1>";

// Test environment detection
echo "<h2>🔍 Environment Detection</h2>";
echo "<p><strong>Current Environment:</strong> " . Config::getEnvironment() . "</p>";
echo "<p><strong>Host:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . "</p>";

// Test database configuration
echo "<h2>🗄️ Database Configuration</h2>";
$dbConfig = Config::getDatabaseConfig();
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>Environment</td><td>" . $dbConfig['environment'] . "</td></tr>";
echo "<tr><td>Host</td><td>" . $dbConfig['host'] . "</td></tr>";
echo "<tr><td>Database</td><td>" . $dbConfig['database'] . "</td></tr>";
echo "<tr><td>Username</td><td>" . $dbConfig['username'] . "</td></tr>";
echo "<tr><td>Password</td><td>" . (empty($dbConfig['password']) ? '❌ Empty' : '✅ Set') . "</td></tr>";
echo "</table>";

// Test database connection
echo "<h2>🔗 Database Connection Test</h2>";
try {
    $connection = Connection::connect();
    echo "<p style='color: green;'>✅ <strong>Database connection successful!</strong></p>";
    
    // Test a simple query
    $stmt = $connection->prepare("SELECT 1 as test");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['test'] == 1) {
        echo "<p style='color: green;'>✅ <strong>Database query test successful!</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Database connection failed:</strong> " . $e->getMessage() . "</p>";
    
    if (Config::getEnvironment() === 'production') {
        echo "<p style='color: orange;'>💡 <strong>Production Tip:</strong> Make sure you've created <code>config/production-secrets.php</code> with your Hostinger database password.</p>";
    }
}

// Test application configuration
echo "<h2>⚙️ Application Configuration</h2>";
$appConfig = Config::getAppConfig();
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>Debug Mode</td><td>" . ($appConfig['debug'] ? '✅ Enabled' : '❌ Disabled') . "</td></tr>";
echo "<tr><td>Error Reporting</td><td>" . $appConfig['error_reporting'] . "</td></tr>";
echo "<tr><td>Display Errors</td><td>" . ($appConfig['display_errors'] ? 'On' : 'Off') . "</td></tr>";
echo "<tr><td>App URL</td><td>" . $appConfig['app_url'] . "</td></tr>";
echo "<tr><td>Timezone</td><td>" . $appConfig['timezone'] . "</td></tr>";
echo "</table>";

echo "<hr>";
echo "<p><strong>🗑️ Important:</strong> Delete this file (<code>test-environment.php</code>) after testing!</p>";
echo "<p><strong>📅 Test Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
?> 