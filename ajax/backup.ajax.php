<?php

require_once dirname(__DIR__) . "/backup/database_backup.php";

class AjaxBackup {

    /*=============================================
    HANDLE AJAX REQUESTS
    =============================================*/
    public function ajaxHandleBackup() {
        
        if(isset($_POST["action"])) {
            
            switch($_POST["action"]) {
                
                case "create":
                    $this->createBackup();
                    break;
                    
                case "stats":
                    $this->getBackupStats();
                    break;
                    
                case "list":
                    $this->getBackupList();
                    break;
                    
                case "log":
                    $this->getBackupLog();
                    break;
                    
                case "delete":
                    $this->deleteBackup();
                    break;
                    
                default:
                    echo json_encode(["success" => false, "message" => "Invalid action"]);
                    break;
            }
            
        } elseif(isset($_GET["action"]) && $_GET["action"] == "download") {
            
            $this->downloadBackup();
            
        } else {
            
            echo json_encode(["success" => false, "message" => "No action specified"]);
            
        }
    }
    
    /*=============================================
    CREATE BACKUP
    =============================================*/
    private function createBackup() {
        
        try {
            
            // Set longer execution time for backup
            set_time_limit(300); // 5 minutes
            
            // Capture any output to prevent JSON corruption
            ob_start();
            
            $backup = new DatabaseBackup();
            $result = $backup->createBackup();
            
            // Clear any captured output (debug messages)
            ob_end_clean();
            
            if($result) {
                echo json_encode([
                    "success" => true, 
                    "message" => "Database backup created successfully!"
                ]);
            } else {
                // Get the last few lines from the log for better error reporting
                $logFile = dirname(__FILE__) . '/../backup/backup.log';
                $errorDetails = "Failed to create backup.";
                
                if (file_exists($logFile)) {
                    $logContent = file_get_contents($logFile);
                    $lines = explode("\n", $logContent);
                    $lastLines = array_slice($lines, -5); // Get last 5 lines
                    $errorDetails .= " Last log entries: " . implode(" | ", array_filter($lastLines));
                }
                
                echo json_encode([
                    "success" => false, 
                    "message" => $errorDetails
                ]);
            }
            
        } catch(Exception $e) {
            
            // Clear any output buffer in case of exception
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            echo json_encode([
                "success" => false, 
                "message" => "Error: " . $e->getMessage()
            ]);
            
        }
    }
    
    /*=============================================
    GET BACKUP STATISTICS
    =============================================*/
    private function getBackupStats() {
        
        try {
            
            $backup = new DatabaseBackup();
            $stats = $backup->getBackupStats();
            
            echo json_encode([
                "success" => true, 
                "data" => $stats
            ]);
            
        } catch(Exception $e) {
            
            echo json_encode([
                "success" => false, 
                "message" => "Error: " . $e->getMessage()
            ]);
            
        }
    }
    
