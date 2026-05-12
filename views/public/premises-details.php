<?php
/**
 * Premises Details - Public View with QR Code
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/PremisesController.php';
require_once __DIR__ . '/../../controllers/InspectionController.php';

$premisesController = new PremisesController();
$inspectionController = new InspectionController();

$premisesId = (int)($_GET['id'] ?? 0);
$premises = $premisesController->getById($premisesId)['data'] ?? null;

if (!$premises) {
    setFlashMessage('danger', 'Premises not found');
    redirect('views/public/search.php');
}

$inspections = $inspectionController->getByPremises($premisesId)['data'] ?? [];

// Generate QR Code data
$qrData = BASE_URL . 'verify.php?id=' . $premisesId;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $premises['shop_name']; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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
                    <li class="nav-item"><a class="nav-link" href="complaint.php">Submit Complaint</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Premises Header -->
                <div class="card shadow border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="mb-2"><?php echo $premises['shop_name']; ?></h2>
                                <p class="text-muted mb-3">
                                    <i class="bi bi-geo-alt me-2"></i><?php echo $premises['address']; ?>, <?php echo $premises['district']; ?>
                                </p>
                                <div class="d-flex gap-3 mb-3">
                                    <span class="badge bg-<?php echo $premises['status'] === 'active' ? 'success' : 'danger'; ?> fs-6">
                                        <?php echo ucfirst($premises['status']); ?>
                                    </span>
                                    <span class="badge bg-dark fs-6">
                                        <i class="bi bi-telephone me-1"></i><?php echo $premises['contact_number']; ?>
                                    </span>
                                </div>
                                <p class="mb-0">
                                    <strong>License:</strong> <?php echo $premises['license_number']; ?> | 
                                    <strong>Business Type:</strong> <?php echo $premises['business_type']; ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="grade-display p-4 rounded" style="background: linear-gradient(135deg, 
                                    <?php echo $premises['grade'] === 'A' ? '#198754' : ($premises['grade'] === 'B' ? '#0dcaf0' : ($premises['grade'] === 'C' ? '#ffc107' : '#dc3545')); ?>, 
                                    <?php echo $premises['grade'] === 'A' ? '#146c43' : ($premises['grade'] === 'B' ? '#0a9bc2' : ($premises['grade'] === 'C' ? '#e0a800' : '#b02a37')); ?>);">
                                    <div class="text-white">
                                        <div class="display-1 fw-bold"><?php echo $premises['grade']; ?></div>
                                        <div class="fs-5">Grade</div>
                                        <div class="mt-2">
                                            <?php echo $premises['grade'] === 'A' ? 'Excellent' : ($premises['grade'] === 'B' ? 'Good' : ($premises['grade'] === 'C' ? 'Satisfactory' : 'Needs Improvement')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Inspection History -->
                    <div class="col-lg-8">
                        <div class="card shadow border-0">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Inspection History</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($inspections)): ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>No inspection records available.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Grade</th>
                                                    <th>Score</th>
                                                    <th>Inspector</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($inspections as $inspection): ?>
                                                    <tr>
                                                        <td><?php echo formatDate($inspection['inspection_date']); ?></td>
                                                        <td><?php echo getGradeBadge($inspection['grade']); ?></td>
                                                        <td><?php echo $inspection['total_score']; ?>%</td>
                                                        <td><?php echo $inspection['inspector_name']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Photos -->
                        <?php if (!empty($premises['photos'])): ?>
                            <div class="card shadow border-0 mt-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="bi bi-images me-2"></i>Premises Photos</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <?php foreach ($premises['photos'] as $photo): ?>
                                            <div class="col-md-4">
                                                <img src="<?php echo BASE_URL; ?>assets/uploads/premises/<?php echo $photo; ?>" 
                                                     class="img-fluid rounded" alt="Premises Photo">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- QR Code & Verification -->
                    <div class="col-lg-4">
                        <div class="card shadow border-0">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="bi bi-qr-code me-2"></i>Verification</h5>
                            </div>
                            <div class="card-body text-center">
                                <div id="qrcode" class="mb-3"></div>
                                <p class="text-muted small mb-3">Scan this QR code to verify this premises grade</p>
                                <a href="<?php echo $qrData; ?>" class="btn btn-outline-success btn-sm w-100" target="_blank">
                                    <i class="bi bi-check-circle me-1"></i>Verify Online
                                </a>
                            </div>
                        </div>

                        <!-- Report Issue -->
                        <div class="card shadow border-0 mt-4">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Report Issue</h5>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted">Notice a problem with this premises? Report it to our inspectors.</p>
                                <a href="complaint.php?premises=<?php echo $premisesId; ?>" class="btn btn-warning w-100">
                                    <i class="bi bi-flag me-1"></i>Submit Complaint
                                </a>
                            </div>
                        </div>

                        <!-- Inspection Validity -->
                        <div class="card shadow border-0 mt-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Inspection Details</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>Last Inspected:</strong><br><?php echo formatDate($premises['inspection_date']); ?></p>
                                <p class="mb-2"><strong>Valid Until:</strong><br><?php echo formatDate($premises['expiry_date']); ?></p>
                                <p class="mb-0">
                                    <strong>Status:</strong><br>
                                    <?php 
                                    $expiryDate = strtotime($premises['expiry_date']);
                                    $today = time();
                                    if ($expiryDate < $today) {
                                        echo '<span class="badge bg-danger">Expired</span>';
                                    } elseif ($expiryDate < strtotime('+30 days')) {
                                        echo '<span class="badge bg-warning">Expiring Soon</span>';
                                    } else {
                                        echo '<span class="badge bg-success">Valid</span>';
                                    }
                                    ?>
                                </p>
                            </div>
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
        // Generate QR Code
        new QRCode(document.getElementById("qrcode"), {
            text: "<?php echo $qrData; ?>",
            width: 200,
            height: 200,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    </script>
</body>
</html>
