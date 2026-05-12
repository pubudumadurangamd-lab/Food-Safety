<?php
/**
 * Premises Controller
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Premises.php';

class PremisesController {
    private $premisesModel;

    public function __construct() {
        $this->premisesModel = new Premises();
    }

    /**
     * Create new premises
     */
    public function create($data, $files = []) {
        if (!validateCSRFToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $required = ['shop_name', 'owner_name', 'address', 'contact_number', 'business_type', 'license_number', 'district'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
            }
        }

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

        $premisesData = [
            'shop_name' => sanitize($data['shop_name']),
            'owner_name' => sanitize($data['owner_name']),
            'owner_id' => $data['owner_id'] ?? null,
            'address' => sanitize($data['address']),
            'gps_lat' => $data['gps_lat'] ?? null,
            'gps_lng' => $data['gps_lng'] ?? null,
            'contact_number' => sanitize($data['contact_number']),
            'business_type' => sanitize($data['business_type']),
            'license_number' => sanitize($data['license_number']),
            'inspection_date' => $data['inspection_date'] ?? date('Y-m-d'),
            'expiry_date' => $data['expiry_date'] ?? date('Y-m-d', strtotime('+1 year')),
            'district' => sanitize($data['district']),
            'photos' => $photos,
            'created_by' => getCurrentUserId()
        ];

        $premisesId = $this->premisesModel->create($premisesData);

        if ($premisesId) {
            logActivity(getCurrentUserId(), 'Premises Created', "Created premises ID: {$premisesId}");
            return ['success' => true, 'message' => 'Premises registered successfully', 'id' => $premisesId];
        }

        return ['success' => false, 'message' => 'Failed to register premises'];
    }

    /**
     * Update premises
     */
    public function update($id, $data, $files = []) {
        if (!validateCSRFToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $premises = $this->premisesModel->findById($id);
        if (!$premises) {
            return ['success' => false, 'message' => 'Premises not found'];
        }

        // Handle new photo uploads
        $photos = $premises['photos'];
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

        $updateData = [
            'shop_name' => sanitize($data['shop_name']),
            'owner_name' => sanitize($data['owner_name']),
            'address' => sanitize($data['address']),
            'gps_lat' => $data['gps_lat'] ?? null,
            'gps_lng' => $data['gps_lng'] ?? null,
            'contact_number' => sanitize($data['contact_number']),
            'business_type' => sanitize($data['business_type']),
            'license_number' => sanitize($data['license_number']),
            'inspection_date' => $data['inspection_date'],
            'expiry_date' => $data['expiry_date'],
            'district' => sanitize($data['district']),
            'photos' => $photos,
            'status' => $data['status'] ?? 'active'
        ];

        if ($this->premisesModel->update($id, $updateData)) {
            logActivity(getCurrentUserId(), 'Premises Updated', "Updated premises ID: {$id}");
            return ['success' => true, 'message' => 'Premises updated successfully'];
        }

        return ['success' => false, 'message' => 'Failed to update premises'];
    }

    /**
     * Delete premises
     */
    public function delete($id) {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $premises = $this->premisesModel->findById($id);
        if (!$premises) {
            return ['success' => false, 'message' => 'Premises not found'];
        }

        if ($this->premisesModel->delete($id)) {
            logActivity(getCurrentUserId(), 'Premises Deleted', "Deleted premises ID: {$id}");
            return ['success' => true, 'message' => 'Premises deleted successfully'];
        }

        return ['success' => false, 'message' => 'Failed to delete premises'];
    }

    /**
     * Get premises list
     */
    public function getList($filters = [], $page = 1, $perPage = 20) {
        $premises = $this->premisesModel->getAll($filters, $page, $perPage);
        $total = $this->premisesModel->count($filters);
        $pagination = paginate($total, $perPage, $page);

        return [
            'success' => true,
            'data' => $premises,
            'pagination' => $pagination
        ];
    }

    /**
     * Get single premises
     */
    public function getById($id) {
        $premises = $this->premisesModel->findById($id);
        if ($premises) {
            return ['success' => true, 'data' => $premises];
        }
        return ['success' => false, 'message' => 'Premises not found'];
    }

    /**
     * Search public premises
     */
    public function searchPublic($query, $district = null, $grade = null) {
        $results = $this->premisesModel->searchPublic($query, $district, $grade);
        return ['success' => true, 'data' => $results];
    }

    /**
     * Get statistics
     */
    public function getStats() {
        $gradeStats = $this->premisesModel->getGradeStats();
        $districtStats = $this->premisesModel->getDistrictStats();
        $monthlyStats = $this->premisesModel->getMonthlyStats();

        return [
            'success' => true,
            'grade_stats' => $gradeStats,
            'district_stats' => $districtStats,
            'monthly_stats' => $monthlyStats
        ];
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
