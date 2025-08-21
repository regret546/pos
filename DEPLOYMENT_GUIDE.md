# 🚀 Local Deployment Guide - POS PHP System

## 📋 Prerequisites

### Required Software:

1. **Laragon** (Latest version)
   - Download from: https://laragon.org/download/
   - Includes Apache, MySQL, PHP, and phpMyAdmin

## 🛠️ Installation Steps

### Step 1: Install Laragon

1. Download Laragon from the official website
2. Run the installer with administrator privileges
3. Install with default settings (recommended)
4. Start Laragon after installation

### Step 2: Setup Project Directory

1. Copy the POS-PHP project folder to Laragon's www directory:
   ```
   C:\laragon\www\POS-PHP\
   ```
2. The project should be accessible at: `http://localhost/POS-PHP` or `http://pos-php.test`

### Step 3: Database Setup

#### Option A: Create Empty Database (Recommended for clean deployment)

1. Open Laragon
2. Click **"Database"** → **"Open"** (opens phpMyAdmin)
3. Create a new database:
   - Database name: `pos_database` (or your preferred name)
   - Collation: `utf8mb4_general_ci`

#### Option B: Import Existing Database Structure

1. If you have an SQL dump file, import it via phpMyAdmin
2. Go to **Import** tab and select your SQL file

### Step 4: Configure Database Connection

1. Open the project in your code editor
2. Navigate to `models/connection.php`
3. Update the database configuration:

```php
class Connection{

    static public function connect(){

        // For Laragon default setup
        $link = new PDO("mysql:host=localhost;dbname=pos_database",
                       "root",
                       "",
                       array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

        return $link;

    }

}
```

**Default Laragon Settings:**

- **Host:** `localhost`
- **Username:** `root`
- **Password:** `` (empty)
- **Database:** `pos_database` (or your chosen name)

### Step 5: Create Database Tables

If you're starting with an empty database, you'll need to create the necessary tables. Here's the basic structure:

#### Users Table:

```sql
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `user` text NOT NULL,
  `password` text NOT NULL,
  `profile` text NOT NULL,
  `photo` text NOT NULL,
  `status` int(11) NOT NULL,
  `lastLogin` datetime NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Default Admin User:

```sql
INSERT INTO `users` (`name`, `user`, `password`, `profile`, `photo`, `status`, `lastLogin`)
VALUES ('Administrator', 'admin', '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$', 'Administrator', 'views/img/users/default/anonymous.png', 1, '0000-00-00 00:00:00');
```

**Default Login Credentials:**

- **Username:** `admin`
- **Password:** `admin`

#### Additional Required Tables:

```sql
-- Categories Table
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customers Table
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `idDocument` int(11) NOT NULL,
  `email` text NOT NULL,
  `phone` text NOT NULL,
  `address` text NOT NULL,
  `dateOfBirth` date NOT NULL,
  `purchases` int(11) NOT NULL,
  `lastPurchase` datetime NOT NULL,
  `registerDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products Table
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idCategory` int(11) NOT NULL,
  `code` text NOT NULL,
  `description` text NOT NULL,
  `image` text NOT NULL,
  `stock` int(11) NOT NULL,
  `buyingPrice` float NOT NULL,
  `sellingPrice` float NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sales Table
CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(11) NOT NULL,
  `idCustomer` int(11) NOT NULL,
  `idSeller` int(11) NOT NULL,
  `products` text NOT NULL,
  `tax` float NOT NULL,
  `netPrice` float NOT NULL,
  `totalPrice` float NOT NULL,
  `discount` float NOT NULL DEFAULT 0,
  `paymentMethod` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Step 6: Set Permissions (if needed)

1. Ensure the following directories have write permissions:
   - `views/img/users/`
   - `views/img/products/`
   - `extensions/tcpdf/pdf/` (for PDF generation)

### Step 7: Start the Application

1. Open Laragon
2. Start **Apache** and **MySQL** services
3. Visit: `http://localhost/POS-PHP`
4. Login with:
   - **Username:** `admin`
   - **Password:** `admin`

## 🔧 Troubleshooting

### Common Issues:

#### 1. Database Connection Error

**Solution:** Check `models/connection.php` settings match your Laragon configuration

#### 2. Apache Not Starting

**Solution:**

- Check if port 80 is free
- Stop IIS or other web servers
- Run Laragon as Administrator

#### 3. MySQL Not Starting

**Solution:**

- Check if port 3306 is free
- Stop other MySQL services
- Restart Laragon

#### 4. Permission Denied Errors

**Solution:**

- Run Laragon as Administrator
- Check folder permissions for www directory

#### 5. PHP Errors

**Solution:**

- Ensure Laragon is using PHP 7.4 or higher
- Check PHP error logs in Laragon

## 📱 Features Available After Setup

✅ **User Management** - Admin can manage users  
✅ **Product Management** - Add/edit products and categories  
✅ **Customer Management** - Customer database with search  
✅ **Sales Processing** - Multiple payment methods (Cash, QRPH, Card, Installment)  
✅ **Payment Summaries** - Dynamic calculation for all payment types  
✅ **Receipt Generation** - PDF receipts (delivery & acknowledgment)  
✅ **Discount System** - Integrated discount calculations  
✅ **Reports** - Sales reports and analytics  
✅ **Stock Management** - Automatic inventory updates

## 🛡️ Security Notes

1. **Change Default Password:** Immediately change the admin password after first login
2. **Database Security:** Consider setting a MySQL root password in production
3. **File Permissions:** Ensure proper permissions on upload directories
4. **Updates:** Keep Laragon and PHP updated

## 🔄 Moving to Another PC

To deploy this application on another PC:

1. **Install Laragon** on the new PC
2. **Copy Project Folder** to `C:\laragon\www\`
3. **Export Database** from old PC via phpMyAdmin
4. **Import Database** on new PC via phpMyAdmin
5. **Update** `models/connection.php` if database settings differ
6. **Start Services** in Laragon

## 📞 Support

If you encounter any issues:

1. Check Laragon logs in the Laragon interface
2. Verify all services are running (Apache + MySQL)
3. Ensure database connection settings are correct
4. Check PHP error logs for detailed error information

---

**Project Structure:**

```
POS-PHP/
├── controllers/          # Application logic
├── models/              # Database models
├── views/               # User interface
├── extensions/          # Third-party libraries (TCPDF)
├── .htaccess           # Apache configuration
└── index.php           # Main entry point
```

🎉 **You're all set! Your POS system should now be running locally on Laragon.**
