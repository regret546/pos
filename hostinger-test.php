<?php
// Simple test file for Hostinger deployment
echo "<!DOCTYPE html><html><head><title>Hostinger Test</title></head><body>";
echo "<h1>Hostinger Test Page</h1>";
echo "<p>If you can see this, PHP is working!</p>";
echo "<p>Server Info:</p>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
echo "<li>Script Name: " . $_SERVER['SCRIPT_NAME'] . "</li>";
echo "<li>Request URI: " . $_SERVER['REQUEST_URI'] . "</li>";
echo "<li>HTTP Host: " . $_SERVER['HTTP_HOST'] . "</li>";
echo "</ul>";

echo "<h2>File System Check:</h2>";
echo "<ul>";
echo "<li>index.php exists: " . (file_exists('index.php') ? 'YES' : 'NO') . "</li>";
echo "<li>.htaccess exists: " . (file_exists('.htaccess') ? 'YES' : 'NO') . "</li>";
echo "<li>views folder exists: " . (is_dir('views') ? 'YES' : 'NO') . "</li>";
echo "<li>controllers folder exists: " . (is_dir('controllers') ? 'YES' : 'NO') . "</li>";
echo "</ul>";

if (file_exists('.htaccess')) {
    echo "<h2>.htaccess content:</h2>";
    echo "<pre>" . htmlspecialchars(file_get_contents('.htaccess')) . "</pre>";
}

echo "</body></html>";
?>
