<?php
/**
 * API - Verify Premises (QR Code)
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/PremisesController.php';

header('Content-Type: application/json');

$premisesController = new PremisesController();

$id = (int)($_GET['id'] ?? 0);
$result = $premisesController->getById($id);

if ($result['success']) {
    $data = $result['data'];
    echo json_encode([
        'success' => true,
        'verified' => true,
        'premises' => [
            'id' => $data['id'],
            'shop_name' => $data['shop_name'],
            'grade' => $data['grade'],
            'district' => $data['district'],
            'inspection_date' => $data['inspection_date'],
            'expiry_date' => $data['expiry_date'],
            'status' => $data['status'],
            'license_number' => $data['license_number']
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'verified' => false,
        'message' => 'Premises not found or not registered'
    ]);
}
