<?php
/**
 * Inspector - Create Premises Action Handler
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/PremisesController.php';
requireMinimumRole(ROLES['inspector']);

$premisesController = new PremisesController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $premisesController->create($_POST, $_FILES);
    setFlashMessage($result['success'] ? 'success' : 'danger', $result['message']);
}

redirect('views/inspector/premises.php');
