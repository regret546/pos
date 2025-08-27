<?php

class Connection
{
    /**
     * Create and return a PDO connection.
     * 
     * Credentials are read from environment variables when available:
     * - DB_HOST
     * - DB_NAME
     * - DB_USER
     * - DB_PASS
     * 
     * Falls back to local development defaults when env vars are not set.
     */
    public static function connect()
    {
        // Helper to read an env var with fallback; checks getenv, $_ENV, $_SERVER, and REDIRECT_* variants
        $env = function (string $key, ?string $default = null): ?string {
            $value = getenv($key);
            if ($value === false || $value === '') {
                $value = $_ENV[$key] ?? $_SERVER[$key] ?? $_SERVER['REDIRECT_'.$key] ?? $default;
            }
            return $value;
        };

        $dbHost = $env('DB_HOST', 'localhost');
        $dbName = $env('DB_NAME', 'posystem');
        $dbUser = $env('DB_USER', 'root');
        $dbPass = $env('DB_PASS', '');

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8', $dbHost, $dbName);

        try {
            $link = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            return $link;
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
