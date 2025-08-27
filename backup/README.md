# POSYS Database Backup System

This backup system provides automated daily backups of your POSYS MySQL database with the following features:

- **Automated daily backups** with timestamp naming
- **Compression** to save disk space
- **Automatic cleanup** of old backups (keeps last 30 days)
- **Error logging** for troubleshooting
- **Windows Task Scheduler integration**

## Files Included

- `database_backup.php` - Main backup script
- `run_backup.bat` - Windows batch file for scheduling
- `README.md` - This documentation
- `backups/` - Directory where backup files are stored (created automatically)
- `backup.log` - Log file for backup operations (created automatically)

## Quick Setup

### 1. Test the Backup Script

First, test that the backup works manually:

```bash
cd C:\laragon\www\POSYS\backup
php database_backup.php
```

You should see output like:

```
POSYS Database Backup Script
============================

[2024-01-15 10:30:00] Starting database backup...
[2024-01-15 10:30:02] Backup created successfully: posys_backup_2024-01-15_10-30-00.sql (2.5 MB)
[2024-01-15 10:30:03] Backup compressed: posys_backup_2024-01-15_10-30-00.sql.gz (856 KB)

Backup completed successfully!
```

### 2. Setup Windows Task Scheduler

#### Option A: Using Task Scheduler GUI

1. Open **Task Scheduler** (type "Task Scheduler" in Windows Start menu)
2. Click **"Create Basic Task"** in the right panel
3. Fill in the details:
   - **Name**: `POSYS Daily Backup`
   - **Description**: `Daily automated backup of POSYS database`
4. **Trigger**: Select "Daily"
   - **Start date**: Today's date
   - **Start time**: Choose a time when the system is usually on (e.g., 2:00 AM)
   - **Recur every**: 1 days
5. **Action**: Select "Start a program"
   - **Program/script**: Browse to `C:\laragon\www\POSYS\backup\run_backup.bat`
   - **Start in**: `C:\laragon\www\POSYS\backup`
6. Click **Finish**

#### Option B: Using Command Line

Open Command Prompt as Administrator and run:

```cmd
schtasks /create /tn "POSYS Daily Backup" /tr "C:\laragon\www\POSYS\backup\run_backup.bat" /sc daily /st 02:00 /sd 01/15/2024
```

### 3. Verify the Schedule

Check that your task is scheduled:

```cmd
schtasks /query /tn "POSYS Daily Backup"
```

## Configuration Options

### Backup Retention

By default, the script keeps the last 30 backups. To change this:

1. Open `database_backup.php`
2. Find the line: `public function __construct($backupDir = 'backups', $maxBackups = 30)`
3. Change `30` to your desired number of backups to keep

### Backup Location

By default, backups are stored in the `backup/backups/` directory. To change this:

1. Open `database_backup.php`
2. Find the line: `public function __construct($backupDir = 'backups', $maxBackups = 30)`
3. Change `'backups'` to your desired directory path

### PHP Path Configuration

If your PHP installation is in a different location than Laragon's default:

1. Open `run_backup.bat`
2. Update the PHP path in this line:
   ```batch
   "C:\laragon\bin\php\php-8.2.4-Win32-vs16-x64\php.exe" database_backup.php
   ```

## Backup File Format

Backup files are named with the following format:

```
posys_backup_YYYY-MM-DD_HH-MM-SS.sql.gz
```

Example: `posys_backup_2024-01-15_02-00-00.sql.gz`

## Monitoring and Troubleshooting

### Check Backup Status

View recent log entries:

```bash
cd C:\laragon\www\POSYS\backup
type backup.log
```

### Manual Backup

Run a backup manually at any time:

```bash
cd C:\laragon\www\POSYS\backup
php database_backup.php
```

### Common Issues

**Issue**: "mysqldump command not found"
**Solution**: Add MySQL bin directory to your Windows PATH, or update the script to use the full path to mysqldump.exe

**Issue**: "Access denied" error
**Solution**: Check that the database credentials in your environment variables or connection.php are correct.

**Issue**: Task doesn't run
**Solution**:

- Ensure the computer is on at the scheduled time
- Check Task Scheduler History for error details
- Verify the PHP path in run_backup.bat is correct

## Restoring from Backup

To restore your database from a backup:

1. **Decompress the backup file** (if compressed):

   ```bash
   gunzip posys_backup_2024-01-15_02-00-00.sql.gz
   ```

2. **Restore the database**:
   ```bash
   mysql -h localhost -u db_admin -p posystem < posys_backup_2024-01-15_02-00-00.sql
   ```

## Security Recommendations

1. **Secure backup location**: Consider storing backups in a secure location outside the web directory
2. **Regular testing**: Periodically test restoration from backups
3. **Off-site backups**: Consider copying backups to cloud storage or another location
4. **Access control**: Ensure backup files have appropriate file permissions

## Advanced Features

### Email Notifications

To add email notifications on backup success/failure, you can modify the `database_backup.php` script to include PHP's mail functionality.

### Cloud Backup Integration

Consider adding cloud storage integration (Google Drive, Dropbox, AWS S3) for off-site backup storage.

### Multiple Database Support

The script can be easily modified to backup multiple databases if needed.

---

## Support

If you encounter any issues with the backup system, check:

1. The `backup.log` file for error details
2. Windows Event Viewer for Task Scheduler issues
3. MySQL error logs for database connection issues
