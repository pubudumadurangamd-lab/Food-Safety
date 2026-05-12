<?php
/**
 * API - Search Premises (Public)
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/PremisesController.php';

header('Content-Type: application/json');

$premisesController = new PremisesController();

$query = sanitize($_GET['query'] ?? '');
$district = sanitize($_GET['district'] ?? '');
$grade = sanitize($_GET['grade'] ?? '');

$results = $premisesController->searchPublic($query, $district, $grade);

echo json_encode($results);
