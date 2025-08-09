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

// Handle routing
$route = '';

if (isset($_GET['route'])) {
    $route = $_GET['route'];
} else {
    $request_uri = $_SERVER['REQUEST_URI'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    
    // Remove script name from URI if present
    if (strpos($request_uri, $script_name) === 0) {
        $request_uri = substr($request_uri, strlen($script_name));
    }
    
    // Clean the URI
    $request_uri = parse_url($request_uri, PHP_URL_PATH);
    $route = trim($request_uri, '/');
}

// Set default route if empty
if (empty($route)) {
    $route = 'index';
}

// Store the clean route
$_GET['route'] = $route;

$template = new ControllerTemplate();
$template -> ctrTemplate();