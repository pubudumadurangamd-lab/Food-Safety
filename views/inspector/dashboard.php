<?php
/**
 * Inspector Dashboard
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/InspectionController.php';
require_once __DIR__ . '/../../controllers/ComplaintController.php';
require_once __DIR__ . '/../../controllers/WorkerController.php';
requireMinimumRole(ROLES['inspector']);

$inspectionController = new InspectionController();
$complaintController = new ComplaintController();
$workerController = new WorkerController();

$inspectorId = getCurrentUserId();

// Get stats
$inspections = $inspectionController->getList($inspectorId, 1, 5);
$inspectionCount = $inspectionController->getList($inspectorId)['pagination']['total'] ?? 0;
$complaintStats = $complaintController->getStats()['resolution_stats'];
$workerStats = $workerController->getStats();
$expiringCerts = $workerController->getExpiringCertificates(30)['data'] ?? [];

$pageTitle = 'Inspector Dashboard';
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
                <!-- Welcome Banner -->
                <div class="alert alert-success mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-check fs-1 me-3"></i>
                        <div>
                            <h5 class="mb-1">Welcome, <?php echo $_SESSION['user_name']; ?>!</h5>
                            <p class="mb-0">Public Health Inspector - <?php echo $_SESSION['user_district'] ?? 'Sri Lanka'; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <a href="new-inspection.php" class="text-decoration-none">
                            <div class="dashboard-card text-center">
                                <div class="card-icon bg-success-light mx-auto">
                                    <i class="bi bi-clipboard-plus"></i>
                                </div>
                                <h5>New Inspection</h5>
                                <p class="text-muted mb-0">Start a new premises inspection</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="premises.php" class="text-decoration-none">
                            <div class="dashboard-card text-center">
                                <div class="card-icon bg-info-light mx-auto">
                                    <i class="bi bi-shop"></i>
                                </div>
                                <h5>View Premises</h5>
                                <p class="text-muted mb-0">Manage registered premises</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="workers.php" class="text-decoration-none">
                            <div class="dashboard-card text-center">
                                <div class="card-icon bg-warning-light mx-auto">
                                    <i class="bi bi-person-workspace"></i>
                                </div>
                                <h5>Workers</h5>
                                <p class="text-muted mb-0">Manage worker records</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="complaints.php" class="text-decoration-none">
                            <div class="dashboard-card text-center">
                                <div class="card-icon bg-danger-light mx-auto">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <h5>Complaints</h5>
                                <p class="text-muted mb-0">View public complaints</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-card">
                            <div class="card-icon bg-success-light">
                                <i class="bi bi-clipboard-check"></i>
                            </div>
                            <div class="card-number"><?php echo $inspectionCount; ?></div>
                            <div class="card-label">My Inspections</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-card">
                            <div class="card-icon bg-info-light">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="card-number"><?php echo $complaintStats['pending'] ?? 0; ?></div>
                            <div class="card-label">Pending Complaints</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-card">
                            <div class="card-icon bg-warning-light">
                                <i class="bi bi-file-medical"></i>
                            </div>
                            <div class="card-number"><?php echo count($expiringCerts); ?></div>
                            <div class="card-label">Expiring Certificates</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-card">
                            <div class="card-icon bg-danger-light">
                                <i class="bi bi-person-workspace"></i>
                            </div>
                            <div class="card-number"><?php echo $workerStats['worker_stats']['total'] ?? 0; ?></div>
                            <div class="card-label">Total Workers</div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="bi bi-graph-up me-2 text-success"></i>My Monthly Inspections</h5>
                            <canvas id="inspectorMonthlyChart" height="250"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="bi bi-pie-chart me-2 text-success"></i>Grade Distribution</h5>
                            <canvas id="inspectorGradeChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Inspections & Expiring Certs -->
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="bi bi-clock-history me-2 text-success"></i>Recent Inspections</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Premises</th>
                                            <th>Grade</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inspections['data'] ?? [] as $inspection): ?>
                                            <tr>
                                                <td>#<?php echo $inspection['id']; ?></td>
                                                <td><?php echo $inspection['shop_name']; ?></td>
                                                <td><?php echo getGradeBadge($inspection['grade']); ?></td>
                                                <td><?php echo formatDate($inspection['inspection_date']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="bi bi-calendar-x me-2 text-danger"></i>Expiring Certificates (30 days)</h5>
                            <?php if (empty($expiringCerts)): ?>
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle me-2"></i>No certificates expiring soon!
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Worker</th>
                                                <th>NIC</th>
                                                <th>Workplace</th>
                                                <th>Expires</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($expiringCerts as $cert): ?>
                                                <tr>
                                                    <td><?php echo $cert['worker_name']; ?></td>
                                                    <td><?php echo $cert['worker_nic']; ?></td>
                                                    <td><?php echo $cert['workplace_name'] ?? 'N/A'; ?></td>
                                                    <td>
                                                        <span class="badge bg-warning">
                                                            <?php echo formatDate($cert['expiry_date']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inspector Monthly Chart
        const monthlyCtx = document.getElementById('inspectorMonthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Inspections',
                    data: [12, 19, 15, 25, 22, 30],
                    backgroundColor: '#198754',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        // Inspector Grade Chart
        const gradeCtx = document.getElementById('inspectorGradeChart').getContext('2d');
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: ['A', 'B', 'C', 'D'],
                datasets: [{
                    data: [45, 30, 15, 10],
                    backgroundColor: ['#198754', '#0dcaf0', '#ffc107', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

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
