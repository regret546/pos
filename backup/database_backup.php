<?php
/**
 * Automated Database Backup Script for POSYS
 * 
 * This script creates daily backups of your MySQL database with:
 * - Automatic file naming with timestamps
 * - Cleanup of old backups (keeps last 30 days by default)
 * - Error logging
 * - Compression support
 * 
 * Usage: php database_backup.php
 */

require_once dirname(__DIR__) . '/models/connection.php';

class DatabaseBackup
{
    private $backupDir;
    private $maxBackups;
    private $logFile;
    
    public function __construct($backupDir = 'backups', $maxBackups = 30)
    {
        $this->backupDir = __DIR__ . '/' . $backupDir;
        $this->maxBackups = $maxBackups;
        $this->logFile = __DIR__ . '/backup.log';
        
        // Create backup directory if it doesn't exist
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Create database backup
     */
    public function createBackup()
    {
        try {
            $this->log("Starting database backup...");
            
            // Get database connection details
            $dbConfig = $this->getDatabaseConfig();
            
            // Generate backup filename with Philippines timezone
            $dateTime = new DateTime('now', new DateTimeZone('Asia/Manila'));
            $timestamp = $dateTime->format('Y-m-d_H-i-s');
            $filename = "posys_backup_{$timestamp}.sql";
            $filepath = $this->backupDir . '/' . $filename;
            
            // Find mysqldump executable
            $mysqldumpPath = $this->findMysqldump();
            
            // Create mysqldump command
            $command = sprintf(
                '%s --host=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
                $mysqldumpPath,
                escapeshellarg($dbConfig['host']),
                escapeshellarg($dbConfig['user']),
                escapeshellarg($dbConfig['password']),
                escapeshellarg($dbConfig['database']),
                escapeshellarg($filepath)
            );
            
            $this->log("Executing command: " . str_replace($dbConfig['password'], '***', $command));
            
            // Execute backup command
            $output = [];
            $returnCode = 0;
            exec($command . ' 2>&1', $output, $returnCode);
            
            $this->log("Command return code: " . $returnCode);
            if (!empty($output)) {
                $this->log("Command output: " . implode("\n", $output));
            }
            
            // Check if backup was successful
            // A successful backup should have return code 0 AND a reasonable file size (> 1KB)
            if ($returnCode === 0 && file_exists($filepath) && filesize($filepath) > 1024) {
                $fileSize = $this->formatBytes(filesize($filepath));
                $this->log("Backup created successfully: {$filename} ({$fileSize})");
                
                // Compress the backup file
                $this->compressBackup($filepath);
                
                // Clean up old backups
                $this->cleanupOldBackups();
                
                return true;
            } else {
                $errorMsg = "Backup failed. ";
                
                if ($returnCode !== 0) {
                    $errorMsg .= "Return code: {$returnCode}. ";
                }
                
                if (!empty($output)) {
                    $errorMsg .= "Error: " . implode("\n", $output);
                } else {
                    $errorMsg .= "No output from mysqldump command.";
                }
                
                if (!file_exists($filepath)) {
                    $errorMsg .= " Backup file was not created.";
                } elseif (filesize($filepath) === 0) {
                    $errorMsg .= " Backup file is empty.";
                } elseif (filesize($filepath) <= 1024) {
                    // File is very small, likely contains error message
                    $fileContent = file_get_contents($filepath);
                    $errorMsg .= " Backup file is too small (" . filesize($filepath) . " bytes). Content: " . substr($fileContent, 0, 200);
                    // Delete the invalid backup file
                    unlink($filepath);
                }
                
                $this->log($errorMsg);
                return false;
            }
            
        } catch (Exception $e) {
            $this->log("Backup error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find mysqldump executable
     */
    private function findMysqldump()
    {
        // Common paths where mysqldump might be located
        $possiblePaths = [
            'mysqldump', // If it's in PATH
            'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe',
            'C:\xampp\mysql\bin\mysqldump.exe',
            'C:\wamp\bin\mysql\mysql8.0.31\bin\mysqldump.exe',
            'C:\mamp\bin\mysql\bin\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/lampp/bin/mysqldump'
        ];
        
        foreach ($possiblePaths as $path) {
            // Test if the executable exists and is accessible
            if ($this->testMysqldump($path)) {
                $this->log("Found mysqldump at: " . $path);
                return $path;
            }
        }
        
        // If not found, try to auto-detect based on environment
        if (PHP_OS_FAMILY === 'Windows') {
            // Try to find MySQL installation automatically on Windows
            $possibleDirs = [
                'C:\laragon\bin\mysql',
                'C:\xampp\mysql\bin',
                'C:\wamp\bin\mysql',
                'C:\Program Files\MySQL'
            ];
            
            foreach ($possibleDirs as $dir) {
                if (is_dir($dir)) {
                    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
                    foreach ($iterator as $file) {
                        if ($file->getFilename() === 'mysqldump.exe') {
                            $path = $file->getPathname();
                            if ($this->testMysqldump($path)) {
                                $this->log("Auto-detected mysqldump at: " . $path);
                                return $path;
                            }
                        }
                    }
                }
            }
        }
        
        // Default fallback
        $this->log("Warning: mysqldump not found in common locations, using 'mysqldump'");
        return 'mysqldump';
    }
    
    /**
     * Test if mysqldump is accessible at the given path
     */
    private function testMysqldump($path)
    {
        $testCommand = $path . ' --version 2>&1';
        $output = [];
        $returnCode = 0;
        exec($testCommand, $output, $returnCode);
        
        return $returnCode === 0 && !empty($output) && stripos(implode(' ', $output), 'mysqldump') !== false;
    }
    
    /**
     * Get database configuration from connection class
     */
    private function getDatabaseConfig()
    {
        // Test the actual connection to get working credentials
        try {
            $pdo = Connection::connect();
            
            // Get the actual user being used
            $stmt = $pdo->query("SELECT USER() as user");
            $result = $stmt->fetch();
            $actualUser = $result['user'];
            
            $this->log("Actual database user: " . $actualUser);
            
            // Parse user@host format
            if (strpos($actualUser, '@') !== false) {
                list($user, $host) = explode('@', $actualUser);
            } else {
                $user = $actualUser;
            }
            
            // Helper to read env vars with fallback (same as in Connection class)
            $env = function (string $key, ?string $default = null): ?string {
                $value = getenv($key);
                if ($value === false || $value === '') {
                    $value = $_ENV[$key] ?? $_SERVER[$key] ?? $_SERVER['REDIRECT_'.$key] ?? $default;
                }
                return $value;
            };

            // Use the actual working user instead of the configured one
            $config = [
                'host' => trim($env('DB_HOST', 'localhost')),
                'database' => trim($env('DB_NAME', 'posystem')),
                'user' => trim($user), // Use the actual user that works
                'password' => trim($env('DB_PASS', 'Pa07xyav!'))
            ];
            
            // For root user on Laragon, password might be empty
            if ($user === 'root') {
                // Try with the configured password first, then empty
                $config['password'] = trim($env('DB_PASS', ''));
                if (empty($config['password'])) {
                    $this->log("Using empty password for root user (common in Laragon)");
                }
            }
            
            return $config;
            
        } catch (Exception $e) {
            $this->log("Could not get actual database user, using configured values: " . $e->getMessage());
            
            // Fallback to original logic
            $env = function (string $key, ?string $default = null): ?string {
                $value = getenv($key);
                if ($value === false || $value === '') {
                    $value = $_ENV[$key] ?? $_SERVER[$key] ?? $_SERVER['REDIRECT_'.$key] ?? $default;
                }
                return $value;
            };

            return [
                'host' => trim($env('DB_HOST', 'localhost')),
                'database' => trim($env('DB_NAME', 'posystem')),
                'user' => trim($env('DB_USER', 'db_admin')),
                'password' => trim($env('DB_PASS', 'Pa07xyav!'))
            ];
        }
    }
    
    /**
     * Compress backup file using gzip
     */
    private function compressBackup($filepath)
    {
        if (function_exists('gzencode')) {
            $data = file_get_contents($filepath);
            $compressed = gzencode($data, 9);
            $compressedFile = $filepath . '.gz';
            
            if (file_put_contents($compressedFile, $compressed)) {
                unlink($filepath); // Remove uncompressed file
                $fileSize = $this->formatBytes(filesize($compressedFile));
                $this->log("Backup compressed: " . basename($compressedFile) . " ({$fileSize})");
            }
        }
    }
    
    /**
     * Clean up old backup files
     */
    private function cleanupOldBackups()
    {
        $files = glob($this->backupDir . '/posys_backup_*.sql*');
        
        if (count($files) > $this->maxBackups) {
            // Sort files by modification time (oldest first)
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Remove oldest files
            $filesToDelete = array_slice($files, 0, count($files) - $this->maxBackups);
            foreach ($filesToDelete as $file) {
                if (unlink($file)) {
                    $this->log("Deleted old backup: " . basename($file));
                }
            }
        }
    }
    
    /**
     * Log messages to file
     */
    private function log($message)
    {
        $dateTime = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $timestamp = $dateTime->format('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
        echo $logMessage;
    }
    
    /**
     * Format file size in human readable format
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Get backup statistics
     */
    public function getBackupStats()
    {
        $files = glob($this->backupDir . '/posys_backup_*.sql*');
        $totalSize = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
        }
        
        // Convert timestamps to Philippines timezone
        $oldestBackup = 'None';
        $newestBackup = 'None';
        
        if (count($files) > 0) {
            // Get oldest file timestamp and convert to Philippines time
            $oldestTimestamp = filemtime(min($files));
            $oldestDateTime = new DateTime();
            $oldestDateTime->setTimestamp($oldestTimestamp);
            $oldestDateTime->setTimezone(new DateTimeZone('Asia/Manila'));
            $oldestBackup = $oldestDateTime->format('Y-m-d H:i:s');
            
            // Get newest file timestamp and convert to Philippines time
            $newestTimestamp = filemtime(max($files));
            $newestDateTime = new DateTime();
            $newestDateTime->setTimestamp($newestTimestamp);
            $newestDateTime->setTimezone(new DateTimeZone('Asia/Manila'));
            $newestBackup = $newestDateTime->format('Y-m-d H:i:s');
        }
        
        return [
            'total_backups' => count($files),
            'total_size' => $this->formatBytes($totalSize),
            'oldest_backup' => $oldestBackup,
            'newest_backup' => $newestBackup
        ];
    }
}

// Run backup if script is executed directly (not via web/AJAX or include)
if (php_sapi_name() === 'cli' && basename($_SERVER['SCRIPT_NAME']) === 'database_backup.php') {
    echo "POSYS Database Backup Script\n";
    echo "============================\n\n";
    
    $backup = new DatabaseBackup();
    
    if ($backup->createBackup()) {
        echo "\nBackup completed successfully!\n";
        
        $stats = $backup->getBackupStats();
        echo "\nBackup Statistics:\n";
        echo "- Total backups: {$stats['total_backups']}\n";
        echo "- Total size: {$stats['total_size']}\n";
        echo "- Oldest backup: {$stats['oldest_backup']}\n";
        echo "- Newest backup: {$stats['newest_backup']}\n";
    } else {
        echo "\nBackup failed! Check the log file for details.\n";
        exit(1);
    }
}
?>
