<?php
	
require_once "extensions/vendor/autoload.php";

require_once "controllers/template.controller.php";
require_once "controllers/users.controller.php";
require_once "controllers/categories.controller.php";
require_once "controllers/products.controller.php";
require_once "controllers/customers.controller.php";
require_once "controllers/sales.controller.php";

require_once "models/users.model.php";
require_once "models/categories.model.php";
require_once "models/products.model.php";
require_once "models/customers.model.php";
require_once "models/sales.model.php";

// Handle routing for both mod_rewrite and non-mod_rewrite URLs
if (isset($_GET['route'])) {
    // Clean the route parameter
    $route = filter_var($_GET['route'], FILTER_SANITIZE_STRING);
} else {
    // Get the request URI and clean it
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    
    // Remove the base path from the URI
    $route = substr($uri, strlen($basePath));
    $route = trim($route, '/');
    
    // If no route specified, set to default
    if (empty($route)) {
        $route = 'index';
    }
}

// Store the clean route in $_GET for the template controller
$_GET['route'] = $route;

$template = new ControllerTemplate();
$template -> ctrTemplate();