<?php
/**
 * API - Create Inspection (Authenticated - Inspector+)
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/InspectionController.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasMinimumRole(ROLES['inspector'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$inspectionController = new InspectionController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $inspectionController->create($_POST, $_FILES);
    echo json_encode($result);
} else {
    $inspectorId = hasRole('inspector') ? getCurrentUserId() : null;
    $page = (int)($_GET['page'] ?? 1);
    $result = $inspectionController->getList($inspectorId, $page);
    echo json_encode($result);
}
