<?php
/**
 * Inspector - Inspection History
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/InspectionController.php';
requireMinimumRole(ROLES['inspector']);

$inspectionController = new InspectionController();
$inspectorId = getCurrentUserId();
$page = (int)($_GET['page'] ?? 1);

$result = $inspectionController->getList($inspectorId, $page);
$inspections = $result['data'];
$pagination = $result['pagination'];

$pageTitle = 'Inspection History';
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
                    <h4><i class="bi bi-clipboard-check me-2 text-success"></i>Inspection History</h4>
                    <a href="new-inspection.php" class="btn btn-success">
                        <i class="bi bi-clipboard-plus me-1"></i>New Inspection
                    </a>
                </div>

                <div class="data-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Premises</th>
                                <th>Date</th>
                                <th>Grade</th>
                                <th>Score</th>
                                <th>Inspector</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inspections as $inspection): ?>
                                <tr>
                                    <td>#<?php echo $inspection['id']; ?></td>
                                    <td><?php echo $inspection['shop_name']; ?></td>
                                    <td><?php echo formatDate($inspection['inspection_date']); ?></td>
                                    <td><?php echo getGradeBadge($inspection['grade']); ?></td>
                                    <td><?php echo $inspection['total_score']; ?>%</td>
                                    <td><?php echo $inspection['inspector_name']; ?></td>
                                    <td>
                                        <a href="view-inspection.php?id=<?php echo $inspection['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
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
