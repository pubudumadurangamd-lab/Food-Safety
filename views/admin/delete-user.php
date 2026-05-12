<?php
/**
 * Admin - Delete User
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AdminController.php';
requireRole('super_admin');

$adminController = new AdminController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $result = $adminController->deleteUser((int)$_POST['id']);
    setFlashMessage($result['success'] ? 'success' : 'danger', $result['message']);
}

redirect('views/admin/users.php');
