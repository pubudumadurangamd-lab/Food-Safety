<?php
/**
 * Main Configuration File
 * Food Safety & Premises Grading Management System
 */

session_start();

// Base URL - Change this to your domain
define('BASE_URL', 'http://localhost/food-safety-system/');
define('SITE_NAME', 'Food Safety & Premises Grading Management System');
define('SITE_SHORT', 'FSPGMS');
define('VERSION', '1.0.0');

// Timezone
date_default_timezone_set('Asia/Colombo');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security Settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes

// File Upload Settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');

// Email Settings (Configure with your SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('FROM_EMAIL', 'noreply@foodsafety.gov.lk');
define('FROM_NAME', 'Food Safety Department - Sri Lanka');

// Grade Calculation Weights (H800 Criteria)
define('GRADE_WEIGHTS', [
    'cleanliness' => 15,
    'food_storage' => 15,
    'employee_hygiene' => 15,
    'waste_management' => 10,
    'pest_control' => 10,
    'water_supply' => 10,
    'food_handling' => 15,
    'kitchen_condition' => 5,
    'temperature_control' => 5
]);

// Grade Thresholds
define('GRADE_THRESHOLDS', [
    'A' => 90,
    'B' => 75,
    'C' => 60,
    'D' => 0
]);

// User Roles
define('ROLES', [
    'super_admin' => 1,
    'inspector' => 2,
    'business_owner' => 3,
    'worker' => 4,
    'public_user' => 5
]);

// Complaint Categories
define('COMPLAINT_CATEGORIES', [
    'food_poisoning' => 'Food Poisoning',
    'unhygienic_conditions' => 'Unhygienic Conditions',
    'expired_food' => 'Expired Food Products',
    'pest_infestation' => 'Pest Infestation',
    'fake_license' => 'Fake License',
    'employee_hygiene_issue' => 'Employee Hygiene Issue',
    'other' => 'Other'
]);

// Include database
require_once __DIR__ . '/database.php';

// Include helper functions
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/security.php';
