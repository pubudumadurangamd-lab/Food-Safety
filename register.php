<?php
/**
 * Registration Page - Food Safety & Premises Grading Management System
 */

require_once 'config/config.php';
require_once 'controllers/AuthController.php';

$auth = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->register($_POST);
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
        redirect('login.php');
    } else {
        setFlashMessage('danger', $result['message']);
    }
}

$flash = getFlashMessage();

// Sri Lanka Districts
$districts = [
    'Colombo', 'Gampaha', 'Kalutara', 'Kandy', 'Matale', 'Nuwara Eliya',
    'Galle', 'Matara', 'Hambantota', 'Jaffna', 'Kilinochchi', 'Mannar',
    'Vavuniya', 'Mullaitivu', 'Batticaloa', 'Ampara', 'Trincomalee',
    'Kurunegala', 'Puttalam', 'Anuradhapura', 'Polonnaruwa', 'Badulla',
    'Monaragala', 'Ratnapura', 'Kegalle'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/auth.css" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header text-center mb-4">
                <div class="auth-logo">
                    <i class="bi bi-shield-check text-success"></i>
                </div>
                <h3 class="mt-3">Create Account</h3>
                <p class="text-muted">Join the Food Safety System</p>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" class="needs-validation" novalidate>
                <?php echo csrfField(); ?>

                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="full_name" name="full_name" required 
                               placeholder="Enter your full name">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required 
                               placeholder="Enter your email">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                        <input type="tel" class="form-control" id="phone" name="phone" required 
                               placeholder="07X XXX XXXX">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nic" class="form-label">NIC Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                        <input type="text" class="form-control" id="nic" name="nic" 
                               placeholder="Enter your NIC">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="district" class="form-label">District</label>
                    <select class="form-select" id="district" name="district">
                        <option value="">Select District</option>
                        <?php foreach ($districts as $district): ?>
                            <option value="<?php echo $district; ?>"><?php echo $district; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Account Type *</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="">Select Account Type</option>
                        <option value="public_user">Public User (Search & Complaints)</option>
                        <option value="business_owner">Business Owner</option>
                        <option value="worker">Food Handler / Worker</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required 
                               minlength="8" placeholder="Minimum 8 characters">
                    </div>
                    <div class="form-text">Must be at least 8 characters long</div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required 
                               placeholder="Confirm your password">
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="#" class="text-success">Terms of Service</a> and 
                        <a href="#" class="text-success">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-success w-100 btn-lg mb-3">
                    <i class="bi bi-person-plus me-2"></i>Register
                </button>
            </form>

            <div class="text-center">
                <p class="mb-0">Already have an account? <a href="login.php" class="text-success">Login here</a></p>
            </div>

            <hr class="my-4">

            <div class="text-center">
                <a href="index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Back to Home
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
