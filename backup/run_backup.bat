@echo off
REM =====================================================
REM POSYS Daily Database Backup Script
REM =====================================================
REM This batch file runs the PHP backup script
REM and can be scheduled to run daily using Windows Task Scheduler

echo Starting POSYS Database Backup...
echo ================================

REM Change to the backup directory
cd /d "%~dp0"

REM Run the PHP backup script
REM Update the PHP path below if your PHP installation is in a different location
"C:\laragon\bin\php\php-8.2.4-Win32-vs16-x64\php.exe" database_backup.php

REM Check if backup was successful
if %ERRORLEVEL% equ 0 (
    echo.
    echo Backup completed successfully at %date% %time%
) else (
    echo.
    echo Backup failed at %date% %time%
    echo Check backup.log for details
)

REM Uncomment the line below if you want to keep the command window open
REM pause

echo.
echo Script finished.
