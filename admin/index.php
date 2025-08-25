<?php
// Start session and check authentication first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: auth/login.php');
    exit;
}

// Check session timeout (24 hours)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 86400) {
    session_destroy();
    header('Location: auth/login.php');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

// Include required files
require_once '../api/models/FormulationModel.php';

// Get dashboard statistics
$formulationModel = new FormulationModel();
$stats = $formulationModel->getFormulationStats();
$recentFormulations = $formulationModel->getAllFormulations(5, 0);

// Calculate totals
$totalFormulations = 0;
$uniqueCustomers = 0;
$formatBreakdown = [];

if ($stats) {
    foreach ($stats as $stat) {
        $totalFormulations += $stat['format_count'];
        $formatBreakdown[$stat['base_format']] = $stat['format_count'];
    }
    
    // Get unique customers count
    try {
        $database = new Database();
        $conn = $database->getConnection();
        $stmt = $conn->query("SELECT COUNT(DISTINCT customer_email) as unique_customers FROM formulations");
        $result = $stmt->fetch();
        $uniqueCustomers = $result['unique_customers'] ?? 0;
    } catch (Exception $e) {
        $uniqueCustomers = 0;
    }
}

// Get today's formulations
$todayFormulations = 0;
try {
    $database = new Database();
    $conn = $database->getConnection();
    $stmt = $conn->query("SELECT COUNT(*) as today_count FROM formulations WHERE DATE(created_at) = CURDATE()");
    $result = $stmt->fetch();
    $todayFormulations = $result['today_count'] ?? 0;
} catch (Exception $e) {
    $todayFormulations = 0;
}

include 'includes/header.php';
?>

<!-- Dashboard Stats Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-primary mb-2">
                    <i class="bi bi-clipboard-data" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-muted mb-1">Total Formulations</h5>
                <h3 class="mb-0"><?php echo $totalFormulations; ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-success mb-2">
                    <i class="bi bi-people" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-muted mb-1">Unique Customers</h5>
                <h3 class="mb-0"><?php echo $uniqueCustomers; ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-warning mb-2">
                    <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-muted mb-1">Today's Formulations</h5>
                <h3 class="mb-0"><?php echo $todayFormulations; ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-info mb-2">
                    <i class="bi bi-droplet-half" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-muted mb-1">Product Types</h5>
                <h3 class="mb-0"><?php echo count($formatBreakdown); ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Format Breakdown Chart -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Product Format Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="formatChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Formulations</h5>
            </div>
            <div class="card-body">
                <?php if ($recentFormulations && isset($recentFormulations['formulations'])): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($recentFormulations['formulations'], 0, 5) as $formulation): ?>
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($formulation['customer_name']); ?></h6>
                                        <p class="mb-1 text-muted small">
                                            <?php echo ucfirst($formulation['base_format']); ?> for 
                                            <?php echo implode(', ', $formulation['skin_types']); ?> skin
                                        </p>
                                        <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($formulation['created_at'])); ?></small>
                                    </div>
                                    <span class="badge bg-primary"><?php echo ucfirst($formulation['base_format']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="formulations.php" class="btn btn-outline-primary btn-sm">View All Formulations</a>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox display-1"></i>
                        <p class="mt-2">No formulations found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



<script>
// Initialize format breakdown chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('formatChart');
    if (ctx) {
        const formatData = <?php echo json_encode($formatBreakdown); ?>;
        const labels = Object.keys(formatData);
        const data = Object.values(formatData);
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
                datasets: [{
                    data: data,
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(118, 75, 162, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(32, 201, 151, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>