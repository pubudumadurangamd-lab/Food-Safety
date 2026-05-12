# Food Safety & Premises Grading Management System
## Installation Guide - Sri Lanka Health Department

### System Requirements
- PHP 8.0 or higher
- MySQL 5.7 or higher (or MariaDB 10.3+)
- Apache/Nginx web server
- SSL certificate (recommended for production)
- Minimum 512MB RAM, 1GB recommended
- 500MB disk space

### Required PHP Extensions
- PDO (with MySQL driver)
- GD/ImageMagick (for image processing)
- Fileinfo
- MBString
- OpenSSL
- JSON
- Session

### Installation Steps

#### Step 1: Download & Extract
1. Download the system files
2. Extract to your web server directory (e.g., `/var/www/html/food-safety-system/`)
3. Ensure the web server has read/write permissions

#### Step 2: Create Upload Directories
Ensure these directories exist and are writable by the web server:
```bash
chmod 755 assets/uploads/
chmod 755 assets/uploads/premises/
chmod 755 assets/uploads/complaints/
chmod 755 assets/uploads/medical/
chmod 755 assets/uploads/profiles/
chmod 755 assets/uploads/qrcodes/
chmod 755 database/backups/
```

#### Step 3: Database Setup
1. Create a MySQL database named `food_safety_db`
2. Import the SQL file:
   ```bash
   mysql -u root -p food_safety_db < database/food_safety_db.sql
   ```
3. Or use phpMyAdmin to import `database/food_safety_db.sql`

#### Step 4: Configuration
Edit `config/config.php` and update:
```php
define('BASE_URL', 'http://your-domain.com/food-safety-system/');
```

Edit `config/database.php` and update database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'food_safety_db');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');
```

#### Step 5: Email Configuration (Optional)
For password reset and notifications, configure SMTP in `config/config.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
```

#### Step 6: Install Dependencies (Optional)
For PDF generation and Excel export, install Composer dependencies:
```bash
composer require phpmailer/phpmailer
composer require tecnickcom/tcpdf
composer require phpoffice/phpspreadsheet
composer require endroid/qr-code
```

Or download manually to `vendor/` directory.

#### Step 7: Security Setup
1. Ensure `.htaccess` protects sensitive directories
2. Set proper file permissions:
   ```bash
   find . -type f -exec chmod 644 {} \;
   find . -type d -exec chmod 755 {} \;
   ```
3. Enable HTTPS in production
4. Configure firewall rules

#### Step 8: Access the System
- Public Portal: `http://your-domain.com/food-safety-system/`
- Login: `http://your-domain.com/food-safety-system/login.php`

### Default Login Credentials
| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@foodsafety.gov.lk | password |
| Inspector (Colombo) | phi.colombo@foodsafety.gov.lk | password |
| Inspector (Gampaha) | phi.gampaha@foodsafety.gov.lk | password |
| Inspector (Kandy) | phi.kandy@foodsafety.gov.lk | password |
| Business Owner | sunil@sunilrestaurant.lk | password |
| Worker | ravi.kumar@email.lk | password |
| Public User | kasun@email.lk | password |

**IMPORTANT:** Change all default passwords immediately after first login!

### System Architecture
```
food-safety-system/
├── config/           # Configuration files
│   ├── config.php    # Main configuration
│   └── database.php  # Database connection
├── controllers/      # Business logic controllers
│   ├── AuthController.php
│   ├── PremisesController.php
│   ├── InspectionController.php
│   ├── ComplaintController.php
│   ├── WorkerController.php
│   └── AdminController.php
├── models/           # Database models
│   ├── User.php
│   ├── Premises.php
│   ├── Inspection.php
│   ├── Complaint.php
│   ├── Worker.php
│   └── MedicalCertificate.php
├── views/            # View templates
│   ├── admin/        # Admin dashboard views
│   ├── inspector/    # Inspector views
│   ├── owner/        # Business owner views
│   ├── worker/       # Worker views
│   ├── public/       # Public portal views
│   └── partials/     # Shared components
├── helpers/          # Helper functions
│   ├── functions.php
│   └── security.php
├── assets/           # Static assets
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/      # User uploads
├── database/         # Database files
│   ├── food_safety_db.sql
│   └── backups/      # Backup directory
├── api/              # API endpoints
├── vendor/           # Third-party libraries
├── index.php         # Public portal
├── login.php         # Login page
├── register.php      # Registration page
├── logout.php        # Logout handler
├── verify.php        # QR verification
└── .htaccess         # Apache configuration
```

### Security Features
- Password hashing with Argon2id
- CSRF token protection on all forms
- XSS output encoding
- SQL injection prevention via PDO prepared statements
- Rate limiting on login attempts
- Secure file upload validation
- Session security with regeneration
- Activity logging
- Role-based access control (RBAC)

### Grade Calculation (H800 Criteria)
The system uses weighted scoring based on H800 Food Safety Guide:

| Criteria | Weight | Max Score |
|----------|--------|-----------|
| Cleanliness | 15% | 5 |
| Food Storage | 15% | 5 |
| Employee Hygiene | 15% | 5 |
| Waste Management | 10% | 5 |
| Pest Control | 10% | 5 |
| Water Supply | 10% | 5 |
| Food Handling | 15% | 5 |
| Kitchen Condition | 5% | 5 |
| Temperature Control | 5% | 5 |

**Grade Thresholds:**
- A: 90-100% (Excellent)
- B: 75-89% (Good)
- C: 60-74% (Satisfactory)
- D: Below 60% (Needs Improvement)

### Support
For technical support, contact:
- Ministry of Health, Sri Lanka
- Email: support@foodsafety.gov.lk
- Phone: +94 11 267XXXX

### License
This system is proprietary software of the Government of Sri Lanka.
Unauthorized distribution is prohibited.
