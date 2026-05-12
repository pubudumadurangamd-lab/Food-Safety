<?php
/**
 * Worker Model
 * Food Safety & Premises Grading Management System
 */

class Worker {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new worker
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO workers (
                user_id, full_name, nic, phone, address, workplace_id,
                designation, status, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())
        ");

        $stmt->execute([
            $data['user_id'] ?? null,
            $data['full_name'],
            $data['nic'],
            $data['phone'],
            $data['address'] ?? null,
            $data['workplace_id'] ?? null,
            $data['designation'] ?? null,
            $data['created_by']
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Find worker by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT w.*, p.shop_name as workplace_name, u.full_name as created_by_name
            FROM workers w
            LEFT JOIN premises p ON w.workplace_id = p.id
            LEFT JOIN users u ON w.created_by = u.id
            WHERE w.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Find worker by NIC
     */
    public function findByNIC($nic) {
        $stmt = $this->db->prepare("
            SELECT w.*, p.shop_name as workplace_name
            FROM workers w
            LEFT JOIN premises p ON w.workplace_id = p.id
            WHERE w.nic = ? LIMIT 1
        ");
        $stmt->execute([$nic]);
        return $stmt->fetch();
    }

    /**
     * Update worker
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];

        $allowedFields = ['full_name', 'nic', 'phone', 'address', 'workplace_id', 'designation', 'status'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }

        $values[] = $id;

        $sql = "UPDATE workers SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Delete worker
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM workers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get all workers
     */
    public function getAll($filters = [], $page = 1, $perPage = 20) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['workplace_id'])) {
            $where[] = "workplace_id = ?";
            $params[] = $filters['workplace_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(full_name LIKE ? OR nic LIKE ?)";
            $search = "%" . $filters['search'] . "%";
            $params[] = $search;
            $params[] = $search;
        }

        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT w.*, p.shop_name as workplace_name
            FROM workers w
            LEFT JOIN premises p ON w.workplace_id = p.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY w.created_at DESC LIMIT ? OFFSET ?
        ";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count workers
     */
    public function count($filters = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        $sql = "SELECT COUNT(*) as count FROM workers WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'];
    }

    /**
     * Get workers with expired certificates
     */
    public function getExpiredCertificates($days = 30) {
        $stmt = $this->db->prepare("
            SELECT w.*, p.shop_name as workplace_name, mc.expiry_date, mc.status as cert_status
            FROM workers w
            LEFT JOIN premises p ON w.workplace_id = p.id
            LEFT JOIN medical_certificates mc ON w.id = mc.worker_id
            WHERE mc.expiry_date < DATE_ADD(NOW(), INTERVAL ? DAY)
            AND mc.status = 'valid'
            ORDER BY mc.expiry_date
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    /**
     * Get worker statistics
     */
    public function getStats() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
            FROM workers
        ");
        return $stmt->fetch();
    }
}
