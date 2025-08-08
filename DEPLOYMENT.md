# POS System Deployment Guide

## 🏗️ Environment Setup

This POS system automatically detects whether it's running in **development** or **production** environment and uses the appropriate database credentials.

### 🔍 Environment Detection

- **Development**: `localhost`, `127.0.0.1`, `.local` domains (Laragon, XAMPP, WAMP)
- **Production**: `jodur.tech`, `hostinger` domains

---

## 💻 Development Environment (Laragon/Local)

### Prerequisites
- Laragon, XAMPP, or WAMP
- PHP 7.4+
- MySQL 5.7+

### Setup
1. Clone the repository to your local server directory
2. The system will automatically use development credentials:
   - **Host**: localhost
   - **Database**: pos_sys
   - **Username**: db_admin
   - **Password**: Pa07xyav!

### Database Setup
1. Import the database schema from `DATABASE FILE/`
2. The system will connect automatically to your local database

---

## 🌐 Production Environment (Hostinger)

### Database Credentials
- **Host**: localhost
- **Database**: u735263260_pos
- **Username**: u735263260_jkduran1998
- **Website**: pos.jodur.tech

### Deployment Steps

#### 1. Upload Files
Upload all files to your Hostinger hosting directory **EXCEPT**:
- `config/production-secrets.php` (create this manually)
- Database files (import separately)
- User uploaded images (if any)

#### 2. Create Production Secrets File
On your Hostinger server, create: `config/production-secrets.php`

```php
<?php
// Replace with your actual Hostinger database password
define('PRODUCTION_DB_PASSWORD', 'your_actual_password_here');

// Optional production settings
define('PRODUCTION_DEBUG', false);
define('PRODUCTION_ERROR_EMAIL', 'your-email@example.com');
define('PRODUCTION_SESSION_SECURE', true);
define('PRODUCTION_SESSION_HTTPONLY', true);
?>
```

#### 3. Database Setup
1. Access your Hostinger MySQL database
2. Import the database schema
3. Ensure the database name matches: `u735263260_pos`

#### 4. File Permissions
Set appropriate permissions:
```bash
chmod 755 views/img/products/
chmod 755 views/img/users/
chmod 644 config/production-secrets.php
```

---

## 🔧 Configuration Files

### `config/config.php`
- Main configuration file
- Handles environment detection
- Sets database credentials based on environment
- **Safe for GitHub** (no sensitive data)

### `config/production-secrets.php` 
- Contains production database password
- **NOT in GitHub** (in .gitignore)
- Must be created manually on production server

### `.gitignore`
Excludes from GitHub:
- Production secrets
- User uploaded files
- Database files
- Logs and temporary files

---

## 🚀 Quick Deployment Checklist

### For GitHub
- [ ] Commit and push code
- [ ] Verify `.gitignore` excludes sensitive files
- [ ] Check that `production-secrets.example.php` is included

### For Hostinger
- [ ] Upload files via FTP/File Manager
- [ ] Create `config/production-secrets.php` with actual password
- [ ] Import database schema
- [ ] Set file permissions
- [ ] Test the application at pos.jodur.tech

---

## 🔍 Troubleshooting

### Database Connection Issues
1. Check if `config/production-secrets.php` exists on production
2. Verify database credentials in Hostinger panel
3. Check file permissions
4. Review error logs

### Environment Detection Issues
The system detects environment by domain:
- **Development**: localhost, 127.0.0.1, .local
- **Production**: jodur.tech, hostinger

### Debug Mode
- **Development**: Full error reporting enabled
- **Production**: Minimal error reporting for security

---

## 📧 Support

For deployment issues, check:
1. Error logs in your hosting panel
2. PHP error logs
3. Database connection status

Environment info can be checked with:
```php
print_r(Connection::getEnvironmentInfo());
``` 