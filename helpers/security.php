<?php
/**
 * Security Helper Functions
 * Food Safety & Premises Grading Management System
 */

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Validate CSRF Token
 */
function validateCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Get CSRF Token Field
 */
function csrfField() {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCSRFToken() . '">';
}

/**
 * Sanitize input to prevent XSS
 */
function xssClean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Prevent SQL Injection using prepared statements
 * This is handled by PDO prepared statements in models
 */

/**
 * Rate limiting for login attempts
 */
function checkRateLimit($identifier) {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE identifier = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([$identifier, LOCKOUT_TIME]);
    $result = $stmt->fetch();

    if ($result['attempts'] >= MAX_LOGIN_ATTEMPTS) {
        return false;
    }

    return true;
}

/**
 * Record login attempt
 */
function recordLoginAttempt($identifier, $success = false) {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("INSERT INTO login_attempts (identifier, ip_address, success, attempted_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$identifier, $_SERVER['REMOTE_ADDR'], $success ? 1 : 0]);
}

/**
 * Clear login attempts
 */
function clearLoginAttempts($identifier) {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("DELETE FROM login_attempts WHERE identifier = ?");
    $stmt->execute([$identifier]);
}

/**
 * Secure password hashing
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Regenerate session ID to prevent session fixation
 */
function regenerateSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Validate file upload
 */
function validateFileUpload($file) {
    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload failed with error code: ' . $file['error'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = 'File size exceeds 5MB limit';
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        $errors[] = 'Invalid file type. Only JPG, PNG, and GIF allowed';
    }

    // Check for PHP code in images
    $content = file_get_contents($file['tmp_name']);
    if (preg_match('/<\?php|<\?=|<\?/i', $content)) {
        $errors[] = 'File contains potentially malicious code';
    }

    return $errors;
}

/**
 * Secure headers
 */
function setSecurityHeaders() {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; img-src 'self' data: blob:; font-src 'self' cdn.jsdelivr.net;");
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * JSON response for AJAX
 */
function jsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

/**
 * Require authentication
 */
function requireAuth() {
    if (!isLoggedIn()) {
        setFlashMessage('danger', 'Please login to access this page');
        redirect('login.php');
    }
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireAuth();
    if (!hasRole($role)) {
        setFlashMessage('danger', 'You do not have permission to access this page');
        redirect('dashboard.php');
    }
}

/**
 * Require minimum role level
 */
function requireMinimumRole($roleLevel) {
    requireAuth();
    if (!hasMinimumRole($roleLevel)) {
        setFlashMessage('danger', 'You do not have permission to access this page');
        redirect('dashboard.php');
    }
}
