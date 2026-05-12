<?php
/**
 * Public Portal - Food Safety & Premises Grading Management System
 * Sri Lanka Health Department
 */

require_once 'config/config.php';
require_once 'controllers/PremisesController.php';

$premisesController = new PremisesController();
$gradeStats = $premisesController->getStats()['grade_stats'] ?? [];

// Get search parameters
$searchQuery = sanitize($_GET['search'] ?? '');
$districtFilter = sanitize($_GET['district'] ?? '');
$gradeFilter = sanitize($_GET['grade'] ?? '');

$searchResults = [];
if ($searchQuery || $districtFilter || $gradeFilter) {
    $searchResults = $premisesController->searchPublic($searchQuery, $districtFilter, $gradeFilter)['data'] ?? [];
}

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
    <title><?php echo SITE_NAME; ?> - Sri Lanka</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="public-portal">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shield-check me-2"></i>
                <?php echo SITE_SHORT; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="bi bi-house me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="views/public/search.php"><i class="bi bi-search me-1"></i> Search Shops</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="views/public/complaint.php"><i class="bi bi-exclamation-triangle me-1"></i> Submit Complaint</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php"><i class="bi bi-person-plus me-1"></i> Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-success mb-3">
                        Food Safety &<br>Premises Grading
                    </h1>
                    <p class="lead text-muted mb-4">
                        Ensuring food safety standards across Sri Lanka. 
                        Search for food premises grades, submit complaints, and track food safety compliance.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="views/public/search.php" class="btn btn-success btn-lg">
                            <i class="bi bi-search me-2"></i>Search Food Premises
                        </a>
                        <a href="views/public/complaint.php" class="btn btn-outline-success btn-lg">
                            <i class="bi bi-exclamation-triangle me-2"></i>File Complaint
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="grade-showcase">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="grade-card grade-a">
                                    <div class="grade-letter">A</div>
                                    <div class="grade-label">Excellent</div>
                                    <div class="grade-score">90-100%</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="grade-card grade-b">
                                    <div class="grade-letter">B</div>
                                    <div class="grade-label">Good</div>
                                    <div class="grade-score">75-89%</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="grade-card grade-c">
                                    <div class="grade-letter">C</div>
                                    <div class="grade-label">Satisfactory</div>
                                    <div class="grade-score">60-74%</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="grade-card grade-d">
                                    <div class="grade-letter">D</div>
                                    <div class="grade-label">Needs Improvement</div>
                                    <div class="grade-score">Below 60%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Search -->
    <section class="quick-search py-5">
        <div class="container">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h3 class="card-title mb-4 text-center">
                        <i class="bi bi-search text-success me-2"></i>Quick Search Food Premises
                    </h3>
                    <form method="GET" action="index.php">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control form-control-lg" name="search" 
                                       placeholder="Search by shop name..." value="<?php echo $searchQuery; ?>">
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
                                    <option value="A" <?php echo $gradeFilter === 'A' ? 'selected' : ''; ?>>Grade A</option>
                                    <option value="B" <?php echo $gradeFilter === 'B' ? 'selected' : ''; ?>>Grade B</option>
                                    <option value="C" <?php echo $gradeFilter === 'C' ? 'selected' : ''; ?>>Grade C</option>
                                    <option value="D" <?php echo $gradeFilter === 'D' ? 'selected' : ''; ?>>Grade D</option>
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
        </div>
    </section>

    <!-- Search Results -->
    <?php if ($searchQuery || $districtFilter || $gradeFilter): ?>
    <section class="search-results py-4">
        <div class="container">
            <h4 class="mb-4">Search Results (<?php echo count($searchResults); ?> found)</h4>
            <?php if (empty($searchResults)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>No premises found matching your criteria.
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($searchResults as $premises): ?>
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
                                        <i class="bi bi-calendar me-1"></i>Inspected: <?php echo formatDate($premises['inspection_date']); ?>
                                    </p>
                                    <p class="mb-0">
                                        <span class="badge bg-<?php echo $premises['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($premises['status']); ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="views/public/premises-details.php?id=<?php echo $premises['id']; ?>" 
                                       class="btn btn-outline-success btn-sm w-100">
                                        <i class="bi bi-eye me-1"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Statistics -->
    <section class="statistics py-5 bg-light">
        <div class="container">
            <h3 class="text-center mb-5">Food Safety Statistics</h3>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stat-card text-center p-4 bg-white rounded shadow-sm">
                        <div class="stat-icon text-success mb-3">
                            <i class="bi bi-shop fs-1"></i>
                        </div>
                        <h4 class="stat-number"><?php echo $premisesController->getStats()['grade_stats'][0]['count'] ?? 0; ?></h4>
                        <p class="stat-label text-muted mb-0">Grade A Premises</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card text-center p-4 bg-white rounded shadow-sm">
                        <div class="stat-icon text-info mb-3">
                            <i class="bi bi-clipboard-check fs-1"></i>
                        </div>
                        <h4 class="stat-number"><?php echo array_sum(array_column($gradeStats, 'count')); ?></h4>
                        <p class="stat-label text-muted mb-0">Total Inspected</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card text-center p-4 bg-white rounded shadow-sm">
                        <div class="stat-icon text-warning mb-3">
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                        <h4 class="stat-number">24/7</h4>
                        <p class="stat-label text-muted mb-0">Complaint Monitoring</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card text-center p-4 bg-white rounded shadow-sm">
                        <div class="stat-icon text-primary mb-3">
                            <i class="bi bi-shield-check fs-1"></i>
                        </div>
                        <h4 class="stat-number">25</h4>
                        <p class="stat-label text-muted mb-0">Districts Covered</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works py-5">
        <div class="container">
            <h3 class="text-center mb-5">How It Works</h3>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card text-center p-4">
                        <div class="step-number">1</div>
                        <h5>Inspection</h5>
                        <p class="text-muted">PHI inspectors conduct thorough food safety inspections following H800 guidelines.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card text-center p-4">
                        <div class="step-number">2</div>
                        <h5>Grading</h5>
                        <p class="text-muted">Premises are graded A, B, C, or D based on compliance with food safety standards.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card text-center p-4">
                        <div class="step-number">3</div>
                        <h5>Public Access</h5>
                        <p class="text-muted">Public can search grades, view compliance status, and submit complaints online.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="bi bi-shield-check me-2"></i><?php echo SITE_SHORT; ?></h5>
                    <p class="small text-muted">Ministry of Health, Sri Lanka<br>
                    Ensuring food safety for all citizens.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="small text-muted mb-0">Version <?php echo VERSION; ?></p>
                    <p class="small text-muted">&copy; <?php echo date('Y'); ?> Government of Sri Lanka</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
