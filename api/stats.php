<?php
/**
 * API - Get Dashboard Statistics (Authenticated)
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../controllers/PremisesController.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$adminController = new AdminController();
$premisesController = new PremisesController();

$stats = $adminController->getDashboardStats()['stats'];
$gradeStats = $premisesController->getStats();

echo json_encode([
    'success' => true,
    'stats' => $stats,
    'grade_distribution' => $gradeStats['grade_stats'] ?? []
]);
