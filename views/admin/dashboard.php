<?php
/**
 * Admin Dashboard
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AdminController.php';
requireRole('super_admin');

$adminController = new AdminController();
$stats = $adminController->getDashboardStats()['stats'];

$pageTitle = 'Admin Dashboard';
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
                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-card animate-fade-in">
                            <div class="card-icon bg-success-light">
                                <i class="bi bi-shop"></i>
                            </div>
                            <div class="card-number"><?php echo $stats['total_premises']; ?></div>
                            <div class="card-label">Total Premises</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-card animate-fade-in">
                            <div class="card-icon bg-info-light">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="card-number"><?php echo $stats['total_users']; ?></div>
                            <div class="card-label">Total Users</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-card animate-fade-in">
                            <div class="card-icon bg-warning-light">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="card-number"><?php echo $stats['pending_complaints']; ?></div>
                            <div class="card-label">Pending Complaints</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="dashboard-card animate-fade-in">
                            <div class="card-icon bg-danger-light">
                                <i class="bi bi-file-medical"></i>
                            </div>
                            <div class="card-number"><?php echo $stats['expired_certificates']; ?></div>
                            <div class="card-label">Expired Certificates</div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="bi bi-pie-chart me-2 text-success"></i>Grade Distribution</h5>
                            <canvas id="gradeChart" height="250"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="bi bi-bar-chart me-2 text-success"></i>Monthly Inspections</h5>
                            <canvas id="monthlyChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Complaint Stats & Recent -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="bi bi-exclamation-circle me-2 text-success"></i>Complaint Status</h5>
                            <canvas id="complaintChart" height="250"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="bi bi-clock-history me-2 text-success"></i>Recent Complaints</h5>
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
                                        <?php foreach ($stats['recent_complaints'] as $complaint): ?>
                                            <tr>
                                                <td>#<?php echo $complaint['id']; ?></td>
                                                <td><?php echo $complaint['shop_name']; ?></td>
                                                <td><?php echo COMPLAINT_CATEGORIES[$complaint['category']] ?? $complaint['category']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $complaint['status'] === 'resolved' ? 'success' : 
                                                            ($complaint['status'] === 'pending' ? 'warning' : 
                                                            ($complaint['status'] === 'rejected' ? 'danger' : 'info')); ?>">
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

                <!-- User Stats -->
                <div class="row g-4">
                    <div class="col-lg-12">
                        <div class="chart-container">
                            <h5 class="mb-4"><i class="bi bi-people-fill me-2 text-success"></i>User Distribution</h5>
                            <div class="row text-center">
                                <div class="col-md-2 col-6 mb-3">
                                    <div class="p-3 bg-success bg-opacity-10 rounded">
                                        <h3 class="text-success mb-1"><?php echo $stats['total_inspectors']; ?></h3>
                                        <small class="text-muted">Inspectors</small>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-3">
                                    <div class="p-3 bg-info bg-opacity-10 rounded">
                                        <h3 class="text-info mb-1"><?php echo $stats['total_owners']; ?></h3>
                                        <small class="text-muted">Business Owners</small>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-3">
                                    <div class="p-3 bg-warning bg-opacity-10 rounded">
                                        <h3 class="text-warning mb-1"><?php echo $stats['total_workers_reg']; ?></h3>
                                        <small class="text-muted">Workers</small>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-3">
                                    <div class="p-3 bg-primary bg-opacity-10 rounded">
                                        <h3 class="text-primary mb-1"><?php echo $stats['total_public_users']; ?></h3>
                                        <small class="text-muted">Public Users</small>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-3">
                                    <div class="p-3 bg-danger bg-opacity-10 rounded">
                                        <h3 class="text-danger mb-1"><?php echo $stats['expiring_certificates']; ?></h3>
                                        <small class="text-muted">Expiring Soon</small>
                                    </div>
                                </div>
                                <div class="col-md-2 col-6 mb-3">
                                    <div class="p-3 bg-secondary bg-opacity-10 rounded">
                                        <h3 class="text-secondary mb-1"><?php echo $stats['total_complaints']; ?></h3>
                                        <small class="text-muted">Total Complaints</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Grade Distribution Chart
        const gradeCtx = document.getElementById('gradeChart').getContext('2d');
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($stats['grade_stats'], 'grade')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($stats['grade_stats'], 'count')); ?>,
                    backgroundColor: ['#198754', '#0dcaf0', '#ffc107', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Monthly Inspections Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($stats['monthly_inspections'], 'month')); ?>,
                datasets: [{
                    label: 'Inspections',
                    data: <?php echo json_encode(array_column($stats['monthly_inspections'], 'count')); ?>,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Complaint Status Chart
        const complaintCtx = document.getElementById('complaintChart').getContext('2d');
        new Chart(complaintCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($s) { return ucfirst(str_replace('_', ' ', $s['status'])); }, $stats['complaint_stats'])); ?>,
                datasets: [{
                    label: 'Complaints',
                    data: <?php echo json_encode(array_column($stats['complaint_stats'], 'count')); ?>,
                    backgroundColor: ['#ffc107', '#0dcaf0', '#198754', '#dc3545'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Sidebar Toggle
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Dark Mode Toggle
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        }

        // Check saved dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    </script>
</body>
</html>
