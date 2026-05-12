<?php
/**
 * Admin Controller
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Premises.php';
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../models/Worker.php';
require_once __DIR__ . '/../models/MedicalCertificate.php';

class AdminController {
    private $userModel;
    private $premisesModel;
    private $complaintModel;
    private $workerModel;
    private $certificateModel;

    public function __construct() {
        $this->userModel = new User();
        $this->premisesModel = new Premises();
        $this->complaintModel = new Complaint();
        $this->workerModel = new Worker();
        $this->certificateModel = new MedicalCertificate();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $stats = [
            'total_users' => $this->userModel->count(),
            'total_inspectors' => $this->userModel->count('inspector'),
            'total_owners' => $this->userModel->count('business_owner'),
            'total_workers' => $this->userModel->count('worker'),
            'total_public_users' => $this->userModel->count('public_user'),
            'total_premises' => $this->premisesModel->count(),
            'total_complaints' => $this->complaintModel->count(),
            'pending_complaints' => $this->complaintModel->count(['status' => 'pending']),
            'total_workers_reg' => $this->workerModel->count(),
            'expired_certificates' => count($this->certificateModel->getExpired()),
            'expiring_certificates' => count($this->certificateModel->getExpiring(30)),
            'grade_stats' => $this->premisesModel->getGradeStats(),
            'monthly_inspections' => $this->premisesModel->getMonthlyStats(6),
            'complaint_stats' => $this->complaintModel->getStatusStats(),
            'recent_complaints' => $this->complaintModel->getRecent(5)
        ];

        return ['success' => true, 'stats' => $stats];
    }

    /**
     * Manage users
     */
    public function manageUsers($filters = [], $page = 1, $perPage = 20) {
        $users = $this->userModel->getAll($filters['role'] ?? null, $page, $perPage);
        $total = $this->userModel->count($filters['role'] ?? null);
        $pagination = paginate($total, $perPage, $page);

        return [
            'success' => true,
            'users' => $users,
            'pagination' => $pagination
        ];
    }

    /**
     * Create user (admin only)
     */
    public function createUser($data) {
        if (!validateCSRFToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $required = ['full_name', 'email', 'password', 'phone', 'role'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
            }
        }

        if ($this->userModel->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

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
            logActivity(getCurrentUserId(), 'User Created by Admin', "Created user ID: {$userId} with role: {$data['role']}");
            return ['success' => true, 'message' => 'User created successfully'];
        }

        return ['success' => false, 'message' => 'Failed to create user'];
    }

    /**
     * Toggle user status
     */
    public function toggleUserStatus($id) {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        if ($this->userModel->toggleStatus($id)) {
            logActivity(getCurrentUserId(), 'User Status Toggled', "User ID: {$id}");
            return ['success' => true, 'message' => 'User status updated'];
        }

        return ['success' => false, 'message' => 'Failed to update status'];
    }

    /**
     * Delete user
     */
    public function deleteUser($id) {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        if ($id == getCurrentUserId()) {
            return ['success' => false, 'message' => 'Cannot delete yourself'];
        }

        if ($this->userModel->delete($id)) {
            logActivity(getCurrentUserId(), 'User Deleted', "Deleted user ID: {$id}");
            return ['success' => true, 'message' => 'User deleted successfully'];
        }

        return ['success' => false, 'message' => 'Failed to delete user'];
    }

    /**
     * Get activity logs
     */
    public function getActivityLogs($page = 1, $perPage = 50) {
        $db = Database::getInstance()->getConnection();
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("
            SELECT al.*, u.full_name as user_name
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $logs = $stmt->fetchAll();

        $countStmt = $db->query("SELECT COUNT(*) as count FROM activity_logs");
        $total = $countStmt->fetch()['count'];
        $pagination = paginate($total, $perPage, $page);

        return [
            'success' => true,
            'logs' => $logs,
            'pagination' => $pagination
        ];
    }

    /**
     * Export data to Excel
     */
    public function exportToExcel($type) {
        switch ($type) {
            case 'premises':
                $data = $this->premisesModel->getAll([], 1, 10000);
                $headers = ['ID', 'Shop Name', 'Owner', 'Address', 'District', 'Business Type', 'Grade', 'Status', 'License Number', 'Inspection Date', 'Expiry Date'];
                $exportData = [];
                foreach ($data as $row) {
                    $exportData[] = [
                        $row['id'], $row['shop_name'], $row['owner_name'], $row['address'],
                        $row['district'], $row['business_type'], $row['grade'], $row['status'],
                        $row['license_number'], $row['inspection_date'], $row['expiry_date']
                    ];
                }
                exportToExcel($exportData, $headers, 'premises_export.xlsx');
                break;

            case 'complaints':
                $data = $this->complaintModel->getAll([], 1, 10000);
                $headers = ['ID', 'Shop Name', 'Category', 'Description', 'Status', 'Created At'];
                $exportData = [];
                foreach ($data as $row) {
                    $exportData[] = [
                        $row['id'], $row['shop_name'], $row['category'],
                        substr($row['description'], 0, 100), $row['status'], $row['created_at']
                    ];
                }
                exportToExcel($exportData, $headers, 'complaints_export.xlsx');
                break;

            case 'workers':
                $data = $this->workerModel->getAll([], 1, 10000);
                $headers = ['ID', 'Name', 'NIC', 'Phone', 'Workplace', 'Designation', 'Status'];
                $exportData = [];
                foreach ($data as $row) {
                    $exportData[] = [
                        $row['id'], $row['full_name'], $row['nic'], $row['phone'],
                        $row['workplace_name'], $row['designation'], $row['status']
                    ];
                }
                exportToExcel($exportData, $headers, 'workers_export.xlsx');
                break;
        }
    }

    /**
     * Backup database
     */
    public function backupDatabase() {
        $dbName = DB_NAME;
        $backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $backupPath = __DIR__ . '/../database/backups/' . $backupFile;

        if (!is_dir(__DIR__ . '/../database/backups/')) {
            mkdir(__DIR__ . '/../database/backups/', 0755, true);
        }

        $command = "mysqldump -h " . DB_HOST . " -u " . DB_USER . " " . (DB_PASS ? "-p" . DB_PASS . " " : "") . $dbName . " > " . $backupPath;
        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            logActivity(getCurrentUserId(), 'Database Backup', "Backup created: {$backupFile}");
            return ['success' => true, 'message' => 'Database backup created', 'file' => $backupFile];
        }

        return ['success' => false, 'message' => 'Backup failed'];
    }
}
