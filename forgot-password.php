<?php
/**
 * Forgot Password Page
 */

require_once 'config/config.php';
require_once 'controllers/AuthController.php';

$auth = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->forgotPassword($_POST);
    setFlashMessage($result['success'] ? 'success' : 'danger', $result['message']);
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/auth.css" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header text-center mb-4">
                <div class="auth-logo">
                    <i class="bi bi-key text-success"></i>
                </div>
                <h3 class="mt-3">Reset Password</h3>
                <p class="text-muted">Enter your email to receive reset instructions</p>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="forgot-password.php">
                <?php echo csrfField(); ?>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required 
                               placeholder="Enter your registered email">
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 btn-lg mb-3">
                    <i class="bi bi-send me-2"></i>Send Reset Link
                </button>
            </form>

            <div class="text-center">
                <p class="mb-0">Remember your password? <a href="login.php" class="text-success">Login here</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
