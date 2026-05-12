<?php
/**
 * Complaint Model
 * Food Safety & Premises Grading Management System
 */

class Complaint {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new complaint
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO complaints (
                user_id, premises_id, shop_name, category, description,
                location, contact_details, photos, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");

        $stmt->execute([
            $data['user_id'],
            $data['premises_id'] ?? null,
            $data['shop_name'],
            $data['category'],
            $data['description'],
            $data['location'] ?? null,
            $data['contact_details'],
            json_encode($data['photos'] ?? [])
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Find complaint by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.full_name as complainant_name, u.email as complainant_email
            FROM complaints c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        $complaint = $stmt->fetch();

        if ($complaint) {
            $complaint['photos'] = json_decode($complaint['photos'], true) ?? [];
        }

        return $complaint;
    }

    /**
     * Update complaint status
     */
    public function updateStatus($id, $status, $resolution = null, $resolvedBy = null) {
        $stmt = $this->db->prepare("
            UPDATE complaints 
            SET status = ?, resolution = ?, resolved_by = ?, resolved_at = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$status, $resolution, $resolvedBy, $id]);
    }

    /**
     * Get complaints by user
     */
    public function getByUser($userId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare("
            SELECT * FROM complaints
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$userId, $perPage, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get all complaints with filters
     */
    public function getAll($filters = [], $page = 1, $perPage = 20) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['category'])) {
            $where[] = "category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(shop_name LIKE ? OR description LIKE ?)";
            $search = "%" . $filters['search'] . "%";
            $params[] = $search;
            $params[] = $search;
        }

        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT c.*, u.full_name as complainant_name
            FROM complaints c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY c.created_at DESC LIMIT ? OFFSET ?
        ";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count complaints
     */
    public function count($filters = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        $sql = "SELECT COUNT(*) as count FROM complaints WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'];
    }

    /**
     * Get status statistics
     */
    public function getStatusStats() {
        $stmt = $this->db->query("
            SELECT status, COUNT(*) as count
            FROM complaints
            GROUP BY status
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get monthly statistics
     */
    public function getMonthlyStats($months = 12) {
        $stmt = $this->db->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
            FROM complaints
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY month
            ORDER BY month
        ");
        $stmt->execute([$months]);
        return $stmt->fetchAll();
    }

    /**
     * Get resolution statistics
     */
    public function getResolutionStats() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'under_investigation' THEN 1 ELSE 0 END) as investigating,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM complaints
        ");
        return $stmt->fetch();
    }

    /**
     * Get recent complaints
     */
    public function getRecent($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.full_name as complainant_name
            FROM complaints c
            LEFT JOIN users u ON c.user_id = u.id
            ORDER BY c.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
