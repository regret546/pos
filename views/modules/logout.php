<?php

session_destroy();

// Use absolute URL for better compatibility with hosting environments
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$redirectUrl = $protocol . $host . $basePath;

// If base path is just '/', remove it to avoid double slash
if ($basePath === '/') {
    $redirectUrl = $protocol . $host;
}

echo '<script>
	window.location = "' . $redirectUrl . '";
</script>';