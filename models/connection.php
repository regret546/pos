<?php

// Include configuration
require_once __DIR__ . '/../config/config.php';

class Connection
{
	public static function connect()
	{
		try {
			// Get database configuration based on environment
			$dbConfig = Config::getDatabaseConfig();
			
			// Create PDO connection string
			$dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
			
			// Create PDO instance
			$link = new PDO(
				$dsn,
				$dbConfig['username'],
				$dbConfig['password']
			);
			
			// Set PDO options
			$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$link->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			$link->exec("set names {$dbConfig['charset']}");
			
			// Log successful connection in development
			if ($dbConfig['environment'] === 'development') {
				error_log("Connected to {$dbConfig['environment']} database: {$dbConfig['database']}");
			}
			
			return $link;
			
		} catch (PDOException $e) {
			// Log the error
			error_log("Database connection error: " . $e->getMessage());
			
			// In development, show detailed error
			if (Config::getAppConfig()['debug']) {
				throw new Exception("Database connection failed: " . $e->getMessage());
			} else {
				// In production, show generic error
				throw new Exception("Database connection failed. Please try again later.");
			}
		}
	}
	
	/**
	 * Get current environment info (for debugging)
	 */
	public static function getEnvironmentInfo()
	{
		$dbConfig = Config::getDatabaseConfig();
		return [
			'environment' => $dbConfig['environment'],
			'database' => $dbConfig['database'],
			'host' => $dbConfig['host']
		];
	}
}
