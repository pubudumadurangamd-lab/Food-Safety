<?php
/**
 * Premises Model
 * Food Safety & Premises Grading Management System
 */

class Premises {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new premises
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO premises (
                shop_name, owner_name, owner_id, address, gps_lat, gps_lng,
                contact_number, business_type, license_number, inspection_date,
                expiry_date, grade, status, district, photos, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $data['shop_name'],
            $data['owner_name'],
            $data['owner_id'] ?? null,
            $data['address'],
            $data['gps_lat'] ?? null,
            $data['gps_lng'] ?? null,
            $data['contact_number'],
            $data['business_type'],
            $data['license_number'],
            $data['inspection_date'],
            $data['expiry_date'],
            $data['grade'] ?? 'D',
            $data['district'],
            json_encode($data['photos'] ?? []),
            $data['created_by']
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Find premises by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.full_name as inspector_name 
            FROM premises p 
            LEFT JOIN users u ON p.created_by = u.id 
            WHERE p.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        $premises = $stmt->fetch();

        if ($premises) {
            $premises['photos'] = json_decode($premises['photos'], true) ?? [];
        }

        return $premises;
    }

    /**
     * Update premises
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];

        $allowedFields = [
            'shop_name', 'owner_name', 'owner_id', 'address', 'gps_lat', 'gps_lng',
            'contact_number', 'business_type', 'license_number', 'inspection_date',
            'expiry_date', 'grade', 'status', 'district', 'photos'
        ];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = ?";
                if ($key === 'photos') {
                    $values[] = json_encode($value);
                } else {
                    $values[] = $value;
                }
            }
        }

        $values[] = $id;

        $sql = "UPDATE premises SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Delete premises
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM premises WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get all premises with filters
     */
    public function getAll($filters = [], $page = 1, $perPage = 20) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['district'])) {
            $where[] = "district = ?";
            $params[] = $filters['district'];
        }

        if (!empty($filters['grade'])) {
            $where[] = "grade = ?";
            $params[] = $filters['grade'];
        }

        if (!empty($filters['business_type'])) {
            $where[] = "business_type = ?";
            $params[] = $filters['business_type'];
        }

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(shop_name LIKE ? OR owner_name LIKE ? OR license_number LIKE ?)";
            $search = "%" . $filters['search'] . "%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.*, u.full_name as inspector_name FROM premises p 
                LEFT JOIN users u ON p.created_by = u.id 
                WHERE " . implode(' AND ', $where) . " 
                ORDER BY p.created_at DESC LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        foreach ($results as &$result) {
            $result['photos'] = json_decode($result['photos'], true) ?? [];
        }

        return $results;
    }

    /**
     * Count premises with filters
     */
    public function count($filters = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['district'])) {
            $where[] = "district = ?";
            $params[] = $filters['district'];
        }

        if (!empty($filters['grade'])) {
            $where[] = "grade = ?";
            $params[] = $filters['grade'];
        }

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        $sql = "SELECT COUNT(*) as count FROM premises WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'];
    }

    /**
     * Get grade statistics
     */
    public function getGradeStats() {
        $stmt = $this->db->query("
            SELECT grade, COUNT(*) as count 
            FROM premises 
            WHERE status = 'active' 
            GROUP BY grade 
            ORDER BY grade
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get district statistics
     */
    public function getDistrictStats() {
        $stmt = $this->db->query("
            SELECT district, COUNT(*) as count 
            FROM premises 
            WHERE status = 'active' 
            GROUP BY district 
            ORDER BY count DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get monthly inspection stats
     */
    public function getMonthlyStats($months = 12) {
        $stmt = $this->db->prepare("
            SELECT DATE_FORMAT(inspection_date, '%Y-%m') as month, COUNT(*) as count 
            FROM premises 
            WHERE inspection_date >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY month 
            ORDER BY month
        ");
        $stmt->execute([$months]);
        return $stmt->fetchAll();
    }

    /**
     * Search public premises
     */
    public function searchPublic($query, $district = null, $grade = null) {
        $where = ["status = 'active'"];
        $params = [];

        if ($query) {
            $where[] = "(shop_name LIKE ? OR owner_name LIKE ?)";
            $search = "%$query%";
            $params[] = $search;
            $params[] = $search;
        }

        if ($district) {
            $where[] = "district = ?";
            $params[] = $district;
        }

        if ($grade) {
            $where[] = "grade = ?";
            $params[] = $grade;
        }

        $sql = "SELECT id, shop_name, grade, inspection_date, status, district, address 
                FROM premises 
                WHERE " . implode(' AND ', $where) . " 
                ORDER BY shop_name LIMIT 100";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get premises by owner
     */
    public function getByOwner($ownerId) {
        $stmt = $this->db->prepare("SELECT * FROM premises WHERE owner_id = ? ORDER BY created_at DESC");
        $stmt->execute([$ownerId]);
        $results = $stmt->fetchAll();

        foreach ($results as &$result) {
            $result['photos'] = json_decode($result['photos'], true) ?? [];
        }

        return $results;
    }

    /**
     * Get expired premises
     */
    public function getExpired($days = 30) {
        $stmt = $this->db->prepare("
            SELECT * FROM premises 
            WHERE expiry_date < DATE_ADD(NOW(), INTERVAL ? DAY) 
            AND status = 'active'
            ORDER BY expiry_date
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
}
