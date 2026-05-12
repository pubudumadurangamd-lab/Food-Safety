<?php
/**
 * Authentication Controller
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Handle user registration
     */
    public function register($data) {
        // Validate CSRF token
        if (!validateCSRFToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        // Validate required fields
        $required = ['full_name', 'email', 'password', 'confirm_password', 'phone', 'role'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
            }
        }

        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }

        // Check if email exists
        if ($this->userModel->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Validate password
        if (strlen($data['password']) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        if ($data['password'] !== $data['confirm_password']) {
            return ['success' => false, 'message' => 'Passwords do not match'];
        }

        // Validate role
        $allowedRoles = ['business_owner', 'worker', 'public_user'];
        if (!in_array($data['role'], $allowedRoles)) {
            return ['success' => false, 'message' => 'Invalid role selected'];
        }

        // Create user
        $userData = [
            'full_name' => sanitize($data['full_name']),
            'email' => sanitize($data['email']),
            'password' => $data['password'],
            'phone' => sanitize($data['phone']),
            'role' => $data['role'],
            'nic' => sanitize($data['nic'] ?? ''),
            'district' => sanitize($data['district'] ?? '')
        ];

        $userId = $this->userModel->create($userData);

        if ($userId) {
            logActivity($userId, 'User Registration', 'New account created');
            return ['success' => true, 'message' => 'Registration successful! Please login.'];
        }

        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }

    /**
     * Handle user login
     */
    public function login($data) {
        // Validate CSRF token
        if (!validateCSRFToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $email = sanitize($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $remember = isset($data['remember_me']);

        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }

        // Check rate limit
        if (!checkRateLimit($email)) {
            return ['success' => false, 'message' => 'Too many failed attempts. Please try again after 15 minutes.'];
        }

        // Find user
        $user = $this->userModel->findByEmail($email);

        if (!$user || $user['status'] !== 'active') {
            recordLoginAttempt($email, false);
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Verify password
        if (!verifyPassword($password, $user['password'])) {
            recordLoginAttempt($email, false);
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Clear login attempts
        clearLoginAttempts($email);

        // Update last login
        $this->userModel->updateLastLogin($user['id']);

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_district'] = $user['district'];
        $_SESSION['login_time'] = time();

        // Regenerate session ID
        regenerateSession();

        // Set remember me cookie
        if ($remember) {
            $token = generateRandomString(32);
            setcookie('remember_token', $token, time() + 30 * 24 * 3600, '/', '', false, true);
            // Store token in database (implementation needed)
        }

        logActivity($user['id'], 'User Login', 'Successful login from ' . $_SERVER['REMOTE_ADDR']);

        return ['success' => true, 'message' => 'Login successful', 'redirect' => $this->getDashboardUrl($user['role'])];
    }

    /**
     * Handle forgot password
     */
    public function forgotPassword($data) {
        if (!validateCSRFToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $email = sanitize($data['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Valid email is required'];
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            // Don't reveal if email exists
            return ['success' => true, 'message' => 'If this email exists, a reset link has been sent.'];
        }

        $token = generateRandomString(32);
        $this->userModel->setResetToken($email, $token);

        // Send reset email
        $resetLink = BASE_URL . "reset-password.php?token=" . $token;
        $subject = "Password Reset - " . SITE_NAME;
        $body = "<h2>Password Reset Request</h2>
                <p>Click the link below to reset your password:</p>
                <p><a href='{$resetLink}'>{$resetLink}</a></p>
                <p>This link expires in 1 hour.</p>
                <p>If you didn't request this, please ignore this email.</p>";

        sendEmail($email, $subject, $body);

        logActivity($user['id'], 'Password Reset Request', 'Reset token generated');

        return ['success' => true, 'message' => 'If this email exists, a reset link has been sent.'];
    }

    /**
     * Handle password reset
     */
    public function resetPassword($data) {
        if (!validateCSRFToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $token = sanitize($data['token'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        if (empty($token)) {
            return ['success' => false, 'message' => 'Invalid reset token'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }

        if ($password !== $confirmPassword) {
            return ['success' => false, 'message' => 'Passwords do not match'];
        }

        $user = $this->userModel->findByResetToken($token);

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid or expired reset token'];
        }

        $this->userModel->update($user['id'], ['password' => $password]);
        $this->userModel->clearResetToken($user['id']);

        logActivity($user['id'], 'Password Reset', 'Password successfully reset');

        return ['success' => true, 'message' => 'Password reset successful! Please login.'];
    }

    /**
     * Handle logout
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            logActivity($_SESSION['user_id'], 'User Logout', 'User logged out');
        }

        // Clear session
        $_SESSION = [];
        session_destroy();

        // Clear remember me cookie
        setcookie('remember_token', '', time() - 3600, '/');

        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * Get dashboard URL based on role
     */
    private function getDashboardUrl($role) {
        $urls = [
            'super_admin' => 'views/admin/dashboard.php',
            'inspector' => 'views/inspector/dashboard.php',
            'business_owner' => 'views/owner/dashboard.php',
            'worker' => 'views/worker/dashboard.php',
            'public_user' => 'views/public/portal.php'
        ];

        return $urls[$role] ?? 'index.php';
    }
}
