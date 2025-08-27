# POSYS Deployment Guide

This guide helps you deploy the POSYS system with backup functionality to a new computer.

## 📋 Prerequisites

### Required Software

- **Web Server**: Apache/Nginx (XAMPP, WAMP, Laragon, or similar)
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **PHP Extensions**: PDO, PDO_MySQL, GD, ZIP

## 🚀 Step-by-Step Deployment

### 1. **Copy Project Files**

```bash
# Copy the entire POSYS folder to your web directory
# Example paths:
# XAMPP: C:\xampp\htdocs\POSYS
# WAMP: C:\wamp64\www\POSYS
# Laragon: C:\laragon\www\POSYS
```

### 2. **Database Setup**

#### Option A: Import Existing Database

```sql
-- Create database
CREATE DATABASE posystem;

-- Import your database dump
mysql -u root -p posystem < your_backup_file.sql
```

#### Option B: Fresh Installation

```sql
-- Create database and user
CREATE DATABASE posystem;
CREATE USER 'db_admin'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON posystem.* TO 'db_admin'@'localhost';
FLUSH PRIVILEGES;
```

### 3. **Database Configuration**

#### Update Connection Settings

Edit `models/connection.php` or set environment variables:

**Option A: Direct Edit (Quick)**

```php
// In models/connection.php, update the defaults:
$dbHost = $env('DB_HOST', 'localhost');        // Your MySQL host
$dbName = $env('DB_NAME', 'posystem');         // Your database name
$dbUser = $env('DB_USER', 'root');             // Your MySQL user
$dbPass = $env('DB_PASS', '');                 // Your MySQL password
```

**Option B: Environment Variables (Recommended)**
Create a `.env` file in the project root:

```env
DB_HOST=localhost
DB_NAME=posystem
DB_USER=root
DB_PASS=your_password_here
```

### 4. **Backup System Configuration**

#### Automatic MySQL Path Detection

The backup system will automatically try to find `mysqldump` in these locations:

- **XAMPP**: `C:\xampp\mysql\bin\mysqldump.exe`
- **WAMP**: `C:\wamp\bin\mysql\mysql8.0.31\bin\mysqldump.exe`
- **Laragon**: `C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe`
- **System PATH**: If MySQL is in your system PATH

#### Manual Configuration (If Auto-Detection Fails)

If auto-detection doesn't work, you can:

1. **Find your mysqldump path**:

   ```cmd
   # Windows - search for mysqldump.exe
   where mysqldump
   # Or manually check these directories:
   dir C:\xampp\mysql\bin\mysqldump.exe
   dir "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe"
   ```

2. **Update the backup script** (if needed):
   Edit `backup/database_backup.php` and add your path to the `$possiblePaths` array around line 120.

### 5. **File Permissions**

#### Windows

```cmd
# Make sure these directories are writable:
# - backup/backups/ (for backup files)
# - backup/ (for backup.log)
# - views/img/products/ (for product images)
# - views/img/users/ (for user photos)
```

#### Linux/Mac

```bash
# Set proper permissions
chmod 755 backup/
chmod 777 backup/backups/
chmod 777 views/img/products/
chmod 777 views/img/users/
```

### 6. **Test the Installation**

#### 1. Test Database Connection

```bash
# Navigate to your project directory
cd /path/to/POSYS

# Test the connection
php -r "require_once 'models/connection.php'; try { \$pdo = Connection::connect(); echo 'Database connection successful!'; } catch(Exception \$e) { echo 'Error: ' . \$e->getMessage(); }"
```

#### 2. Test Backup System

```bash
# Test manual backup
php backup/database_backup.php
```

#### 3. Test Web Interface

1. Visit: `http://localhost/POSYS` (or your domain)
2. Login with your credentials
3. Go to: `http://localhost/POSYS/index.php?route=backup`
4. Click "Create Backup Now"

## 🔧 Environment-Specific Configuration

### XAMPP Setup

```php
// Typical XAMPP configuration
DB_HOST=localhost
DB_NAME=posystem
DB_USER=root
DB_PASS=          // Usually empty
```

### WAMP Setup

```php
// Typical WAMP configuration
DB_HOST=localhost
DB_NAME=posystem
DB_USER=root
DB_PASS=          // Usually empty
```

### MAMP Setup

```php
// Typical MAMP configuration
DB_HOST=localhost
DB_NAME=posystem
DB_USER=root
DB_PASS=root      // Default MAMP password
```

### Production Server Setup

```php
// Production configuration
DB_HOST=your_server_host
DB_NAME=your_db_name
DB_USER=your_db_user
DB_PASS=your_secure_password
```

## 🔒 Security Considerations

### 1. **Change Default Passwords**

```sql
-- Change default admin password
UPDATE users SET password = '$2y$10$new_hashed_password' WHERE id = 1;
```

### 2. **Secure Database Credentials**

- Use strong database passwords
- Consider using environment variables instead of hardcoded values
- Restrict database user privileges to only what's needed

### 3. **File Permissions**

- Ensure backup files are not publicly accessible
- Set proper file permissions on sensitive directories

### 4. **Backup Security**

- Store backups in a secure location
- Consider encrypting backup files
- Implement regular backup rotation

## 📅 Automated Backup Setup

### Windows Task Scheduler

1. Open **Task Scheduler**
2. Create Basic Task: **"POSYS Daily Backup"**
3. **Trigger**: Daily at 2:00 AM
4. **Action**: Start a program
5. **Program**: `C:\path\to\your\project\backup\run_backup.bat`
6. **Start in**: `C:\path\to\your\project\backup`

### Linux/Mac Cron Job

```bash
# Edit crontab
crontab -e

# Add this line for daily backup at 2 AM
0 2 * * * cd /path/to/POSYS && php backup/database_backup.php
```

## 🐛 Troubleshooting

### Common Issues

#### 1. "Database connection failed"

- Check database credentials in `models/connection.php`
- Ensure MySQL service is running
- Verify database exists

#### 2. "mysqldump not found"

- Check if MySQL is installed
- Verify the path in backup script
- Add MySQL bin directory to system PATH

#### 3. "Permission denied" errors

- Check file/directory permissions
- Ensure web server can write to backup directories

#### 4. "Backup page 404"

- Ensure `backup` is added to allowed routes in `views/template.php`
- Check if all backup files are copied

#### 5. "JSON response error"

- Clear browser cache
- Check PHP error logs
- Verify all backup files are properly uploaded

### Getting Help

#### Check Logs

1. **Backup Log**: `backup/backup.log`
2. **PHP Error Log**: Check your server's PHP error log
3. **MySQL Error Log**: Check MySQL error log

#### Debug Mode

Enable PHP error reporting temporarily:

```php
// Add to top of index.php for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## ✅ Deployment Checklist

- [ ] Web server installed and running
- [ ] PHP 7.4+ with required extensions
- [ ] MySQL 5.7+ installed and running
- [ ] Project files copied to web directory
- [ ] Database created and imported
- [ ] Database credentials configured
- [ ] File permissions set correctly
- [ ] Backup directories writable
- [ ] mysqldump path detected/configured
- [ ] Web interface accessible
- [ ] Backup functionality tested
- [ ] Automated backup scheduled (optional)
- [ ] Security settings reviewed

## 📞 Support

If you encounter issues during deployment:

1. Check the troubleshooting section above
2. Review the backup log file
3. Verify all prerequisites are met
4. Test each component individually

---

_This guide covers the most common deployment scenarios. Specific environments may require additional configuration._
