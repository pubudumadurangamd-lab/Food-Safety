<?php
/**
 * Inspection Model
 * Food Safety & Premises Grading Management System
 */

class Inspection {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create new inspection
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO inspections (
                premises_id, inspector_id, inspection_date, cleanliness, food_storage,
                employee_hygiene, waste_management, pest_control, water_supply,
                food_handling, kitchen_condition, temperature_control, total_score,
                grade, notes, photos, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $data['premises_id'],
            $data['inspector_id'],
            $data['inspection_date'],
            $data['cleanliness'],
            $data['food_storage'],
            $data['employee_hygiene'],
            $data['waste_management'],
            $data['pest_control'],
            $data['water_supply'],
            $data['food_handling'],
            $data['kitchen_condition'],
            $data['temperature_control'],
            $data['total_score'],
            $data['grade'],
            $data['notes'] ?? null,
            json_encode($data['photos'] ?? [])
        ]);

        $inspectionId = $this->db->lastInsertId();

        // Update premises grade
        $this->updatePremisesGrade($data['premises_id'], $data['grade']);

        return $inspectionId;
    }

    /**
     * Update premises grade
     */
    private function updatePremisesGrade($premisesId, $grade) {
        $stmt = $this->db->prepare("UPDATE premises SET grade = ? WHERE id = ?");
        return $stmt->execute([$grade, $premisesId]);
    }

    /**
     * Find inspection by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT i.*, p.shop_name, p.address, u.full_name as inspector_name
            FROM inspections i
            JOIN premises p ON i.premises_id = p.id
            JOIN users u ON i.inspector_id = u.id
            WHERE i.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        $inspection = $stmt->fetch();

        if ($inspection) {
            $inspection['photos'] = json_decode($inspection['photos'], true) ?? [];
        }

        return $inspection;
    }

    /**
     * Get inspections by premises
     */
    public function getByPremises($premisesId) {
        $stmt = $this->db->prepare("
            SELECT i.*, u.full_name as inspector_name
            FROM inspections i
            JOIN users u ON i.inspector_id = u.id
            WHERE i.premises_id = ?
            ORDER BY i.inspection_date DESC
        ");
        $stmt->execute([$premisesId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all inspections
     */
    public function getAll($inspectorId = null, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        if ($inspectorId) {
            $stmt = $this->db->prepare("
                SELECT i.*, p.shop_name, u.full_name as inspector_name
                FROM inspections i
                JOIN premises p ON i.premises_id = p.id
                JOIN users u ON i.inspector_id = u.id
                WHERE i.inspector_id = ?
                ORDER BY i.created_at DESC LIMIT ? OFFSET ?
            ");
            $stmt->execute([$inspectorId, $perPage, $offset]);
        } else {
            $stmt = $this->db->prepare("
                SELECT i.*, p.shop_name, u.full_name as inspector_name
                FROM inspections i
                JOIN premises p ON i.premises_id = p.id
                JOIN users u ON i.inspector_id = u.id
                ORDER BY i.created_at DESC LIMIT ? OFFSET ?
            ");
            $stmt->execute([$perPage, $offset]);
        }

        return $stmt->fetchAll();
    }

    /**
     * Count inspections
     */
    public function count($inspectorId = null) {
        if ($inspectorId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM inspections WHERE inspector_id = ?");
            $stmt->execute([$inspectorId]);
        } else {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM inspections");
        }

        return $stmt->fetch()['count'];
    }

    /**
     * Get inspection statistics by month
     */
    public function getMonthlyStats($months = 12) {
        $stmt = $this->db->prepare("
            SELECT DATE_FORMAT(inspection_date, '%Y-%m') as month, COUNT(*) as count
            FROM inspections
            WHERE inspection_date >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY month
            ORDER BY month
        ");
        $stmt->execute([$months]);
        return $stmt->fetchAll();
    }

    /**
     * Get grade distribution
     */
    public function getGradeDistribution() {
        $stmt = $this->db->query("
            SELECT grade, COUNT(*) as count
            FROM inspections
            GROUP BY grade
            ORDER BY grade
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get inspector performance
     */
    public function getInspectorStats() {
        $stmt = $this->db->query("
            SELECT u.full_name, COUNT(i.id) as inspection_count
            FROM inspections i
            JOIN users u ON i.inspector_id = u.id
            GROUP BY i.inspector_id
            ORDER BY inspection_count DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get latest inspection for premises
     */
    public function getLatestByPremises($premisesId) {
        $stmt = $this->db->prepare("
            SELECT * FROM inspections
            WHERE premises_id = ?
            ORDER BY inspection_date DESC
            LIMIT 1
        ");
        $stmt->execute([$premisesId]);
        return $stmt->fetch();
    }
}
