<?php
/**
 * Logout Handler
 */

require_once 'config/config.php';
require_once 'controllers/AuthController.php';

$auth = new AuthController();
$auth->logout();

setFlashMessage('success', 'You have been logged out successfully');
redirect('index.php');
