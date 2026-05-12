<?php
/**
 * Worker Controller
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Worker.php';
require_once __DIR__ . '/../models/MedicalCertificate.php';

class WorkerController {
    private $workerModel;
    private $certificateModel;

    public function __construct() {
        $this->workerModel = new Worker();
        $this->certificateModel = new MedicalCertificate();
    }

    /**
     * Create new worker
     */
    public function create($data) {
        if (!validateCSRFToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $required = ['full_name', 'nic', 'phone'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
            }
        }

        // Check if NIC already exists
        $existing = $this->workerModel->findByNIC($data['nic']);
        if ($existing) {
            return ['success' => false, 'message' => 'Worker with this NIC already exists'];
        }

        $workerData = [
            'full_name' => sanitize($data['full_name']),
            'nic' => sanitize($data['nic']),
            'phone' => sanitize($data['phone']),
            'address' => sanitize($data['address'] ?? ''),
            'workplace_id' => $data['workplace_id'] ?? null,
            'designation' => sanitize($data['designation'] ?? ''),
            'created_by' => getCurrentUserId()
        ];

        $workerId = $this->workerModel->create($workerData);

        if ($workerId) {
            logActivity(getCurrentUserId(), 'Worker Created', "Worker ID: {$workerId}");
            return ['success' => true, 'message' => 'Worker registered successfully', 'id' => $workerId];
        }

        return ['success' => false, 'message' => 'Failed to register worker'];
    }

    /**
     * Update worker
     */
    public function update($id, $data) {
        if (!validateCSRFToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $worker = $this->workerModel->findById($id);
        if (!$worker) {
            return ['success' => false, 'message' => 'Worker not found'];
        }

        $updateData = [
            'full_name' => sanitize($data['full_name']),
            'nic' => sanitize($data['nic']),
            'phone' => sanitize($data['phone']),
            'address' => sanitize($data['address'] ?? ''),
            'workplace_id' => $data['workplace_id'] ?? null,
            'designation' => sanitize($data['designation'] ?? ''),
            'status' => $data['status'] ?? 'active'
        ];

        if ($this->workerModel->update($id, $updateData)) {
            logActivity(getCurrentUserId(), 'Worker Updated', "Worker ID: {$id}");
            return ['success' => true, 'message' => 'Worker updated successfully'];
        }

        return ['success' => false, 'message' => 'Failed to update worker'];
    }

    /**
     * Delete worker
     */
    public function delete($id) {
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        if ($this->workerModel->delete($id)) {
            logActivity(getCurrentUserId(), 'Worker Deleted', "Worker ID: {$id}");
            return ['success' => true, 'message' => 'Worker deleted successfully'];
        }

        return ['success' => false, 'message' => 'Failed to delete worker'];
    }

    /**
     * Add medical certificate
     */
    public function addCertificate($data, $files = []) {
        if (!validateCSRFToken($data['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $required = ['worker_id', 'certificate_number', 'issue_date', 'expiry_date', 'medical_status'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
            }
        }

        // Handle document upload
        $documentPath = null;
        if (!empty($files['document']) && $files['document']['error'] === UPLOAD_ERR_OK) {
            $errors = validateFileUpload($files['document']);
            if (empty($errors)) {
                $result = uploadFile($files['document'], 'medical');
                if ($result['success']) {
                    $documentPath = $result['filename'];
                }
            }
        }

        $certData = [
            'worker_id' => (int)$data['worker_id'],
            'certificate_number' => sanitize($data['certificate_number']),
            'issue_date' => $data['issue_date'],
            'expiry_date' => $data['expiry_date'],
            'medical_status' => sanitize($data['medical_status']),
            'notes' => sanitize($data['notes'] ?? ''),
            'document_path' => $documentPath,
            'created_by' => getCurrentUserId()
        ];

        $certId = $this->certificateModel->create($certData);

        if ($certId) {
            logActivity(getCurrentUserId(), 'Certificate Added', "Certificate ID: {$certId} for worker ID: {$data['worker_id']}");
            return ['success' => true, 'message' => 'Medical certificate added successfully', 'id' => $certId];
        }

        return ['success' => false, 'message' => 'Failed to add certificate'];
    }

    /**
     * Get worker by ID
     */
    public function getById($id) {
        $worker = $this->workerModel->findById($id);
        if ($worker) {
            $certificates = $this->certificateModel->getByWorker($id);
            $worker['certificates'] = $certificates;
            return ['success' => true, 'data' => $worker];
        }
        return ['success' => false, 'message' => 'Worker not found'];
    }

    /**
     * Get worker by NIC
     */
    public function getByNIC($nic) {
        $worker = $this->workerModel->findByNIC($nic);
        if ($worker) {
            $certificates = $this->certificateModel->getByWorker($worker['id']);
            $worker['certificates'] = $certificates;
            return ['success' => true, 'data' => $worker];
        }
        return ['success' => false, 'message' => 'Worker not found'];
    }

    /**
     * Get all workers
     */
    public function getAll($filters = [], $page = 1, $perPage = 20) {
        $workers = $this->workerModel->getAll($filters, $page, $perPage);
        $total = $this->workerModel->count($filters);
        $pagination = paginate($total, $perPage, $page);

        return [
            'success' => true,
            'data' => $workers,
            'pagination' => $pagination
        ];
    }

    /**
     * Get statistics
     */
    public function getStats() {
        $workerStats = $this->workerModel->getStats();
        $certStats = $this->certificateModel->getStats();
        $expiringCerts = $this->certificateModel->getExpiring(30);
        $expiredCerts = $this->certificateModel->getExpired();

        return [
            'success' => true,
            'worker_stats' => $workerStats,
            'certificate_stats' => $certStats,
            'expiring_count' => count($expiringCerts),
            'expired_count' => count($expiredCerts)
        ];
    }

    /**
     * Get expiring certificates
     */
    public function getExpiringCertificates($days = 30) {
        $certificates = $this->certificateModel->getExpiring($days);
        return ['success' => true, 'data' => $certificates];
    }

    /**
     * Auto update expired certificates
     */
    public function autoUpdateExpired() {
        $updated = $this->certificateModel->autoUpdateExpired();
        return ['success' => true, 'updated' => $updated];
    }

    /**
     * Get certificate by ID
     */
    public function getCertificateById($id) {
        $certificate = $this->certificateModel->findById($id);
        if ($certificate) {
            return ['success' => true, 'data' => $certificate];
        }
        return ['success' => false, 'message' => 'Certificate not found'];
    }
}
