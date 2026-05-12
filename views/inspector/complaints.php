<?php
/**
 * Inspector - Manage Complaints
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/ComplaintController.php';
requireMinimumRole(ROLES['inspector']);

$complaintController = new ComplaintController();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $result = $complaintController->updateStatus((int)$_POST['complaint_id'], $_POST);
    setFlashMessage($result['success'] ? 'success' : 'danger', $result['message']);
    redirect('views/inspector/complaints.php');
}

$flash = getFlashMessage();

$filters = [
    'status' => sanitize($_GET['status'] ?? ''),
    'category' => sanitize($_GET['category'] ?? ''),
    'search' => sanitize($_GET['search'] ?? '')
];
$page = (int)($_GET['page'] ?? 1);

$result = $complaintController->getAll($filters, $page);
$complaints = $result['data'];
$pagination = $result['pagination'];

$categories = COMPLAINT_CATEGORIES;
$statuses = ['pending' => 'Pending', 'under_investigation' => 'Under Investigation', 'resolved' => 'Resolved', 'rejected' => 'Rejected'];

$pageTitle = 'Manage Complaints';
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
                    <h4><i class="bi bi-exclamation-triangle me-2 text-success"></i>Public Complaints</h4>
                </div>

                <!-- Filters -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">All Statuses</option>
                                    <?php foreach ($statuses as $key => $label): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $filters['status'] === $key ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $key => $label): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $filters['category'] === $key ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search..." value="<?php echo $filters['search']; ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Complaints Table -->
                <div class="data-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Shop</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as $c): ?>
                                <tr>
                                    <td>#<?php echo $c['id']; ?></td>
                                    <td><?php echo $c['shop_name']; ?></td>
                                    <td><?php echo $categories[$c['category']] ?? $c['category']; ?></td>
                                    <td><?php echo substr($c['description'], 0, 100); ?>...</td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $c['status'] === 'resolved' ? 'success' : 
                                                ($c['status'] === 'pending' ? 'warning' : 
                                                ($c['status'] === 'rejected' ? 'danger' : 'info')); ?>">
                                            <?php echo $statuses[$c['status']] ?? $c['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($c['created_at']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                                data-bs-target="#updateModal<?php echo $c['id']; ?>">
                                            <i class="bi bi-pencil me-1"></i>Update
                                        </button>
                                    </td>
                                </tr>

                                <!-- Update Modal -->
                                <div class="modal fade" id="updateModal<?php echo $c['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">Update Complaint #<?php echo $c['id']; ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="complaints.php">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="complaint_id" value="<?php echo $c['id']; ?>">
                                                <input type="hidden" name="update_status" value="1">
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Status *</label>
                                                        <select class="form-select" name="status" required>
                                                            <?php foreach ($statuses as $key => $label): ?>
                                                                <option value="<?php echo $key; ?>" <?php echo $c['status'] === $key ? 'selected' : ''; ?>>
                                                                    <?php echo $label; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Resolution Notes</label>
                                                        <textarea class="form-control" name="resolution" rows="3" 
                                                                  placeholder="Enter resolution details or investigation notes..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">Update Status</button>
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
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($filters['status']); ?>&category=<?php echo urlencode($filters['category']); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
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
