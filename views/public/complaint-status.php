<?php
/**
 * Public - Track Complaint Status
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/ComplaintController.php';

$complaintController = new ComplaintController();

$complaintId = (int)($_GET['id'] ?? 0);
$result = $complaintController->getById($complaintId);
$complaint = $result['data'] ?? null;

$categories = COMPLAINT_CATEGORIES;
$statuses = ['pending' => 'Pending', 'under_investigation' => 'Under Investigation', 'resolved' => 'Resolved', 'rejected' => 'Rejected'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Complaint - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="public-portal">
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="bi bi-shield-check me-2"></i><?php echo SITE_SHORT; ?>
            </a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h3 class="text-center mb-4"><i class="bi bi-search text-success me-2"></i>Complaint Status</h3>

                <?php if ($complaint): ?>
                    <div class="card shadow border-0">
                        <div class="card-header bg-<?php 
                            echo $complaint['status'] === 'resolved' ? 'success' : 
                                ($complaint['status'] === 'pending' ? 'warning' : 
                                ($complaint['status'] === 'rejected' ? 'danger' : 'info')); ?> text-white">
                            <h5 class="mb-0">Complaint #<?php echo $complaint['id']; ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Shop:</strong> <?php echo $complaint['shop_name']; ?>
                            </div>
                            <div class="mb-3">
                                <strong>Category:</strong> <?php echo $categories[$complaint['category']] ?? $complaint['category']; ?>
                            </div>
                            <div class="mb-3">
                                <strong>Status:</strong> 
                                <span class="badge bg-<?php 
                                    echo $complaint['status'] === 'resolved' ? 'success' : 
                                        ($complaint['status'] === 'pending' ? 'warning' : 
                                        ($complaint['status'] === 'rejected' ? 'danger' : 'info')); ?>">
                                    <?php echo $statuses[$complaint['status']] ?? $complaint['status']; ?>
                                </span>
                            </div>
                            <div class="mb-3">
                                <strong>Submitted:</strong> <?php echo formatDateTime($complaint['created_at']); ?>
                            </div>
                            <?php if ($complaint['resolved_at']): ?>
                                <div class="mb-3">
                                    <strong>Resolved:</strong> <?php echo formatDateTime($complaint['resolved_at']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($complaint['resolution']): ?>
                                <div class="mb-3">
                                    <strong>Resolution:</strong><br>
                                    <div class="alert alert-light mt-2"><?php echo nl2br($complaint['resolution']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-x-circle fs-1 d-block mb-3"></i>
                        <h5>Complaint Not Found</h5>
                        <p>The complaint reference number you entered could not be found.</p>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="complaint.php" class="btn btn-success">
                        <i class="bi bi-arrow-left me-1"></i>Back to Complaints
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
