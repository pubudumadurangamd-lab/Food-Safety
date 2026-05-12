<?php
/**
 * Worker Dashboard
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/WorkerController.php';
requireRole('worker');

$workerController = new WorkerController();

// Get worker by user ID or NIC
$worker = null;
$certificates = [];

// Try to find worker by logged in user
$userId = getCurrentUserId();
// This would need a method to find worker by user_id - simplified for demo

$pageTitle = 'Worker Dashboard';
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
                <div class="alert alert-success mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-check fs-1 me-3"></i>
                        <div>
                            <h5 class="mb-1">Welcome, <?php echo $_SESSION['user_name']; ?>!</h5>
                            <p class="mb-0">Food Handler Portal - View your medical certificates and status</p>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-file-medical me-2"></i>My Medical Certificates</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Enter your NIC number to search for your medical records and download certificates.
                                </div>
                                <form method="GET" class="mb-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-lg" name="nic" 
                                               placeholder="Enter your NIC number" required>
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="bi bi-search me-1"></i>Search
                                        </button>
                                    </div>
                                </form>

                                <?php 
                                $nic = sanitize($_GET['nic'] ?? '');
                                if ($nic) {
                                    $result = $workerController->getByNIC($nic);
                                    if ($result['success']) {
                                        $worker = $result['data'];
                                        $certificates = $worker['certificates'] ?? [];
                                    }
                                }
                                ?>

                                <?php if ($worker): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Certificate #</th>
                                                    <th>Issue Date</th>
                                                    <th>Expiry Date</th>
                                                    <th>Status</th>
                                                    <th>Medical Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($certificates as $cert): ?>
                                                    <tr>
                                                        <td><?php echo $cert['certificate_number']; ?></td>
                                                        <td><?php echo formatDate($cert['issue_date']); ?></td>
                                                        <td>
                                                            <?php 
                                                            $expiry = strtotime($cert['expiry_date']);
                                                            $today = time();
                                                            if ($expiry < $today) {
                                                                echo '<span class="badge bg-danger">' . formatDate($cert['expiry_date']) . '</span>';
                                                            } elseif ($expiry < strtotime('+30 days')) {
                                                                echo '<span class="badge bg-warning">' . formatDate($cert['expiry_date']) . '</span>';
                                                            } else {
                                                                echo '<span class="badge bg-success">' . formatDate($cert['expiry_date']) . '</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $cert['status'] === 'valid' ? 'success' : ($cert['status'] === 'expired' ? 'danger' : 'warning'); ?>">
                                                                <?php echo ucfirst($cert['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo ucfirst($cert['medical_status']); ?></td>
                                                        <td>
                                                            <?php if ($cert['document_path']): ?>
                                                                <a href="<?php echo BASE_URL; ?>assets/uploads/medical/<?php echo $cert['document_path']; ?>" 
                                                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                                                    <i class="bi bi-download me-1"></i>Download
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">No file</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php elseif ($nic): ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        No worker found with NIC: <?php echo $nic; ?>. Please contact your PHI for registration.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Information</h5>
                            </div>
                            <div class="card-body">
                                <h6>Medical Certificate Requirements</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Valid for 1 year</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Must be renewed before expiry</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Required for all food handlers</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Issued by registered PHI</li>
                                </ul>
                                <hr>
                                <h6>Health Requirements</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2"><i class="bi bi-heart-pulse text-danger me-2"></i>No communicable diseases</li>
                                    <li class="mb-2"><i class="bi bi-hand-thumbs-up text-success me-2"></i>Good personal hygiene</li>
                                    <li class="mb-2"><i class="bi bi-shield-check text-success me-2"></i>Fit for food handling</li>
                                </ul>
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
