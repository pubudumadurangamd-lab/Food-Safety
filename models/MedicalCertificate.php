<?php
/**
 * Medical Certificate Model
 * Food Safety & Premises Grading Management System
 */

class MedicalCertificate {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new medical certificate
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO medical_certificates (
                worker_id, certificate_number, issue_date, expiry_date,
                medical_status, notes, document_path, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $data['worker_id'],
            $data['certificate_number'],
            $data['issue_date'],
            $data['expiry_date'],
            $data['medical_status'],
            $data['notes'] ?? null,
            $data['document_path'] ?? null,
            $data['created_by']
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Find certificate by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT mc.*, w.full_name as worker_name, w.nic as worker_nic,
                   p.shop_name as workplace_name, u.full_name as created_by_name
            FROM medical_certificates mc
            JOIN workers w ON mc.worker_id = w.id
            LEFT JOIN premises p ON w.workplace_id = p.id
            JOIN users u ON mc.created_by = u.id
            WHERE mc.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get certificates by worker
     */
    public function getByWorker($workerId) {
        $stmt = $this->db->prepare("
            SELECT mc.*, u.full_name as created_by_name
            FROM medical_certificates mc
            JOIN users u ON mc.created_by = u.id
            WHERE mc.worker_id = ?
            ORDER BY mc.created_at DESC
        ");
        $stmt->execute([$workerId]);
        return $stmt->fetchAll();
    }

    /**
     * Get latest certificate by worker
     */
    public function getLatestByWorker($workerId) {
        $stmt = $this->db->prepare("
            SELECT * FROM medical_certificates
            WHERE worker_id = ?
            ORDER BY expiry_date DESC
            LIMIT 1
        ");
        $stmt->execute([$workerId]);
        return $stmt->fetch();
    }

    /**
     * Update certificate status
     */
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE medical_certificates SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    /**
     * Get all certificates with filters
     */
    public function getAll($filters = [], $page = 1, $perPage = 20) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "mc.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['expiring_soon'])) {
            $where[] = "mc.expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)";
        }

        if (!empty($filters['expired'])) {
            $where[] = "mc.expiry_date < NOW()";
        }

        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT mc.*, w.full_name as worker_name, w.nic as worker_nic,
                   p.shop_name as workplace_name
            FROM medical_certificates mc
            JOIN workers w ON mc.worker_id = w.id
            LEFT JOIN premises p ON w.workplace_id = p.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY mc.expiry_date ASC LIMIT ? OFFSET ?
        ";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count certificates
     */
    public function count($filters = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        $sql = "SELECT COUNT(*) as count FROM medical_certificates WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'];
    }

    /**
     * Get expiring certificates
     */
    public function getExpiring($days = 30) {
        $stmt = $this->db->prepare("
            SELECT mc.*, w.full_name as worker_name, w.nic as worker_nic,
                   p.shop_name as workplace_name
            FROM medical_certificates mc
            JOIN workers w ON mc.worker_id = w.id
            LEFT JOIN premises p ON w.workplace_id = p.id
            WHERE mc.expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)
            AND mc.status = 'valid'
            ORDER BY mc.expiry_date ASC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    /**
     * Get expired certificates
     */
    public function getExpired() {
        $stmt = $this->db->query("
            SELECT mc.*, w.full_name as worker_name, w.nic as worker_nic,
                   p.shop_name as workplace_name
            FROM medical_certificates mc
            JOIN workers w ON mc.worker_id = w.id
            LEFT JOIN premises p ON w.workplace_id = p.id
            WHERE mc.expiry_date < NOW()
            AND mc.status = 'valid'
            ORDER BY mc.expiry_date DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Auto update expired certificates status
     */
    public function autoUpdateExpired() {
        $stmt = $this->db->query("
            UPDATE medical_certificates 
            SET status = 'expired' 
            WHERE expiry_date < NOW() AND status = 'valid'
        ");
        return $stmt->rowCount();
    }

    /**
     * Get certificate statistics
     */
    public function getStats() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'valid' THEN 1 ELSE 0 END) as valid,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as expiring_soon
            FROM medical_certificates
        ");
        return $stmt->fetch();
    }
}
