<?php
/**
 * Inspector - Medical Certificates Overview
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/WorkerController.php';
requireMinimumRole(ROLES['inspector']);

$workerController = new WorkerController();

// Auto-update expired certificates
$workerController->autoUpdateExpired();

$expiring = $workerController->getExpiringCertificates(30)['data'] ?? [];
$expired = $workerController->getStats()['certificate_stats']['expired'] ?? 0;
$valid = $workerController->getStats()['certificate_stats']['valid'] ?? 0;
$total = $workerController->getStats()['certificate_stats']['total'] ?? 0;

$pageTitle = 'Medical Certificates';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main-content">
            <?php include __DIR__ . '/../partials/header.php'; ?>

            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4><i class="bi bi-file-medical me-2 text-success"></i>Medical Certificates Overview</h4>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-card">
                            <div class="card-icon bg-success-light">
                                <i class="bi bi-file-medical"></i>
                            </div>
                            <div class="card-number"><?php echo $total; ?></div>
                            <div class="card-label">Total Certificates</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-card">
                            <div class="card-icon bg-success-light">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="card-number"><?php echo $valid; ?></div>
                            <div class="card-label">Valid Certificates</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-card">
                            <div class="card-icon bg-warning-light">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="card-number"><?php echo count($expiring); ?></div>
                            <div class="card-label">Expiring Soon (30 days)</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-card">
                            <div class="card-icon bg-danger-light">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div class="card-number"><?php echo $expired; ?></div>
                            <div class="card-label">Expired Certificates</div>
                        </div>
                    </div>
                </div>

                <!-- Expiring Certificates -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-calendar-x me-2"></i>Certificates Expiring Within 30 Days</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($expiring)): ?>
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle me-2"></i>No certificates expiring soon. All workers have valid medical certificates!
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Worker</th>
                                            <th>NIC</th>
                                            <th>Workplace</th>
                                            <th>Certificate #</th>
                                            <th>Expires</th>
                                            <th>Days Left</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($expiring as $cert): 
                                            $daysLeft = ceil((strtotime($cert['expiry_date']) - time()) / 86400);
                                        ?>
                                            <tr>
                                                <td><?php echo $cert['worker_name']; ?></td>
                                                <td><?php echo $cert['worker_nic']; ?></td>
                                                <td><?php echo $cert['workplace_name'] ?? 'N/A'; ?></td>
                                                <td><?php echo $cert['certificate_number']; ?></td>
                                                <td><?php echo formatDate($cert['expiry_date']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $daysLeft <= 7 ? 'danger' : 'warning'; ?>">
                                                        <?php echo $daysLeft; ?> days
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="workers.php?search=<?php echo urlencode($cert['worker_nic']); ?>" 
                                                       class="btn btn-sm btn-success">
                                                        <i class="bi bi-plus me-1"></i>Renew
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body text-center p-4">
                                <i class="bi bi-person-plus fs-1 text-success mb-3"></i>
                                <h5>Register New Worker</h5>
                                <p class="text-muted">Add a new food handler to the system</p>
                                <a href="workers.php" class="btn btn-success">
                                    <i class="bi bi-person-plus me-1"></i>Go to Workers
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body text-center p-4">
                                <i class="bi bi-file-earmark-text fs-1 text-info mb-3"></i>
                                <h5>View All Certificates</h5>
                                <p class="text-muted">Browse and manage all medical certificates</p>
                                <a href="workers.php" class="btn btn-info text-white">
                                    <i class="bi bi-list me-1"></i>View Workers
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
