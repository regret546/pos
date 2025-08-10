<?php
// Hostinger Debug Script - Upload this to your server and visit it in browser
// This script will help diagnose the login redirection issue

echo "<h1>Hostinger Debug Information</h1>";

// 1. Check environment variables
echo "<h2>1. Environment Variables</h2>";
echo "<pre>";
echo "DB_HOST: " . (getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? 'NOT SET') . "\n";
echo "DB_NAME: " . (getenv('DB_NAME') ?: $_ENV['DB_NAME'] ?? 'NOT SET') . "\n";
echo "DB_USER: " . (getenv('DB_USER') ?: $_ENV['DB_USER'] ?? 'NOT SET') . "\n";
echo "DB_PASS: " . (getenv('DB_PASS') ? '***SET***' : 'NOT SET') . "\n";
echo "APP_ENV: " . (getenv('APP_ENV') ?: $_ENV['APP_ENV'] ?? 'NOT SET') . "\n";
echo "</pre>";

// 2. Check if REDIRECT_ prefixed environment variables work (common on shared hosting)
echo "<h2>2. REDIRECT_ Environment Variables (Hostinger fallback)</h2>";
echo "<pre>";
echo "REDIRECT_DB_HOST: " . (getenv('REDIRECT_DB_HOST') ?: $_ENV['REDIRECT_DB_HOST'] ?? 'NOT SET') . "\n";
echo "REDIRECT_DB_NAME: " . (getenv('REDIRECT_DB_NAME') ?: $_ENV['REDIRECT_DB_NAME'] ?? 'NOT SET') . "\n";
echo "REDIRECT_DB_USER: " . (getenv('REDIRECT_DB_USER') ?: $_ENV['REDIRECT_DB_USER'] ?? 'NOT SET') . "\n";
echo "REDIRECT_DB_PASS: " . (getenv('REDIRECT_DB_PASS') ? '***SET***' : 'NOT SET') . "\n";
echo "</pre>";

// 3. Test database connection
echo "<h2>3. Database Connection Test</h2>";
try {
    // Try to include the connection file
    if (file_exists('models/connection.php')) {
        require_once 'models/connection.php';
        echo "<span style='color: green;'>✓ Connection file found and included</span><br>";
        
        // Test if Connection class exists
        if (class_exists('Connection')) {
            echo "<span style='color: green;'>✓ Connection class exists</span><br>";
            
            // Try actual connection
            try {
                $pdo = Connection::connect();
                echo "<span style='color: green;'>✓ Database connection successful</span><br>";
            } catch (Exception $e) {
                echo "<span style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</span><br>";
            }
        } else {
            echo "<span style='color: red;'>✗ Connection class not found</span><br>";
        }
    } else {
        echo "<span style='color: red;'>✗ models/connection.php not found</span><br>";
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Error loading connection: " . $e->getMessage() . "</span><br>";
}

// 4. Check URL rewriting
echo "<h2>4. URL Rewriting Test</h2>";
echo "<pre>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "QUERY_STRING: " . $_SERVER['QUERY_STRING'] . "\n";
echo "route parameter: " . ($_GET['route'] ?? 'NOT SET') . "\n";
echo "</pre>";

// 5. Check mod_rewrite
echo "<h2>5. Apache Modules</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "<pre>";
    echo "mod_rewrite: " . (in_array('mod_rewrite', $modules) ? 'ENABLED' : 'NOT FOUND') . "\n";
    echo "mod_env: " . (in_array('mod_env', $modules) ? 'ENABLED' : 'NOT FOUND') . "\n";
    echo "</pre>";
} else {
    echo "<p>apache_get_modules() not available (common on shared hosting)</p>";
}

// 6. Test routing logic
echo "<h2>6. Routing Logic Test</h2>";
echo "<pre>";
$route = '';
if (isset($_GET['route'])) {
    $route = $_GET['route'];
} else {
    $request_uri = $_SERVER['REQUEST_URI'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    
    if (strpos($request_uri, $script_name) === 0) {
        $request_uri = substr($request_uri, strlen($script_name));
    }
    
    $request_uri = parse_url($request_uri, PHP_URL_PATH);
    $route = trim($request_uri, '/');
}

if (empty($route)) {
    $route = 'index';
}

echo "Calculated route: " . $route . "\n";
echo "Would include: views/modules/" . $route . ".php\n";

// Check if the route file exists
$route_file = "views/modules/" . $route . ".php";
if (file_exists($route_file)) {
    echo "Route file exists: YES\n";
} else {
    echo "Route file exists: NO\n";
}

// Check home.php specifically
$home_file = "views/modules/home.php";
if (file_exists($home_file)) {
    echo "Home file exists: YES\n";
} else {
    echo "Home file exists: NO\n";
}
echo "</pre>";

// 7. Session information
echo "<h2>7. Session Information</h2>";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session data:\n";
print_r($_SESSION);
echo "</pre>";

// 8. Test login redirect URL
echo "<h2>8. Login Redirect Test</h2>";
echo "<p>The login controller redirects to: <code>window.location = \"index.php?route=home\";</code></p>";
echo "<p>This should resolve to: <code>" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "/index.php?route=home</code></p>";
echo "<p>Try clicking: <a href='index.php?route=home'>Test Home Redirect</a></p>";
echo "<p>Try clicking: <a href='home'>Test Home Redirect (pretty URL)</a></p>";

// 9. File permissions check
echo "<h2>9. File Permissions</h2>";
$important_files = [
    'index.php',
    'views/modules/home.php',
    'views/modules/login.php',
    'controllers/users.controller.php',
    'models/connection.php'
];

foreach ($important_files as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        echo $file . ": " . substr(sprintf('%o', $perms), -4) . " (exists)<br>";
    } else {
        echo $file . ": <span style='color: red;'>NOT FOUND</span><br>";
    }
}

?>
