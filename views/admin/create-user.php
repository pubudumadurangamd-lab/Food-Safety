<?php
/**
 * Admin - Create User Action Handler
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AdminController.php';
requireRole('super_admin');

$adminController = new AdminController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $adminController->createUser($_POST);
    setFlashMessage($result['success'] ? 'success' : 'danger', $result['message']);
}

redirect('views/admin/users.php');
