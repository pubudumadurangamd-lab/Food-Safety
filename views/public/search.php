<?php
/**
 * Public Search Page
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/PremisesController.php';

$premisesController = new PremisesController();

// Get search parameters
$searchQuery = sanitize($_GET['search'] ?? '');
$districtFilter = sanitize($_GET['district'] ?? '');
$gradeFilter = sanitize($_GET['grade'] ?? '');
$page = (int)($_GET['page'] ?? 1);
$perPage = 12;

$results = $premisesController->searchPublic($searchQuery, $districtFilter, $gradeFilter);
$totalResults = count($results);
$paginatedResults = array_slice($results, ($page - 1) * $perPage, $perPage);
$totalPages = ceil($totalResults / $perPage);

// Sri Lanka Districts
$districts = [
    'Colombo', 'Gampaha', 'Kalutara', 'Kandy', 'Matale', 'Nuwara Eliya',
    'Galle', 'Matara', 'Hambantota', 'Jaffna', 'Kilinochchi', 'Mannar',
    'Vavuniya', 'Mullaitivu', 'Batticaloa', 'Ampara', 'Trincomalee',
    'Kurunegala', 'Puttalam', 'Anuradhapura', 'Polonnaruwa', 'Badulla',
    'Monaragala', 'Ratnapura', 'Kegalle'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Food Premises - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="public-portal">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="bi bi-shield-check me-2"></i><?php echo SITE_SHORT; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="search.php">Search Shops</a></li>
                    <li class="nav-item"><a class="nav-link" href="complaint.php">Submit Complaint</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h2 class="text-center mb-4"><i class="bi bi-search text-success me-2"></i>Search Food Premises</h2>
                <p class="text-center text-muted mb-5">Find food safety grades for restaurants, hotels, and food businesses across Sri Lanka</p>

                <!-- Search Form -->
                <div class="card shadow border-0 mb-5">
                    <div class="card-body p-4">
                        <form method="GET" action="search.php">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control form-control-lg" name="search" 
                                               placeholder="Shop name..." value="<?php echo $searchQuery; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select form-select-lg" name="district">
                                        <option value="">All Districts</option>
                                        <?php foreach ($districts as $district): ?>
                                            <option value="<?php echo $district; ?>" <?php echo $districtFilter === $district ? 'selected' : ''; ?>>
                                                <?php echo $district; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select form-select-lg" name="grade">
                                        <option value="">All Grades</option>
                                        <option value="A" <?php echo $gradeFilter === 'A' ? 'selected' : ''; ?>>Grade A - Excellent</option>
                                        <option value="B" <?php echo $gradeFilter === 'B' ? 'selected' : ''; ?>>Grade B - Good</option>
                                        <option value="C" <?php echo $gradeFilter === 'C' ? 'selected' : ''; ?>>Grade C - Satisfactory</option>
                                        <option value="D" <?php echo $gradeFilter === 'D' ? 'selected' : ''; ?>>Grade D - Needs Improvement</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success btn-lg w-100">
                                        <i class="bi bi-search"></i> Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Results -->
                <?php if ($searchQuery || $districtFilter || $gradeFilter): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5>Results: <?php echo $totalResults; ?> premises found</h5>
                        <?php if ($totalResults > 0): ?>
                            <span class="text-muted">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($paginatedResults)): ?>
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle fs-1 d-block mb-3"></i>
                            <h5>No premises found</h5>
                            <p>Try adjusting your search criteria or filters.</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($paginatedResults as $premises): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card premises-card h-100 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h5 class="card-title mb-0"><?php echo $premises['shop_name']; ?></h5>
                                                <?php echo getGradeBadge($premises['grade']); ?>
                                            </div>
                                            <p class="text-muted mb-2">
                                                <i class="bi bi-geo-alt me-1"></i><?php echo $premises['district']; ?>
                                            </p>
                                            <p class="text-muted mb-2">
                                                <i class="bi bi-map me-1"></i><?php echo $premises['address']; ?>
                                            </p>
                                            <p class="text-muted mb-3">
                                                <i class="bi bi-calendar me-1"></i>Inspected: <?php echo formatDate($premises['inspection_date']); ?>
                                            </p>
                                            <div class="d-flex gap-2">
                                                <span class="badge bg-<?php echo $premises['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($premises['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <a href="premises-details.php?id=<?php echo $premises['id']; ?>" 
                                               class="btn btn-outline-success btn-sm w-100">
                                                <i class="bi bi-eye me-1"></i>View Details & QR Code
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?search=<?php echo urlencode($searchQuery); ?>&district=<?php echo urlencode($districtFilter); ?>&grade=<?php echo urlencode($gradeFilter); ?>&page=<?php echo $page - 1; ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?search=<?php echo urlencode($searchQuery); ?>&district=<?php echo urlencode($districtFilter); ?>&grade=<?php echo urlencode($gradeFilter); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?search=<?php echo urlencode($searchQuery); ?>&district=<?php echo urlencode($districtFilter); ?>&grade=<?php echo urlencode($gradeFilter); ?>&page=<?php echo $page + 1; ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-search fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">Enter search criteria to find food premises</h5>
                        <p class="text-muted">You can search by shop name, filter by district, or filter by grade</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Government of Sri Lanka - Ministry of Health</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