    /*=============================================
    GET BACKUP FILES LIST
    =============================================*/
    private function getBackupList() {
        
        try {
            
            $backupDir = dirname(__FILE__) . '/../backup/backups';
            $files = glob($backupDir . '/posys_backup_*.sql*');
            
            $backupList = [];
            
            foreach($files as $file) {
                
                // Convert file timestamp to Philippines time
                $fileTimestamp = filemtime($file);
                $dateTime = new DateTime();
                $dateTime->setTimestamp($fileTimestamp);
                $dateTime->setTimezone(new DateTimeZone('Asia/Manila'));
                $formattedDate = $dateTime->format('Y-m-d H:i:s');
                
                $backupList[] = [
                    "filename" => basename($file),
                    "date" => $formattedDate,
                    "size" => $this->formatBytes(filesize($file))
                ];
                
            }
            
            // Sort by date (newest first)
            usort($backupList, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            
            echo json_encode([
                "success" => true, 
                "data" => $backupList
            ]);
            
        } catch(Exception $e) {
            
            echo json_encode([
                "success" => false, 
                "message" => "Error: " . $e->getMessage()
            ]);
            
        }
    }
    
    /*=============================================
    GET BACKUP LOG
    =============================================*/
    private function getBackupLog() {
        
        try {
            
            $logFile = dirname(__FILE__) . '/../backup/backup.log';
            
            if(file_exists($logFile)) {
                
                $logContent = file_get_contents($logFile);
                
                // Get last 50 lines
                $lines = explode("\n", $logContent);
                $lastLines = array_slice($lines, -50);
                $logContent = implode("\n", $lastLines);
                
                echo json_encode([
                    "success" => true, 
                    "data" => $logContent
                ]);
                
            } else {
                
                echo json_encode([
                    "success" => true, 
                    "data" => "No log file found. Create your first backup to start logging."
                ]);
                
            }
            
        } catch(Exception $e) {
            
            echo json_encode([
                "success" => false, 
                "message" => "Error: " . $e->getMessage()
            ]);
            
        }
    }
    
    /*=============================================
    DELETE BACKUP
    =============================================*/
    private function deleteBackup() {
        
        if(!isset($_POST["filename"])) {
            echo json_encode([
                "success" => false, 
                "message" => "No filename specified"
            ]);
            return;
        }
        
        try {
            
            $filename = $_POST["filename"];
            $backupDir = dirname(__FILE__) . '/../backup/backups';
            $filepath = $backupDir . '/' . $filename;
            
            // Security check - ensure file is in backup directory and has correct pattern
            if(!preg_match('/^posys_backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql(\.gz)?$/', $filename)) {
                echo json_encode([
                    "success" => false, 
                    "message" => "Invalid filename format"
                ]);
                return;
            }
            
            if(file_exists($filepath)) {
                
                if(unlink($filepath)) {
                    echo json_encode([
                        "success" => true, 
                        "message" => "Backup file deleted successfully"
                    ]);
                } else {
                    echo json_encode([
                        "success" => false, 
                        "message" => "Failed to delete backup file"
                    ]);
                }
                
            } else {
                
                echo json_encode([
                    "success" => false, 
                    "message" => "Backup file not found"
                ]);
                
            }
            
        } catch(Exception $e) {
            
            echo json_encode([
                "success" => false, 
                "message" => "Error: " . $e->getMessage()
            ]);
            
        }
    }
    
    /*=============================================
    DOWNLOAD BACKUP
    =============================================*/
    private function downloadBackup() {
        
        if(!isset($_GET["file"])) {
            header("HTTP/1.1 400 Bad Request");
            echo "No file specified";
            return;
        }
        
        try {
            
            $filename = $_GET["file"];
            $backupDir = dirname(__FILE__) . '/../backup/backups';
            $filepath = $backupDir . '/' . $filename;
            
            // Security check - ensure file is in backup directory and has correct pattern
            if(!preg_match('/^posys_backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql(\.gz)?$/', $filename)) {
                header("HTTP/1.1 400 Bad Request");
                echo "Invalid filename format";
                return;
            }
            
            if(file_exists($filepath)) {
                
                $filesize = filesize($filepath);
                $mimetype = (strpos($filename, '.gz') !== false) ? 'application/gzip' : 'application/sql';
                
                header('Content-Type: ' . $mimetype);
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . $filesize);
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                
                readfile($filepath);
                
            } else {
                
                header("HTTP/1.1 404 Not Found");
                echo "File not found";
                
            }
            
        } catch(Exception $e) {
            
            header("HTTP/1.1 500 Internal Server Error");
            echo "Error: " . $e->getMessage();
            
        }
    }
    
    /*=============================================
    FORMAT BYTES
    =============================================*/
    private function formatBytes($size, $precision = 2) {
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}

// Handle the request
$backup = new AjaxBackup();
$backup->ajaxHandleBackup();

?>
