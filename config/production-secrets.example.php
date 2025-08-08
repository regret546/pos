<?php

/**
 * Production Secrets Configuration
 * 
 * IMPORTANT: This is an example file!
 * 
 * On your production server (Hostinger):
 * 1. Copy this file to: config/production-secrets.php
 * 2. Fill in your actual production database password
 * 3. DO NOT commit production-secrets.php to GitHub!
 */

// Production Database Password
// Replace 'YOUR_HOSTINGER_DB_PASSWORD' with your actual password from Hostinger
define('PRODUCTION_DB_PASSWORD', 'YOUR_HOSTINGER_DB_PASSWORD');

// Optional: Additional production settings
define('PRODUCTION_DEBUG', false);
define('PRODUCTION_ERROR_EMAIL', 'your-email@example.com');

// Security Settings
define('PRODUCTION_SESSION_SECURE', true); // Use HTTPS only
define('PRODUCTION_SESSION_HTTPONLY', true); 