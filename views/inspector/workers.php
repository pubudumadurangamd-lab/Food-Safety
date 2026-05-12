<?php
/**
 * Inspector - Manage Workers & Medical Certificates
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/WorkerController.php';
requireMinimumRole(ROLES['inspector']);

$workerController = new WorkerController();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_worker'])) {
        $result = $workerController->create($_POST);
        setFlashMessage($result['success'] ? 'success' : 'danger', $result['message']);
    } elseif (isset($_POST['add_certificate'])) {
        $result = $workerController->addCertificate($_POST, $_FILES);
        setFlashMessage($result['success'] ? 'success' : 'danger', $result['message']);
    }
    redirect('views/inspector/workers.php');
}

$flash = getFlashMessage();

$filters = [
    'search' => sanitize($_GET['search'] ?? ''),
    'workplace_id' => sanitize($_GET['workplace_id'] ?? ''),
    'status' => sanitize($_GET['status'] ?? '')
];
$page = (int)($_GET['page'] ?? 1);

$result = $workerController->getAll($filters, $page);
$workers = $result['data'];
$pagination = $result['pagination'];

$expiringCerts = $workerController->getExpiringCertificates(30)['data'] ?? [];

$pageTitle = 'Manage Workers';
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
                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                        <?php echo $flash['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4><i class="bi bi-person-workspace me-2 text-success"></i>Food Handlers / Workers</h4>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addWorkerModal">
                        <i class="bi bi-person-plus me-1"></i>Add Worker
                    </button>
                </div>

                <!-- Expiring Certificates Alert -->
                <?php if (!empty($expiringCerts)): ?>
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong><?php echo count($expiringCerts); ?></strong> medical certificates are expiring within 30 days!
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search by name or NIC..." value="<?php echo $filters['search']; ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-search me-1"></i>Filter
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="workers.php" class="btn btn-outline-secondary w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Workers Table -->
                <div class="data-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>NIC</th>
                                <th>Phone</th>
                                <th>Workplace</th>
                                <th>Designation</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($workers as $worker): ?>
                                <tr>
                                    <td><?php echo $worker['id']; ?></td>
                                    <td><?php echo $worker['full_name']; ?></td>
                                    <td><?php echo $worker['nic']; ?></td>
                                    <td><?php echo $worker['phone']; ?></td>
                                    <td><?php echo $worker['workplace_name'] ?? 'N/A'; ?></td>
                                    <td><?php echo $worker['designation'] ?? 'N/A'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $worker['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($worker['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" 
                                                    data-bs-target="#addCertModal<?php echo $worker['id']; ?>" title="Add Certificate">
                                                <i class="bi bi-file-medical"></i>
                                            </button>
                                            <a href="view-worker.php?id=<?php echo $worker['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Add Certificate Modal -->
                                <div class="modal fade" id="addCertModal<?php echo $worker['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">Add Medical Certificate - <?php echo $worker['full_name']; ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="workers.php" enctype="multipart/form-data">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="add_certificate" value="1">
                                                <input type="hidden" name="worker_id" value="<?php echo $worker['id']; ?>">
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Certificate Number *</label>
                                                        <input type="text" class="form-control" name="certificate_number" required>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Issue Date *</label>
                                                            <input type="date" class="form-control" name="issue_date" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Expiry Date *</label>
                                                            <input type="date" class="form-control" name="expiry_date" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Medical Status *</label>
                                                        <select class="form-select" name="medical_status" required>
                                                            <option value="fit">Fit for Work</option>
                                                            <option value="unfit">Unfit for Work</option>
                                                            <option value="conditional">Conditional</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Notes</label>
                                                        <textarea class="form-control" name="notes" rows="2"></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Upload Certificate PDF</label>
                                                        <input type="file" class="form-control" name="document" accept=".pdf,.jpg,.jpeg,.png">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">Add Certificate</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($pagination['totalPages'] > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add Worker Modal -->
    <div class="modal fade" id="addWorkerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Register New Worker</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="workers.php">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="add_worker" value="1">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">NIC Number *</label>
                            <input type="text" class="form-control" name="nic" required placeholder="e.g., 198512345678 or 199023456789">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" name="phone" required placeholder="07X XXX XXXX">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Workplace</label>
                            <input type="number" class="form-control" name="workplace_id" placeholder="Premises ID (if known)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Designation</label>
                            <input type="text" class="form-control" name="designation" placeholder="e.g., Chef, Waiter, Cleaner">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Register Worker</button>
                    </div>
                </form>
            </div>
        </div>
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
