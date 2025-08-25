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

$pageTitle = 'Customer Management';
$currentPage = 'customers';

// Include required files
require_once '../api/models/FormulationModel.php';

// Get unique customers with their formulation counts
$formulationModel = new FormulationModel();

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get customer statistics
    $sql = "SELECT 
                customer_name,
                customer_email,
                customer_phone,
                COUNT(*) as formulation_count,
                MIN(created_at) as first_formulation,
                MAX(created_at) as last_formulation,
                GROUP_CONCAT(DISTINCT base_format) as formats_used
            FROM formulations 
            GROUP BY customer_email, customer_name 
            ORDER BY formulation_count DESC, last_formulation DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $customers = $stmt->fetchAll();
    
} catch (Exception $e) {
    $customers = [];
    $error = $e->getMessage();
}

include 'includes/header.php';
?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i>
        Error loading customer data: <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<!-- Customer Statistics -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-primary mb-2">
                    <i class="bi bi-people" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-muted mb-1">Total Customers</h5>
                <h3 class="mb-0"><?php echo count($customers); ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-success mb-2">
                    <i class="bi bi-arrow-repeat" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-muted mb-1">Returning Customers</h5>
                <h3 class="mb-0"><?php echo count(array_filter($customers, function($c) { return $c['formulation_count'] > 1; })); ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-warning mb-2">
                    <i class="bi bi-graph-up" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-muted mb-1">Avg. Formulations</h5>
                <h3 class="mb-0"><?php 
                    $avg = count($customers) > 0 ? round(array_sum(array_column($customers, 'formulation_count')) / count($customers), 1) : 0;
                    echo $avg;
                ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-info mb-2">
                    <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
                </div>
                <h5 class="text-muted mb-1">Active Today</h5>
                <h3 class="mb-0"><?php 
                    $today = date('Y-m-d');
                    echo count(array_filter($customers, function($c) use ($today) { 
                        return date('Y-m-d', strtotime($c['last_formulation'])) === $today; 
                    }));
                ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Search and Export -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control table-search" placeholder="Search customers by name or email...">
        </div>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-success" onclick="exportTableAsCSV('customersTable', 'customers.csv')">
            <i class="bi bi-download"></i>
            Export CSV
        </button>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Customer Overview</h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($customers)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 data-table" id="customersTable">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Formulations</th>
                            <th>Formats Used</th>
                            <th>First Order</th>
                            <th>Last Activity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($customer['customer_name']); ?></strong>
                                        <?php if ($customer['formulation_count'] > 1): ?>
                                            <span class="badge bg-success ms-2">Returning</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <a href="mailto:<?php echo htmlspecialchars($customer['customer_email']); ?>" 
                                           class="text-decoration-none">
                                            <i class="bi bi-envelope"></i>
                                            <?php echo htmlspecialchars($customer['customer_email']); ?>
                                        </a>
                                    </div>
                                    <?php if ($customer['customer_phone']): ?>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                <i class="bi bi-phone"></i>
                                                <?php echo htmlspecialchars($customer['customer_phone']); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary fs-6">
                                        <?php echo $customer['formulation_count']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <?php 
                                        $formats = explode(',', $customer['formats_used']);
                                        foreach ($formats as $format): 
                                        ?>
                                            <span class="badge bg-secondary me-1"><?php echo ucfirst(trim($format)); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <small>
                                        <?php echo date('M j, Y', strtotime($customer['first_formulation'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <small>
                                        <?php echo date('M j, Y', strtotime($customer['last_formulation'])); ?><br>
                                        <span class="text-muted"><?php echo date('g:i A', strtotime($customer['last_formulation'])); ?></span>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="viewCustomerFormulations('<?php echo htmlspecialchars($customer['customer_email']); ?>')"
                                                data-bs-toggle="tooltip" title="View Formulations">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="mailto:<?php echo htmlspecialchars($customer['customer_email']); ?>" 
                                           class="btn btn-sm btn-outline-success"
                                           data-bs-toggle="tooltip" title="Send Email">
                                            <i class="bi bi-envelope"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-people display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">No customers found</h4>
                <p class="text-muted">Customer data will appear here once formulations are submitted</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Customer Formulations Modal -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person"></i>
                    Customer Formulations
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="customerFormulations">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewCustomerFormulations(email) {
    const modal = new bootstrap.Modal(document.getElementById('customerModal'));
    const modalBody = document.getElementById('customerFormulations');
    
    modalBody.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch customer formulations
    fetch(`api/get_customer_formulations.php?email=${encodeURIComponent(email)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.formulations) {
                let html = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Customer: ${data.formulations[0].customer_name}</h6>
                            <p class="text-muted">${email}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="badge bg-primary fs-6">${data.formulations.length} Total Formulations</span>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Format</th>
                                    <th>Skin Types</th>
                                    <th>Key Actives</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                data.formulations.forEach(formulation => {
                    html += `
                        <tr>
                            <td><strong>#${formulation.id}</strong></td>
                            <td><span class="badge bg-primary">${formulation.base_format}</span></td>
                            <td><small>${formulation.skin_types.join(', ')}</small></td>
                            <td><small>${formulation.key_actives.join(', ')}</small></td>
                            <td><small>${new Date(formulation.created_at).toLocaleDateString()}</small></td>
                            <td>
                                <a href="../api/preview_pdf.php?id=${formulation.id}" target="_blank" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                modalBody.innerHTML = html;
            } else {
                modalBody.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        No formulations found for this customer.
                    </div>
                `;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Error loading customer formulations. Please try again.
                </div>
            `;
        });
}
</script>

<?php include 'includes/footer.php'; ?>