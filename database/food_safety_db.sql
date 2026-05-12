-- ============================================
-- Food Safety & Premises Grading Management System
-- MySQL Database Schema
-- Sri Lanka Health Department
-- Version: 1.0.0
-- ============================================

CREATE DATABASE IF NOT EXISTS food_safety_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE food_safety_db;

-- ============================================
-- 1. USERS TABLE
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('super_admin', 'inspector', 'business_owner', 'worker', 'public_user') NOT NULL DEFAULT 'public_user',
    nic VARCHAR(20),
    district VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    profile_image VARCHAR(255),
    reset_token VARCHAR(64),
    reset_expires DATETIME,
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_district (district)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. PREMISES TABLE
-- ============================================
CREATE TABLE premises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_name VARCHAR(255) NOT NULL,
    owner_name VARCHAR(255) NOT NULL,
    owner_id INT,
    address TEXT NOT NULL,
    gps_lat DECIMAL(10, 8),
    gps_lng DECIMAL(11, 8),
    contact_number VARCHAR(20) NOT NULL,
    business_type VARCHAR(100) NOT NULL,
    license_number VARCHAR(100) NOT NULL,
    inspection_date DATE,
    expiry_date DATE,
    grade ENUM('A', 'B', 'C', 'D') DEFAULT 'D',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    district VARCHAR(50),
    photos JSON,
    qr_code VARCHAR(255),
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_grade (grade),
    INDEX idx_district (district),
    INDEX idx_status (status),
    INDEX idx_business_type (business_type),
    INDEX idx_license (license_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. INSPECTIONS TABLE
-- ============================================
CREATE TABLE inspections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    premises_id INT NOT NULL,
    inspector_id INT NOT NULL,
    inspection_date DATE NOT NULL,
    cleanliness TINYINT NOT NULL CHECK (cleanliness BETWEEN 1 AND 5),
    food_storage TINYINT NOT NULL CHECK (food_storage BETWEEN 1 AND 5),
    employee_hygiene TINYINT NOT NULL CHECK (employee_hygiene BETWEEN 1 AND 5),
    waste_management TINYINT NOT NULL CHECK (waste_management BETWEEN 1 AND 5),
    pest_control TINYINT NOT NULL CHECK (pest_control BETWEEN 1 AND 5),
    water_supply TINYINT NOT NULL CHECK (water_supply BETWEEN 1 AND 5),
    food_handling TINYINT NOT NULL CHECK (food_handling BETWEEN 1 AND 5),
    kitchen_condition TINYINT NOT NULL CHECK (kitchen_condition BETWEEN 1 AND 5),
    temperature_control TINYINT NOT NULL CHECK (temperature_control BETWEEN 1 AND 5),
    total_score DECIMAL(5, 2),
    grade ENUM('A', 'B', 'C', 'D') NOT NULL,
    notes TEXT,
    photos JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (premises_id) REFERENCES premises(id) ON DELETE CASCADE,
    FOREIGN KEY (inspector_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_premises (premises_id),
    INDEX idx_inspector (inspector_id),
    INDEX idx_inspection_date (inspection_date),
    INDEX idx_grade (grade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. COMPLAINTS TABLE
-- ============================================
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    premises_id INT,
    shop_name VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    location TEXT,
    contact_details VARCHAR(255) NOT NULL,
    photos JSON,
    status ENUM('pending', 'under_investigation', 'resolved', 'rejected') DEFAULT 'pending',
    resolution TEXT,
    resolved_by INT,
    resolved_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (premises_id) REFERENCES premises(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. WORKERS TABLE
-- ============================================
CREATE TABLE workers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    full_name VARCHAR(255) NOT NULL,
    nic VARCHAR(20) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    workplace_id INT,
    designation VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (workplace_id) REFERENCES premises(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_nic (nic),
    INDEX idx_workplace (workplace_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. MEDICAL CERTIFICATES TABLE
-- ============================================
CREATE TABLE medical_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    worker_id INT NOT NULL,
    certificate_number VARCHAR(100) NOT NULL,
    issue_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    medical_status ENUM('fit', 'unfit', 'conditional') DEFAULT 'fit',
    notes TEXT,
    document_path VARCHAR(255),
    status ENUM('valid', 'expired', 'revoked') DEFAULT 'valid',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_worker (worker_id),
    INDEX idx_expiry (expiry_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. ACTIVITY LOGS TABLE
-- ============================================
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. LOGIN ATTEMPTS TABLE (Security)
-- ============================================
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    success TINYINT(1) DEFAULT 0,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_identifier (identifier),
    INDEX idx_ip (ip_address),
    INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'danger') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Super Admin
INSERT INTO users (full_name, email, password, phone, role, district, status) VALUES
('System Administrator', 'admin@foodsafety.gov.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0771234567', 'super_admin', 'Colombo', 'active');

-- Sample Inspectors
INSERT INTO users (full_name, email, password, phone, role, district, status) VALUES
('Dr. Priyantha Perera', 'phi.colombo@foodsafety.gov.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0772345678', 'inspector', 'Colombo', 'active'),
('Ms. Kumari Silva', 'phi.gampaha@foodsafety.gov.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0773456789', 'inspector', 'Gampaha', 'active'),
('Mr. Nimal Fernando', 'phi.kandy@foodsafety.gov.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0774567890', 'inspector', 'Kandy', 'active');

-- Sample Business Owners
INSERT INTO users (full_name, email, password, phone, role, district, status) VALUES
('Mr. Sunil Rajapaksa', 'sunil@sunilrestaurant.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0775678901', 'business_owner', 'Colombo', 'active'),
('Mrs. Malini Wijesinghe', 'malini@malinibakery.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0776789012', 'business_owner', 'Gampaha', 'active');

-- Sample Workers
INSERT INTO users (full_name, email, password, phone, role, district, status) VALUES
('Mr. Ravi Kumar', 'ravi.kumar@email.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0777890123', 'worker', 'Colombo', 'active'),
('Ms. Shalini Perera', 'shalini.p@email.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0778901234', 'worker', 'Colombo', 'active');

-- Sample Public Users
INSERT INTO users (full_name, email, password, phone, role, district, status) VALUES
('Mr. Kasun Bandara', 'kasun@email.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0779012345', 'public_user', 'Colombo', 'active'),
('Mrs. Anjali De Silva', 'anjali@email.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0770123456', 'public_user', 'Kandy', 'active');

-- Sample Premises
INSERT INTO premises (shop_name, owner_name, owner_id, address, gps_lat, gps_lng, contact_number, business_type, license_number, inspection_date, expiry_date, grade, district, created_by) VALUES
('Sunil Restaurant', 'Mr. Sunil Rajapaksa', 4, '123 Galle Road, Colombo 03', 6.9271, 79.8612, '0112345678', 'Restaurant', 'FS-2024-001', '2024-01-15', '2025-01-15', 'A', 'Colombo', 2),
('Malini Bakery', 'Mrs. Malini Wijesinghe', 5, '45 Main Street, Gampaha', 7.0917, 80.0000, '0332345678', 'Bakery', 'FS-2024-002', '2024-02-20', '2025-02-20', 'B', 'Gampaha', 3),
('Kandy Spice Garden', 'Mr. Nimal Silva', NULL, '78 Temple Road, Kandy', 7.2906, 80.6337, '0812345678', 'Restaurant', 'FS-2024-003', '2024-03-10', '2025-03-10', 'C', 'Kandy', 4),
('Colombo Fast Food', 'Ms. Priya Fernando', NULL, '12 Union Place, Colombo 02', 6.9344, 79.8533, '0113456789', 'Fast Food', 'FS-2024-004', '2024-04-05', '2025-04-05', 'B', 'Colombo', 2),
('Galle Sea View Hotel', 'Mr. Chaminda Perera', NULL, '89 Beach Road, Galle', 6.0329, 80.2168, '0912345678', 'Hotel', 'FS-2024-005', '2024-05-12', '2025-05-12', 'A', 'Galle', 3);

-- Sample Inspections
INSERT INTO inspections (premises_id, inspector_id, inspection_date, cleanliness, food_storage, employee_hygiene, waste_management, pest_control, water_supply, food_handling, kitchen_condition, temperature_control, total_score, grade, notes) VALUES
(1, 2, '2024-01-15', 5, 5, 5, 5, 5, 5, 5, 4, 5, 96.00, 'A', 'Excellent premises. All standards met. Staff well-trained in hygiene practices.'),
(2, 3, '2024-02-20', 4, 4, 4, 3, 4, 4, 4, 3, 4, 78.00, 'B', 'Good overall. Minor improvements needed in waste management and kitchen condition.'),
(3, 4, '2024-03-10', 3, 3, 3, 3, 3, 4, 3, 3, 3, 62.00, 'C', 'Satisfactory. Needs improvement in multiple areas including pest control and food handling.'),
(4, 2, '2024-04-05', 4, 4, 4, 4, 4, 4, 4, 3, 4, 80.00, 'B', 'Good premises. Temperature control needs minor attention.'),
(5, 3, '2024-05-12', 5, 5, 5, 5, 5, 5, 5, 5, 5, 100.00, 'A', 'Outstanding! Exemplary food safety standards. Best practices observed throughout.');

-- Sample Workers
INSERT INTO workers (user_id, full_name, nic, phone, address, workplace_id, designation, created_by) VALUES
(6, 'Mr. Ravi Kumar', '198512345678', '0777890123', '45 Temple Road, Colombo', 1, 'Head Chef', 2),
(7, 'Ms. Shalini Perera', '199023456789', '0778901234', '12 Galle Road, Colombo', 1, 'Kitchen Assistant', 2),
(NULL, 'Mr. Amal Silva', '198834567890', '0779012345', '78 Main Street, Gampaha', 2, 'Baker', 3);

-- Sample Medical Certificates
INSERT INTO medical_certificates (worker_id, certificate_number, issue_date, expiry_date, medical_status, notes, created_by) VALUES
(1, 'MC-2024-001', '2024-01-01', '2025-01-01', 'fit', 'All clear. No communicable diseases.', 2),
(2, 'MC-2024-002', '2024-02-15', '2025-02-15', 'fit', 'Healthy. Regular checkup completed.', 2),
(3, 'MC-2024-003', '2024-03-01', '2025-03-01', 'fit', 'Fit for food handling work.', 3);

-- Sample Complaints
INSERT INTO complaints (user_id, premises_id, shop_name, category, description, location, contact_details, status) VALUES
(8, 3, 'Kandy Spice Garden', 'unhygienic_conditions', 'Observed cockroaches in kitchen area during visit on 2024-04-15. Food was left uncovered.', '78 Temple Road, Kandy', '0779012345', 'under_investigation'),
(9, NULL, 'Unknown Street Vendor', 'food_poisoning', 'Bought kottu from street vendor near bus stand. Got food poisoning within 2 hours.', 'Kandy Bus Stand', '0770123456', 'pending'),
(8, 1, 'Sunil Restaurant', 'employee_hygiene_issue', 'Chef was not wearing hairnet and gloves while preparing food.', '123 Galle Road, Colombo 03', '0779012345', 'resolved');

-- Sample Activity Logs
INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES
(1, 'System Initialization', 'Database initialized with sample data', '127.0.0.1'),
(2, 'Inspector Login', 'PHI Colombo logged in', '192.168.1.100'),
(4, 'Premises Created', 'Created premises ID: 1', '192.168.1.101'),
(2, 'Inspection Created', 'Inspection ID: 1 for premises ID: 1', '192.168.1.100'),
(8, 'Complaint Submitted', 'Complaint ID: 1', '192.168.1.102');

-- ============================================
-- VIEWS FOR REPORTING
-- ============================================

CREATE VIEW vw_premises_summary AS
SELECT 
    p.id,
    p.shop_name,
    p.owner_name,
    p.district,
    p.grade,
    p.status,
    p.inspection_date,
    p.expiry_date,
    u.full_name as inspector_name,
    CASE 
        WHEN p.expiry_date < CURDATE() THEN 'Expired'
        WHEN p.expiry_date < DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Expiring Soon'
        ELSE 'Valid'
    END as validity_status
FROM premises p
LEFT JOIN users u ON p.created_by = u.id;

CREATE VIEW vw_worker_medical_status AS
SELECT 
    w.id,
    w.full_name,
    w.nic,
    w.phone,
    p.shop_name as workplace,
    mc.certificate_number,
    mc.expiry_date,
    mc.medical_status,
    mc.status as cert_status,
    CASE 
        WHEN mc.expiry_date < CURDATE() THEN 'Expired'
        WHEN mc.expiry_date < DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Expiring Soon'
        ELSE 'Valid'
    END as validity
FROM workers w
LEFT JOIN premises p ON w.workplace_id = p.id
LEFT JOIN medical_certificates mc ON w.id = mc.worker_id AND mc.status = 'valid'
WHERE w.status = 'active';

CREATE VIEW vw_complaint_summary AS
SELECT 
    c.id,
    c.shop_name,
    c.category,
    c.status,
    c.created_at,
    u.full_name as complainant,
    ru.full_name as resolved_by_name,
    c.resolved_at,
    DATEDIFF(COALESCE(c.resolved_at, CURDATE()), c.created_at) as days_open
FROM complaints c
LEFT JOIN users u ON c.user_id = u.id
LEFT JOIN users ru ON c.resolved_by = ru.id;

-- ============================================
-- STORED PROCEDURES
-- ============================================

DELIMITER //

CREATE PROCEDURE sp_get_dashboard_stats()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM premises WHERE status = 'active') as total_premises,
        (SELECT COUNT(*) FROM users WHERE status = 'active') as total_users,
        (SELECT COUNT(*) FROM complaints WHERE status = 'pending') as pending_complaints,
        (SELECT COUNT(*) FROM medical_certificates WHERE status = 'valid' AND expiry_date < DATE_ADD(CURDATE(), INTERVAL 30 DAY)) as expiring_certs;
END //

CREATE PROCEDURE sp_auto_expire_certificates()
BEGIN
    UPDATE medical_certificates 
    SET status = 'expired' 
    WHERE expiry_date < CURDATE() AND status = 'valid';

    SELECT ROW_COUNT() as updated_count;
END //

CREATE PROCEDURE sp_get_premises_by_grade(IN p_grade CHAR(1))
BEGIN
    SELECT * FROM premises WHERE grade = p_grade AND status = 'active' ORDER BY district, shop_name;
END //

DELIMITER ;

-- ============================================
-- TRIGGERS
-- ============================================

DELIMITER //

CREATE TRIGGER trg_after_inspection_insert
AFTER INSERT ON inspections
FOR EACH ROW
BEGIN
    UPDATE premises 
    SET grade = NEW.grade, inspection_date = NEW.inspection_date 
    WHERE id = NEW.premises_id;
END //

CREATE TRIGGER trg_after_premises_insert
AFTER INSERT ON premises
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, action, details, created_at)
    VALUES (NEW.created_by, 'Premises Registered', CONCAT('Premises ID: ', NEW.id, ' - ', NEW.shop_name), NOW());
END //

DELIMITER ;

-- ============================================
-- END OF DATABASE SCHEMA
-- ============================================
