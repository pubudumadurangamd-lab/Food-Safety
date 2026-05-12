<?php
/**
 * Inspection Controller
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Inspection.php';
require_once __DIR__ . '/../models/Premises.php';

class InspectionController {
    private $inspectionModel;
    private $premisesModel;

    public function __construct() {
        $this->inspectionModel = new Inspection();
        $this->premisesModel = new Premises();
    }

    /**
     * Create new inspection
     */
    public function create($data, $files = []) {
        if (!validateCSRFToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $required = ['premises_id', 'inspection_date', 'cleanliness', 'food_storage', 'employee_hygiene', 
                     'waste_management', 'pest_control', 'water_supply', 'food_handling', 
                     'kitchen_condition', 'temperature_control'];

        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                return ['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
            }
        }

        // Validate premises exists
        $premises = $this->premisesModel->findById($data['premises_id']);
        if (!$premises) {
            return ['success' => false, 'message' => 'Premises not found'];
        }

        // Calculate scores
        $scores = [
            'cleanliness' => (int)$data['cleanliness'],
            'food_storage' => (int)$data['food_storage'],
            'employee_hygiene' => (int)$data['employee_hygiene'],
            'waste_management' => (int)$data['waste_management'],
            'pest_control' => (int)$data['pest_control'],
            'water_supply' => (int)$data['water_supply'],
            'food_handling' => (int)$data['food_handling'],
            'kitchen_condition' => (int)$data['kitchen_condition'],
            'temperature_control' => (int)$data['temperature_control']
        ];

        // Calculate total score and grade
        $weights = GRADE_WEIGHTS;
        $totalScore = 0;
        $totalWeight = 0;

        foreach ($scores as $criteria => $score) {
            if (isset($weights[$criteria])) {
                $totalScore += ($score * $weights[$criteria]);
                $totalWeight += $weights[$criteria];
            }
        }

        $percentage = ($totalScore / ($totalWeight * 5)) * 100;
        $grade = calculateGrade($scores);

        // Handle photo uploads
        $photos = [];
        if (!empty($files['photos'])) {
            $uploadedFiles = $this->reArrayFiles($files['photos']);
            foreach ($uploadedFiles as $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $errors = validateFileUpload($file);
                    if (empty($errors)) {
                        $result = uploadFile($file, 'premises');
                        if ($result['success']) {
                            $photos[] = $result['filename'];
                        }
                    }
                }
            }
        }

        $inspectionData = [
            'premises_id' => (int)$data['premises_id'],
            'inspector_id' => getCurrentUserId(),
            'inspection_date' => $data['inspection_date'],
            'cleanliness' => $scores['cleanliness'],
            'food_storage' => $scores['food_storage'],
            'employee_hygiene' => $scores['employee_hygiene'],
            'waste_management' => $scores['waste_management'],
            'pest_control' => $scores['pest_control'],
            'water_supply' => $scores['water_supply'],
            'food_handling' => $scores['food_handling'],
            'kitchen_condition' => $scores['kitchen_condition'],
            'temperature_control' => $scores['temperature_control'],
            'total_score' => round($percentage, 2),
            'grade' => $grade,
            'notes' => sanitize($data['notes'] ?? ''),
            'photos' => $photos
        ];

        $inspectionId = $this->inspectionModel->create($inspectionData);

        if ($inspectionId) {
            logActivity(getCurrentUserId(), 'Inspection Created', "Inspection ID: {$inspectionId} for premises ID: {$data['premises_id']}");
            return [
                'success' => true, 
                'message' => "Inspection completed. Grade: {$grade} (Score: " . round($percentage, 1) . "%)",
                'id' => $inspectionId,
                'grade' => $grade,
                'score' => round($percentage, 1)
            ];
        }

        return ['success' => false, 'message' => 'Failed to create inspection'];
    }

    /**
     * Get inspection by ID
     */
    public function getById($id) {
        $inspection = $this->inspectionModel->findById($id);
        if ($inspection) {
            return ['success' => true, 'data' => $inspection];
        }
        return ['success' => false, 'message' => 'Inspection not found'];
    }

    /**
     * Get inspections list
     */
    public function getList($inspectorId = null, $page = 1, $perPage = 20) {
        $inspections = $this->inspectionModel->getAll($inspectorId, $page, $perPage);
        $total = $this->inspectionModel->count($inspectorId);
        $pagination = paginate($total, $perPage, $page);

        return [
            'success' => true,
            'data' => $inspections,
            'pagination' => $pagination
        ];
    }

    /**
     * Get inspection statistics
     */
    public function getStats() {
        $monthlyStats = $this->inspectionModel->getMonthlyStats();
        $gradeDistribution = $this->inspectionModel->getGradeDistribution();
        $inspectorStats = $this->inspectionModel->getInspectorStats();

        return [
            'success' => true,
            'monthly_stats' => $monthlyStats,
            'grade_distribution' => $gradeDistribution,
            'inspector_stats' => $inspectorStats
        ];
    }

    /**
     * Get inspections by premises
     */
    public function getByPremises($premisesId) {
        $inspections = $this->inspectionModel->getByPremises($premisesId);
        return ['success' => true, 'data' => $inspections];
    }

    /**
     * Re-array files from $_FILES format
     */
    private function reArrayFiles($filePost) {
        $files = [];
        $fileCount = count($filePost['name']);
        $fileKeys = array_keys($filePost);

        for ($i = 0; $i < $fileCount; $i++) {
            foreach ($fileKeys as $key) {
                $files[$i][$key] = $filePost[$key][$i];
            }
        }

        return $files;
    }
}
