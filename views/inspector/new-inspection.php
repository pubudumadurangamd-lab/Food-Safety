<?php
/**
 * New Inspection Form
 * Food Safety & Premises Grading Management System
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/InspectionController.php';
require_once __DIR__ . '/../../controllers/PremisesController.php';
requireMinimumRole(ROLES['inspector']);

$inspectionController = new InspectionController();
$premisesController = new PremisesController();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $inspectionController->create($_POST, $_FILES);
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
        redirect('views/inspector/inspections.php');
    } else {
        setFlashMessage('danger', $result['message']);
    }
}

$flash = getFlashMessage();

// Get premises list for dropdown
$premisesList = $premisesController->getList([], 1, 100)['data'] ?? [];

// Inspection criteria with H800 weights
$criteria = [
    'cleanliness' => ['label' => 'Cleanliness', 'weight' => 15, 'description' => 'Overall cleanliness of the premises'],
    'food_storage' => ['label' => 'Food Storage', 'weight' => 15, 'description' => 'Proper food storage conditions'],
    'employee_hygiene' => ['label' => 'Employee Hygiene', 'weight' => 15, 'description' => 'Personal hygiene of food handlers'],
    'waste_management' => ['label' => 'Waste Management', 'weight' => 10, 'description' => 'Waste disposal practices'],
    'pest_control' => ['label' => 'Pest Control', 'weight' => 10, 'description' => 'Pest control measures'],
    'water_supply' => ['label' => 'Water Supply', 'weight' => 10, 'description' => 'Safe water supply'],
    'food_handling' => ['label' => 'Food Handling', 'weight' => 15, 'description' => 'Proper food handling practices'],
    'kitchen_condition' => ['label' => 'Kitchen Condition', 'weight' => 5, 'description' => 'Kitchen equipment and condition'],
    'temperature_control' => ['label' => 'Temperature Control', 'weight' => 5, 'description' => 'Refrigeration and temperature control']
];

$pageTitle = 'New Inspection';
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

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-clipboard-plus me-2"></i>New Food Safety Inspection</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="new-inspection.php" enctype="multipart/form-data" id="inspectionForm">
                                    <?php echo csrfField(); ?>

                                    <!-- Premises Selection -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Select Premises *</label>
                                        <select class="form-select form-select-lg" name="premises_id" required>
                                            <option value="">-- Select Premises --</option>
                                            <?php foreach ($premisesList as $premises): ?>
                                                <option value="<?php echo $premises['id']; ?>">
                                                    <?php echo $premises['shop_name']; ?> (<?php echo $premises['district']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Inspection Date *</label>
                                            <input type="date" class="form-control" name="inspection_date" 
                                                   value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Next Inspection Date</label>
                                            <input type="date" class="form-control" name="next_inspection_date" 
                                                   value="<?php echo date('Y-m-d', strtotime('+6 months')); ?>">
                                        </div>
                                    </div>

                                    <hr class="my-4">
                                    <h6 class="mb-3"><i class="bi bi-list-check me-2 text-success"></i>H800 Inspection Criteria</h6>
                                    <p class="text-muted small mb-4">Rate each criteria from 1 (Poor) to 5 (Excellent). Weights are shown for reference.</p>

                                    <!-- Criteria Scoring -->
                                    <?php foreach ($criteria as $key => $item): ?>
                                        <div class="criteria-item mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <span class="fw-bold"><?php echo $item['label']; ?></span>
                                                    <span class="badge bg-secondary ms-2">Weight: <?php echo $item['weight']; ?>%</span>
                                                </div>
                                                <span class="text-muted small"><?php echo $item['description']; ?></span>
                                            </div>
                                            <div class="criteria-score" id="score-<?php echo $key; ?>">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <label class="score-btn">
                                                        <input type="radio" name="<?php echo $key; ?>" value="<?php echo $i; ?>" 
                                                               class="d-none" required onchange="calculateGrade()">
                                                        <?php echo $i; ?>
                                                    </label>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- Grade Preview -->
                                    <div class="alert alert-info mb-4" id="gradePreview">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <strong>Calculated Grade:</strong>
                                                <span id="calculatedGrade" class="badge bg-secondary fs-5 ms-2">--</span>
                                            </div>
                                            <div>
                                                <strong>Score:</strong> <span id="calculatedScore">0</span>%
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div class="mb-4">
                                        <label class="form-label">Inspection Notes</label>
                                        <textarea class="form-control" name="notes" rows="4" 
                                                  placeholder="Enter any additional observations or recommendations..."></textarea>
                                    </div>

                                    <!-- Photos -->
                                    <div class="mb-4">
                                        <label class="form-label">Upload Photos</label>
                                        <input type="file" class="form-control" name="photos[]" multiple 
                                               accept="image/*" onchange="previewImages(this)">
                                        <div class="form-text">Upload photos of the premises (Max 5MB each)</div>
                                        <div id="imagePreview" class="row g-2 mt-2"></div>
                                    </div>

                                    <div class="d-flex gap-3">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="bi bi-check-circle me-2"></i>Submit Inspection
                                        </button>
                                        <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">
                                            <i class="bi bi-x-circle me-2"></i>Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Info -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Grading Guide</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Grade</th>
                                            <th>Score</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="badge bg-success">A</span></td>
                                            <td>90-100%</td>
                                            <td>Excellent</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-info">B</span></td>
                                            <td>75-89%</td>
                                            <td>Good</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-warning">C</span></td>
                                            <td>60-74%</td>
                                            <td>Satisfactory</td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-danger">D</span></td>
                                            <td>Below 60%</td>
                                            <td>Needs Improvement</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Scoring Tips</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i><strong>5:</strong> Excellent - Exceeds standards</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-info me-2"></i><strong>4:</strong> Good - Meets standards</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-warning me-2"></i><strong>3:</strong> Satisfactory - Minor issues</li>
                                    <li class="mb-2"><i class="bi bi-x-circle text-warning me-2"></i><strong>2:</strong> Poor - Needs improvement</li>
                                    <li class="mb-0"><i class="bi bi-x-circle text-danger me-2"></i><strong>1:</strong> Critical - Immediate action</li>
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
        // Grade weights
        const weights = {
            cleanliness: 15,
            food_storage: 15,
            employee_hygiene: 15,
            waste_management: 10,
            pest_control: 10,
            water_supply: 10,
            food_handling: 15,
            kitchen_condition: 5,
            temperature_control: 5
        };

        function calculateGrade() {
            let totalScore = 0;
            let totalWeight = 0;

            for (let criteria in weights) {
                const selected = document.querySelector(`input[name="${criteria}"]:checked`);
                if (selected) {
                    const score = parseInt(selected.value);
                    totalScore += (score * weights[criteria]);
                    totalWeight += weights[criteria];
                }
            }

            if (totalWeight === 0) return;

            const percentage = (totalScore / (totalWeight * 5)) * 100;

            let grade = 'D';
            let gradeClass = 'bg-danger';

            if (percentage >= 90) {
                grade = 'A';
                gradeClass = 'bg-success';
            } else if (percentage >= 75) {
                grade = 'B';
                gradeClass = 'bg-info';
            } else if (percentage >= 60) {
                grade = 'C';
                gradeClass = 'bg-warning';
            }

            document.getElementById('calculatedGrade').textContent = grade;
            document.getElementById('calculatedGrade').className = `badge ${gradeClass} fs-5 ms-2`;
            document.getElementById('calculatedScore').textContent = percentage.toFixed(1);
        }

        // Score button styling
        document.querySelectorAll('.score-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const name = this.querySelector('input').name;
                document.querySelectorAll(`input[name="${name}"]`).forEach(input => {
                    input.parentElement.classList.remove('active');
                });
                this.classList.add('active');
            });
        });

        // Image preview
        function previewImages(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';

            if (input.files) {
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML += `
                            <div class="col-4">
                                <img src="${e.target.result}" class="img-thumbnail" style="height: 100px; object-fit: cover;">
                            </div>
                        `;
                    };
                    reader.readAsDataURL(file);
                });
            }
        }

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
