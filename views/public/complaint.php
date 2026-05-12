<?php
/**
 * Public Complaint Form
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/ComplaintController.php';

$complaintController = new ComplaintController();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $complaintController->create($_POST, $_FILES);
    if ($result['success']) {
        setFlashMessage('success', 'Complaint submitted successfully! Reference #: ' . $result['id']);
    } else {
        setFlashMessage('danger', $result['message']);
    }
}

$flash = getFlashMessage();

// Complaint categories
$categories = COMPLAINT_CATEGORIES;

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
    <title>Submit Complaint - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="public-portal">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="bi bi-shield-check me-2"></i><?php echo SITE_SHORT; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="search.php">Search Shops</a></li>
                    <li class="nav-item"><a class="nav-link active" href="complaint.php">Submit Complaint</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="text-center mb-2"><i class="bi bi-exclamation-triangle text-success me-2"></i>Submit a Complaint</h2>
                <p class="text-center text-muted mb-5">Report food safety concerns or unhygienic conditions</p>

                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                        <?php echo $flash['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow border-0">
                    <div class="card-body p-4">
                        <form method="POST" action="complaint.php" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Shop/Business Name *</label>
                                    <input type="text" class="form-control" name="shop_name" required 
                                           placeholder="Enter the name of the food premises">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Premises ID (if known)</label>
                                    <input type="number" class="form-control" name="premises_id" 
                                           placeholder="Optional - if registered in system">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Complaint Category *</label>
                                    <select class="form-select" name="category" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $key => $label): ?>
                                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">District</label>
                                    <select class="form-select" name="district">
                                        <option value="">Select District</option>
                                        <?php foreach ($districts as $district): ?>
                                            <option value="<?php echo $district; ?>"><?php echo $district; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Location/Address *</label>
                                <textarea class="form-control" name="location" rows="2" required 
                                          placeholder="Enter the exact location or address of the premises"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Description *</label>
                                <textarea class="form-control" name="description" rows="5" required 
                                          placeholder="Describe the issue in detail. Include date, time, and what you observed..."></textarea>
                                <div class="form-text">Please provide as much detail as possible to help our inspectors</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Your Contact Details *</label>
                                <input type="text" class="form-control" name="contact_details" required 
                                       placeholder="Phone number or email for follow-up">
                                <div class="form-text">Your contact details are kept confidential</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Upload Photos (Optional)</label>
                                <input type="file" class="form-control" name="photos[]" multiple accept="image/*">
                                <div class="form-text">Upload photos as evidence (Max 5MB each, up to 5 photos)</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-send me-2"></i>Submit Complaint
                                </button>
                                <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Home
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Track Complaint -->
                <div class="card shadow border-0 mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-search me-2"></i>Track Your Complaint</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Already submitted a complaint? Track its status here.</p>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Enter Complaint Reference Number" id="trackId">
                            <button class="btn btn-info text-white" type="button" onclick="trackComplaint()">
                                <i class="bi bi-search me-1"></i>Track
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Government of Sri Lanka - Ministry of Health</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function trackComplaint() {
            const id = document.getElementById('trackId').value;
            if (id) {
                window.location.href = 'complaint-status.php?id=' + encodeURIComponent(id);
            } else {
                alert('Please enter a complaint reference number');
            }
        }
    </script>
</body>
</html>
