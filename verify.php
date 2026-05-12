<?php
/**
 * QR Verification Page
 * Food Safety & Premises Grading Management System
 */

require_once 'config/config.php';
require_once 'controllers/PremisesController.php';

$premisesController = new PremisesController();

$premisesId = (int)($_GET['id'] ?? 0);
$premises = $premisesController->getById($premisesId)['data'] ?? null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Premises - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .verification-card {
            max-width: 500px;
            margin: 0 auto;
        }
        .verification-badge {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 700;
            color: white;
            margin: 0 auto;
        }
        .stamp {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 100px;
            height: 100px;
            border: 3px solid #198754;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #198754;
            font-weight: 700;
            transform: rotate(-15deg);
            opacity: 0.7;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="text-center mb-4">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/11/Emblem_of_Sri_Lanka.svg/120px-Emblem_of_Sri_Lanka.svg.png" 
                 alt="Sri Lanka Government" style="height: 60px;" class="mb-2">
            <h4 class="mb-0">Democratic Socialist Republic of Sri Lanka</h4>
            <p class="text-muted">Ministry of Health - Food Safety Verification</p>
        </div>

        <?php if ($premises): ?>
            <div class="card verification-card shadow-lg border-0 position-relative">
                <div class="card-body p-5 text-center">
                    <div class="stamp">VERIFIED</div>

                    <div class="verification-badge mb-4" style="background: linear-gradient(135deg, 
                        <?php echo $premises['grade'] === 'A' ? '#198754' : ($premises['grade'] === 'B' ? '#0dcaf0' : ($premises['grade'] === 'C' ? '#ffc107' : '#dc3545')); ?>, 
                        <?php echo $premises['grade'] === 'A' ? '#146c43' : ($premises['grade'] === 'B' ? '#0a9bc2' : ($premises['grade'] === 'C' ? '#e0a800' : '#b02a37')); ?>);">
                        <?php echo $premises['grade']; ?>
                    </div>

                    <h3 class="mb-2"><?php echo $premises['shop_name']; ?></h3>
                    <p class="text-muted mb-4"><?php echo $premises['address']; ?>, <?php echo $premises['district']; ?></p>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">License</small>
                                <strong><?php echo $premises['license_number']; ?></strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Grade Status</small>
                                <strong class="text-<?php echo $premises['grade'] === 'A' ? 'success' : ($premises['grade'] === 'B' ? 'info' : ($premises['grade'] === 'C' ? 'warning' : 'danger')); ?>">
                                    <?php echo $premises['grade'] === 'A' ? 'EXCELLENT' : ($premises['grade'] === 'B' ? 'GOOD' : ($premises['grade'] === 'C' ? 'SATISFACTORY' : 'NEEDS IMPROVEMENT')); ?>
                                </strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Last Inspected</small>
                                <strong><?php echo formatDate($premises['inspection_date']); ?></strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Valid Until</small>
                                <strong><?php echo formatDate($premises['expiry_date']); ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <i class="bi bi-shield-check me-2"></i>
                        <strong>This premises has been verified by the Ministry of Health, Sri Lanka.</strong>
                    </div>

                    <p class="text-muted small mb-0">
                        Verified on <?php echo date('d M Y h:i A'); ?><br>
                        System: <?php echo SITE_NAME; ?> v<?php echo VERSION; ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="card verification-card shadow-lg border-0">
                <div class="card-body p-5 text-center">
                    <div class="verification-badge mb-4 bg-danger">
                        <i class="bi bi-x-lg"></i>
                    </div>
                    <h3 class="text-danger mb-3">Verification Failed</h3>
                    <p class="text-muted mb-4">This premises could not be verified in our system. The QR code may be invalid or the premises may not be registered.</p>
                    <a href="index.php" class="btn btn-success">
                        <i class="bi bi-house me-1"></i>Back to Home
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
