<?php
/**
 * Business Owner Dashboard
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/PremisesController.php';
require_once __DIR__ . '/../../controllers/InspectionController.php';
require_once __DIR__ . '/../../controllers/ComplaintController.php';
requireRole('business_owner');

$premisesController = new PremisesController();
$inspectionController = new InspectionController();
$complaintController = new ComplaintController();

$ownerId = getCurrentUserId();

// Get owner's premises
$premisesList = $premisesController->getList(['owner_id' => $ownerId], 1, 10)['data'] ?? [];

// Get complaints related to owner's premises
$complaints = [];
foreach ($premisesList as $premises) {
    $premisesComplaints = $complaintController->getAll(['search' => $premises['shop_name']], 1, 5)['data'] ?? [];
    $complaints = array_merge($complaints, $premisesComplaints);
}

$pageTitle = 'Business Owner Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main-content">
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <div class="container-fluid">
                <div class="alert alert-success mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shop fs-1 me-3"></i>
                        <div>
                            <h5 class="mb-1">Welcome, <?php echo $_SESSION['user_name']; ?>!</h5>
                            <p class="mb-0">Business Owner Portal - Manage your food premises</p>
                        </div>
                    </div>
                </div>

                <!-- My Premises -->
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4><i class="bi bi-shop me-2 text-success"></i>My Premises</h4>
                        </div>
                    </div>

                    <?php if (empty($premisesList)): ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>No premises registered under your account yet. Contact your PHI to register your business.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($premisesList as $premises): ?>
                            <?php 
                            $inspections = $inspectionController->getByPremises($premises['id'])['data'] ?? [];
                            $latestInspection = $inspections[0] ?? null;
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card premises-card h-100 shadow-sm">
                                    <div class="card-header bg-<?php echo $premises['grade'] === 'A' ? 'success' : ($premises['grade'] === 'B' ? 'info' : ($premises['grade'] === 'C' ? 'warning' : 'danger')); ?> text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0"><?php echo $premises['shop_name']; ?></h5>
                                            <span class="badge bg-white text-dark fs-6">Grade <?php echo $premises['grade']; ?></span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-geo-alt me-1"></i><?php echo $premises['address']; ?>
                                        </p>
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-telephone me-1"></i><?php echo $premises['contact_number']; ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>License:</strong> <?php echo $premises['license_number']; ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Type:</strong> <?php echo $premises['business_type']; ?>
                                        </p>

                                        <?php if ($latestInspection): ?>
                                            <hr>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted">Last Inspection</small>
                                                    <div><?php echo formatDate($latestInspection['inspection_date']); ?></div>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted">Score</small>
                                                    <div class="fw-bold"><?php echo $latestInspection['total_score']; ?>%</div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="<?php echo BASE_URL; ?>views/public/premises-details.php?id=<?php echo $premises['id']; ?>" 
                                           target="_blank" class="btn btn-outline-success btn-sm w-100">
                                            <i class="bi bi-eye me-1"></i>View Public Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Inspection History -->
                <?php if (!empty($premisesList)): ?>
                    <div class="row g-4 mb-4">
                        <div class="col-lg-12">
                            <div class="chart-container">
                                <h5 class="mb-4"><i class="bi bi-clipboard-check me-2 text-success"></i>Inspection History</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Premises</th>
                                                <th>Date</th>
                                                <th>Grade</th>
                                                <th>Score</th>
                                                <th>Inspector</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $allInspections = [];
                                            foreach ($premisesList as $premises) {
                                                $inspections = $inspectionController->getByPremises($premises['id'])['data'] ?? [];
                                                foreach ($inspections as $ins) {
                                                    $ins['shop_name'] = $premises['shop_name'];
                                                    $allInspections[] = $ins;
                                                }
                                            }
                                            usort($allInspections, function($a, $b) {
                                                return strtotime($b['inspection_date']) - strtotime($a['inspection_date']);
                                            });
                                            $allInspections = array_slice($allInspections, 0, 10);
                                            ?>
                                            <?php foreach ($allInspections as $inspection): ?>
                                                <tr>
                                                    <td><?php echo $inspection['shop_name']; ?></td>
                                                    <td><?php echo formatDate($inspection['inspection_date']); ?></td>
                                                    <td><?php echo getGradeBadge($inspection['grade']); ?></td>
                                                    <td><?php echo $inspection['total_score']; ?>%</td>
                                                    <td><?php echo $inspection['inspector_name']; ?></td>
                                                    <td><?php echo substr($inspection['notes'] ?? '', 0, 50); ?>...</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Complaints -->
                <?php if (!empty($complaints)): ?>
                    <div class="row g-4">
                        <div class="col-lg-12">
                            <div class="chart-container">
                                <h5 class="mb-4"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Complaints About My Premises</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Shop</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($complaints, 0, 5) as $complaint): ?>
                                                <tr>
                                                    <td>#<?php echo $complaint['id']; ?></td>
                                                    <td><?php echo $complaint['shop_name']; ?></td>
                                                    <td><?php echo COMPLAINT_CATEGORIES[$complaint['category']] ?? $complaint['category']; ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $complaint['status'] === 'resolved' ? 'success' : 
                                                                ($complaint['status'] === 'pending' ? 'warning' : 'info'); ?>">
                                                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo formatDate($complaint['created_at']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        }
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    </script>
</body>
</html>
