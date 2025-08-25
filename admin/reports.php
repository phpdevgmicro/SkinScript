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

$pageTitle = 'Reports & Analytics';
$currentPage = 'reports';

// Include required files
require_once '../api/models/FormulationModel.php';

// Get analytics data
$formulationModel = new FormulationModel();

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get formulations by month
    $monthlyData = [];
    $sql = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count 
            FROM formulations 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
            ORDER BY month";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $monthlyResults = $stmt->fetchAll();
    
    foreach ($monthlyResults as $result) {
        $monthlyData[$result['month']] = $result['count'];
    }
    
    // Get format statistics
    $formatStats = [];
    $sql = "SELECT base_format, COUNT(*) as count FROM formulations GROUP BY base_format ORDER BY count DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $formatResults = $stmt->fetchAll();
    
    foreach ($formatResults as $result) {
        $formatStats[$result['base_format']] = $result['count'];
    }
    
    // Get skin type popularity
    $skinTypeStats = [];
    $sql = "SELECT skin_types FROM formulations";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $skinTypeResults = $stmt->fetchAll();
    
    foreach ($skinTypeResults as $result) {
        $types = json_decode($result['skin_types'], true);
        if (is_array($types)) {
            foreach ($types as $type) {
                $skinTypeStats[$type] = ($skinTypeStats[$type] ?? 0) + 1;
            }
        }
    }
    arsort($skinTypeStats);
    
    // Get most popular ingredients
    $ingredientStats = [];
    $sql = "SELECT key_actives FROM formulations";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $ingredientResults = $stmt->fetchAll();
    
    foreach ($ingredientResults as $result) {
        $actives = json_decode($result['key_actives'], true);
        if (is_array($actives)) {
            foreach ($actives as $active) {
                $ingredientStats[$active] = ($ingredientStats[$active] ?? 0) + 1;
            }
        }
    }
    arsort($ingredientStats);
    
    // Get recent activity (last 30 days)
    $sql = "SELECT COUNT(*) as count FROM formulations WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $recentActivity = $stmt->fetch()['count'];
    
    // Get peak hour analysis
    $sql = "SELECT 
                HOUR(created_at) as hour,
                COUNT(*) as count 
            FROM formulations 
            GROUP BY HOUR(created_at) 
            ORDER BY count DESC 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $peakHourData = $stmt->fetch();
    $peakHour = $peakHourData ? $peakHourData['hour'] : 0;
    
} catch (Exception $e) {
    $error = $e->getMessage();
    $monthlyData = [];
    $formatStats = [];
    $skinTypeStats = [];
    $ingredientStats = [];
    $recentActivity = 0;
    $peakHour = 0;
}

include 'includes/header.php';
?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i>
        Error loading analytics data: <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<!-- Analytics Summary Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-primary mb-2">
                    <i class="bi bi-calendar-range" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-muted mb-1">Last 30 Days</h5>
                <h3 class="mb-0"><?php echo $recentActivity; ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-success mb-2">
                    <i class="bi bi-clock" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-muted mb-1">Peak Hour</h5>
                <h3 class="mb-0"><?php echo $peakHour; ?>:00</h3>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-warning mb-2">
                    <i class="bi bi-droplet-half" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-muted mb-1">Product Formats</h5>
                <h3 class="mb-0"><?php echo count($formatStats); ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-info mb-2">
                    <i class="bi bi-grid-3x3-gap" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-muted mb-1">Unique Ingredients</h5>
                <h3 class="mb-0"><?php echo count($ingredientStats); ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Monthly Trend Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up"></i>
                    Formulations Trend (Last 12 Months)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Format Distribution -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pie-chart"></i>
                    Format Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="formatChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Popular Skin Types -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Popular Skin Types</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($skinTypeStats)): ?>
                    <div class="row">
                        <?php foreach (array_slice($skinTypeStats, 0, 6, true) as $type => $count): ?>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold"><?php echo ucfirst($type); ?></span>
                                    <span class="badge bg-primary"><?php echo $count; ?></span>
                                </div>
                                <div class="progress mt-1" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo max(10, ($count / max($skinTypeStats)) * 100); ?>%">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox"></i>
                        <p class="mt-2 mb-0">No data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Popular Ingredients -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Top Ingredients</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($ingredientStats)): ?>
                    <div class="row">
                        <?php foreach (array_slice($ingredientStats, 0, 8, true) as $ingredient => $count): ?>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-truncate"><?php echo ucfirst($ingredient); ?></span>
                                    <span class="badge bg-success"><?php echo $count; ?></span>
                                </div>
                                <div class="progress mt-1" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?php echo max(10, ($count / max($ingredientStats)) * 100); ?>%">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox"></i>
                        <p class="mt-2 mb-0">No data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



<script>
// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    // Monthly trend chart
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        const monthlyData = <?php echo json_encode($monthlyData); ?>;
        
        // Generate last 12 months labels
        const labels = [];
        const data = [];
        const currentDate = new Date();
        
        for (let i = 11; i >= 0; i--) {
            const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
            const monthKey = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
            const monthLabel = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            
            labels.push(monthLabel);
            data.push(monthlyData[monthKey] || 0);
        }
        
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Formulations',
                    data: data,
                    borderColor: 'rgba(102, 126, 234, 1)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
    
    // Format distribution chart
    const formatCtx = document.getElementById('formatChart');
    if (formatCtx) {
        const formatData = <?php echo json_encode($formatStats); ?>;
        
        new Chart(formatCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(formatData).map(label => label.charAt(0).toUpperCase() + label.slice(1)),
                datasets: [{
                    data: Object.values(formatData),
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

function exportReport(type) {
    // This would typically make an API call to generate and download the report
    showAlert(`Exporting ${type} report...`, 'info');
    
    // For now, we'll just show a success message
    setTimeout(() => {
        showAlert(`${type.charAt(0).toUpperCase() + type.slice(1)} report exported successfully!`, 'success');
    }, 2000);
}
</script>

<?php include 'includes/footer.php'; ?>