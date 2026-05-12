<?php
/**
 * API - Get Premises Details
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/PremisesController.php';

header('Content-Type: application/json');

$premisesController = new PremisesController();

$id = (int)($_GET['id'] ?? 0);
$result = $premisesController->getById($id);

echo json_encode($result);
