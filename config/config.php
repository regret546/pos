<?php

/**
 * Environment Configuration
 * Automatically detects development vs production environment
 */

class Config {
    
    /**
     * Detect environment based on domain/server
     */
    public static function getEnvironment() {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        
        // Check if running on Hostinger production
        if (strpos($host, 'jodur.tech') !== false || strpos($host, 'hostinger') !== false) {
            return 'production';
        }
        
        // Check if running on local development (Laragon, XAMPP, etc.)
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false || strpos($host, '.local') !== false) {
            return 'development';
        }
        
        // Default to development for safety
        return 'development';
    }
    
    /**
     * Get database configuration based on environment
     */
    public static function getDatabaseConfig() {
        $environment = self::getEnvironment();
        
        switch ($environment) {
            case 'production':
                // Load production secrets if available
                $secretsFile = __DIR__ . '/production-secrets.php';
                if (file_exists($secretsFile)) {
                    require_once $secretsFile;
                    $password = defined('PRODUCTION_DB_PASSWORD') ? PRODUCTION_DB_PASSWORD : '';
                } else {
                    $password = ''; // Will cause connection error - user needs to create secrets file
                }
                
                return [
                    'host' => 'localhost',
                    'database' => 'u735263260_pos',
                    'username' => 'u735263260_jkduran1998',
                    'password' => $password,
                    'charset' => 'utf8',
                    'environment' => 'production'
                ];
                
            case 'development':
            default:
                return [
                    'host' => 'localhost',
                    'database' => 'pos_sys',
                    'username' => 'db_admin',
                    'password' => 'Pa07xyav!',
                    'charset' => 'utf8',
                    'environment' => 'development'
                ];
        }
    }
    
    /**
     * Get application configuration
     */
    public static function getAppConfig() {
        $environment = self::getEnvironment();
        
        return [
            'environment' => $environment,
            'debug' => ($environment === 'development'),
            'error_reporting' => ($environment === 'development') ? E_ALL : E_ERROR,
            'display_errors' => ($environment === 'development') ? 1 : 0,
            'app_url' => ($environment === 'production') ? 'https://pos.jodur.tech' : 'http://localhost/POS-PHP',
            'timezone' => 'Asia/Manila', // Adjust to your timezone
        ];
    }
    
    /**
     * Initialize application configuration
     */
    public static function init() {
        $appConfig = self::getAppConfig();
        
        // Set error reporting based on environment
        error_reporting($appConfig['error_reporting']);
        ini_set('display_errors', $appConfig['display_errors']);
        
        // Set timezone
        date_default_timezone_set($appConfig['timezone']);
        
        // Log environment info (for debugging)
        if ($appConfig['debug']) {
            error_log("POS System running in: " . $appConfig['environment'] . " environment");
        }
    }
}

// Initialize configuration
Config::init(); 