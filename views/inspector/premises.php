<?php
/**
 * Inspector - Manage Premises
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/PremisesController.php';
requireMinimumRole(ROLES['inspector']);

$premisesController = new PremisesController();

$filters = [
    'search' => sanitize($_GET['search'] ?? ''),
    'district' => sanitize($_GET['district'] ?? ''),
    'grade' => sanitize($_GET['grade'] ?? '')
];
$page = (int)($_GET['page'] ?? 1);

$result = $premisesController->getList($filters, $page);
$premises = $result['data'];
$pagination = $result['pagination'];

// Sri Lanka Districts
$districts = [
    'Colombo', 'Gampaha', 'Kalutara', 'Kandy', 'Matale', 'Nuwara Eliya',
    'Galle', 'Matara', 'Hambantota', 'Jaffna', 'Kilinochchi', 'Mannar',
    'Vavuniya', 'Mullaitivu', 'Batticaloa', 'Ampara', 'Trincomalee',
    'Kurunegala', 'Puttalam', 'Anuradhapura', 'Polonnaruwa', 'Badulla',
    'Monaragala', 'Ratnapura', 'Kegalle'
];

$pageTitle = 'Manage Premises';
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
                    <h4><i class="bi bi-shop me-2 text-success"></i>Food Premises</h4>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPremisesModal">
                        <i class="bi bi-plus-circle me-1"></i>Register Premises
                    </button>
                </div>

                <!-- Filters -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search..." value="<?php echo $filters['search']; ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="district">
                                    <option value="">All Districts</option>
                                    <?php foreach ($districts as $district): ?>
                                        <option value="<?php echo $district; ?>" <?php echo $filters['district'] === $district ? 'selected' : ''; ?>>
                                            <?php echo $district; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="grade">
                                    <option value="">All Grades</option>
                                    <option value="A" <?php echo $filters['grade'] === 'A' ? 'selected' : ''; ?>>Grade A</option>
                                    <option value="B" <?php echo $filters['grade'] === 'B' ? 'selected' : ''; ?>>Grade B</option>
                                    <option value="C" <?php echo $filters['grade'] === 'C' ? 'selected' : ''; ?>>Grade C</option>
                                    <option value="D" <?php echo $filters['grade'] === 'D' ? 'selected' : ''; ?>>Grade D</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-search me-1"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Premises Table -->
                <div class="data-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Shop Name</th>
                                <th>Owner</th>
                                <th>District</th>
                                <th>Grade</th>
                                <th>Status</th>
                                <th>Inspection</th>
                                <th>Expiry</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($premises as $p): ?>
                                <tr>
                                    <td><?php echo $p['id']; ?></td>
                                    <td><?php echo $p['shop_name']; ?></td>
                                    <td><?php echo $p['owner_name']; ?></td>
                                    <td><?php echo $p['district']; ?></td>
                                    <td><?php echo getGradeBadge($p['grade']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $p['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($p['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($p['inspection_date']); ?></td>
                                    <td>
                                        <?php 
                                        $expiry = strtotime($p['expiry_date']);
                                        $today = time();
                                        if ($expiry < $today) {
                                            echo '<span class="badge bg-danger">Expired</span>';
                                        } elseif ($expiry < strtotime('+30 days')) {
                                            echo '<span class="badge bg-warning">Soon</span>';
                                        } else {
                                            echo formatDate($p['expiry_date']);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="new-inspection.php?premises=<?php echo $p['id']; ?>" 
                                               class="btn btn-sm btn-outline-success" title="New Inspection">
                                                <i class="bi bi-clipboard-plus"></i>
                                            </a>
                                            <a href="edit-premises.php?id=<?php echo $p['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>views/public/premises-details.php?id=<?php echo $p['id']; ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-info" title="View Public">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['totalPages'] > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($filters['search']); ?>&district=<?php echo urlencode($filters['district']); ?>&grade=<?php echo urlencode($filters['grade']); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add Premises Modal -->
    <div class="modal fade" id="addPremisesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Register New Premises</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="create-premises.php" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Shop Name *</label>
                                <input type="text" class="form-control" name="shop_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Owner Name *</label>
                                <input type="text" class="form-control" name="owner_name" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address *</label>
                            <textarea class="form-control" name="address" rows="2" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Contact Number *</label>
                                <input type="tel" class="form-control" name="contact_number" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Business Type *</label>
                                <select class="form-select" name="business_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Restaurant">Restaurant</option>
                                    <option value="Hotel">Hotel</option>
                                    <option value="Bakery">Bakery</option>
                                    <option value="Fast Food">Fast Food</option>
                                    <option value="Cafe">Cafe</option>
                                    <option value="Supermarket">Supermarket</option>
                                    <option value="Grocery">Grocery</option>
                                    <option value="Food Factory">Food Factory</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">License Number *</label>
                                <input type="text" class="form-control" name="license_number" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">District *</label>
                                <select class="form-select" name="district" required>
                                    <option value="">Select District</option>
                                    <?php foreach ($districts as $district): ?>
                                        <option value="<?php echo $district; ?>"><?php echo $district; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Photos</label>
                                <input type="file" class="form-control" name="photos[]" multiple accept="image/*">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">GPS Latitude</label>
                                <input type="text" class="form-control" name="gps_lat" placeholder="6.9271">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">GPS Longitude</label>
                                <input type="text" class="form-control" name="gps_lng" placeholder="79.8612">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Register Premises</button>
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
