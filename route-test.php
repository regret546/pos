<?php
echo "<h1>Route Test</h1>";
echo "<pre>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "Base Path: " . dirname($_SERVER['SCRIPT_NAME']) . "\n";
echo "Current Route: " . (isset($_GET['route']) ? $_GET['route'] : 'none') . "\n";
echo "</pre>";
?> 