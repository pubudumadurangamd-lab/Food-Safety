<?php
/**
 * Complaint Controller
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Complaint.php';

class ComplaintController {
    private $complaintModel;

    public function __construct() {
        $this->complaintModel = new Complaint();
    }

    /**
     * Create new complaint
     */
    public function create($data, $files = []) {
        if (!validateCSRFToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $required = ['shop_name', 'category', 'description', 'contact_details'];
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
                        $result = uploadFile($file, 'complaints');
                        if ($result['success']) {
                            $photos[] = $result['filename'];
                        }
                    }
                }
            }
        }

        $complaintData = [
            'user_id' => getCurrentUserId(),
            'premises_id' => $data['premises_id'] ?? null,
            'shop_name' => sanitize($data['shop_name']),
            'category' => sanitize($data['category']),
            'description' => sanitize($data['description']),
            'location' => sanitize($data['location'] ?? ''),
            'contact_details' => sanitize($data['contact_details']),
            'photos' => $photos
        ];

        $complaintId = $this->complaintModel->create($complaintData);

        if ($complaintId) {
            logActivity(getCurrentUserId(), 'Complaint Submitted', "Complaint ID: {$complaintId}");
            return ['success' => true, 'message' => 'Complaint submitted successfully', 'id' => $complaintId];
        }

        return ['success' => false, 'message' => 'Failed to submit complaint'];
    }

    /**
     * Update complaint status
     */
    public function updateStatus($id, $data) {
        if (!validateCSRFToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $status = sanitize($data['status'] ?? '');
        $resolution = sanitize($data['resolution'] ?? '');

        $validStatuses = ['pending', 'under_investigation', 'resolved', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        if ($this->complaintModel->updateStatus($id, $status, $resolution, getCurrentUserId())) {
            logActivity(getCurrentUserId(), 'Complaint Status Updated', "Complaint ID: {$id} set to {$status}");
            return ['success' => true, 'message' => 'Status updated successfully'];
        }

        return ['success' => false, 'message' => 'Failed to update status'];
    }

    /**
     * Get complaints by user
     */
    public function getByUser($userId, $page = 1, $perPage = 10) {
        $complaints = $this->complaintModel->getByUser($userId, $page, $perPage);
        $total = $this->complaintModel->count(['user_id' => $userId]);
        $pagination = paginate($total, $perPage, $page);

        return [
            'success' => true,
            'data' => $complaints,
            'pagination' => $pagination
        ];
    }

    /**
     * Get all complaints
     */
    public function getAll($filters = [], $page = 1, $perPage = 20) {
        $complaints = $this->complaintModel->getAll($filters, $page, $perPage);
        $total = $this->complaintModel->count($filters);
        $pagination = paginate($total, $perPage, $page);

        return [
            'success' => true,
            'data' => $complaints,
            'pagination' => $pagination
        ];
    }

    /**
     * Get complaint by ID
     */
    public function getById($id) {
        $complaint = $this->complaintModel->findById($id);
        if ($complaint) {
            return ['success' => true, 'data' => $complaint];
        }
        return ['success' => false, 'message' => 'Complaint not found'];
    }

    /**
     * Get statistics
     */
    public function getStats() {
        $statusStats = $this->complaintModel->getStatusStats();
        $monthlyStats = $this->complaintModel->getMonthlyStats();
        $resolutionStats = $this->complaintModel->getResolutionStats();

        return [
            'success' => true,
            'status_stats' => $statusStats,
            'monthly_stats' => $monthlyStats,
            'resolution_stats' => $resolutionStats
        ];
    }

    /**
     * Get recent complaints
     */
    public function getRecent($limit = 10) {
        $complaints = $this->complaintModel->getRecent($limit);
        return ['success' => true, 'data' => $complaints];
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
