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

$pageTitle = 'Formulations Management';
$currentPage = 'formulations';

// Include required files
require_once '../api/models/FormulationModel.php';

// Handle pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get formulations
$formulationModel = new FormulationModel();

// Get formulations with search and pagination
if (!empty($search)) {
    // Search in database
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $searchSql = "SELECT id, customer_name, customer_email, skin_types, base_format, key_actives, created_at 
                      FROM formulations 
                      WHERE customer_name LIKE :search OR customer_email LIKE :search OR base_format LIKE :search
                      ORDER BY created_at DESC 
                      LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($searchSql);
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $formulations = $stmt->fetchAll();
        
        // Decode JSON fields
        foreach ($formulations as &$formulation) {
            $formulation['skin_types'] = json_decode($formulation['skin_types'], true);
            $formulation['key_actives'] = json_decode($formulation['key_actives'], true);
        }
        
        // Get search count
        $countSql = "SELECT COUNT(*) as total FROM formulations WHERE customer_name LIKE :search OR customer_email LIKE :search OR base_format LIKE :search";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bindParam(':search', $searchParam);
        $countStmt->execute();
        $totalCount = $countStmt->fetch()['total'];
        $totalPages = ceil($totalCount / $limit);
        
    } catch (Exception $e) {
        $formulations = [];
        $totalCount = 0;
        $totalPages = 1;
    }
} else {
    // Get all formulations with pagination
    $result = $formulationModel->getAllFormulations($limit, $offset);
    $formulations = $result['formulations'] ?? [];
    
    // Get total count for pagination
    try {
        $database = new Database();
        $conn = $database->getConnection();
        $countSql = "SELECT COUNT(*) as total FROM formulations";
        $stmt = $conn->prepare($countSql);
        $stmt->execute();
        $totalCount = $stmt->fetch()['total'];
        $totalPages = ceil($totalCount / $limit);
    } catch (Exception $e) {
        $totalCount = 0;
        $totalPages = 1;
    }
}

include 'includes/header.php';
?>

<!-- Search and Filters -->
<div class="row mb-4">
    <div class="col-md-6">
        <form method="GET" class="d-flex">
            <div class="search-box flex-grow-1 me-2">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" name="search" 
                       placeholder="Search by name, email, or format..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i>
            </button>
            <?php if (!empty($search)): ?>
                <a href="formulations.php" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-x"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-success" onclick="exportTableAsCSV('formulationsTable', 'formulations.csv')">
            <i class="bi bi-download"></i>
            Export CSV
        </button>
    </div>
</div>

<!-- Formulations Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            All Formulations
            <?php if (!empty($search)): ?>
                <small class="text-muted">(filtered by "<?php echo htmlspecialchars($search); ?>")</small>
            <?php endif; ?>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($formulations)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="formulationsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Format</th>
                            <th>Skin Types</th>
                            <th>Key Actives</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($formulations as $formulation): ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo $formulation['id']; ?></strong>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($formulation['customer_name']); ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($formulation['customer_email']); ?>" 
                                       class="text-decoration-none">
                                        <?php echo htmlspecialchars($formulation['customer_email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo ucfirst($formulation['base_format']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo implode(', ', $formulation['skin_types']); ?>
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php 
                                        $actives = array_slice($formulation['key_actives'], 0, 2);
                                        echo implode(', ', $actives);
                                        if (count($formulation['key_actives']) > 2) {
                                            echo ' (+' . (count($formulation['key_actives']) - 2) . ' more)';
                                        }
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <small>
                                        <?php echo date('M j, Y', strtotime($formulation['created_at'])); ?><br>
                                        <span class="text-muted"><?php echo date('g:i A', strtotime($formulation['created_at'])); ?></span>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="viewFormulation(<?php echo $formulation['id']; ?>)"
                                                data-bs-toggle="tooltip" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="../api/preview_pdf.php?id=<?php echo $formulation['id']; ?>" 
                                           target="_blank" class="btn btn-sm btn-outline-success"
                                           data-bs-toggle="tooltip" title="View PDF">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <div class="text-muted">
                        Showing <?php echo (($page - 1) * $limit) + 1; ?> to 
                        <?php echo min($page * $limit, $totalCount); ?> of <?php echo $totalCount; ?> entries
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                        &laquo; Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                        Next &raquo;
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">No formulations found</h4>
                <?php if (!empty($search)): ?>
                    <p class="text-muted">Try adjusting your search criteria</p>
                    <a href="formulations.php" class="btn btn-primary">View All Formulations</a>
                <?php else: ?>
                    <p class="text-muted">Formulations will appear here once customers start submitting them</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Formulation Details Modal -->
<div class="modal fade" id="formulationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-clipboard-data"></i>
                    Formulation Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="formulationDetails">
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
function viewFormulation(id) {
    const modal = new bootstrap.Modal(document.getElementById('formulationModal'));
    const modalBody = document.getElementById('formulationDetails');
    
    modalBody.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch formulation details
    fetch(`../api/controllers/FormulationController.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const formulation = data.formulation;
                modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Name:</strong></td><td>${formulation.customer_name}</td></tr>
                                <tr><td><strong>Email:</strong></td><td>${formulation.customer_email}</td></tr>
                                <tr><td><strong>Phone:</strong></td><td>${formulation.customer_phone || 'Not provided'}</td></tr>
                                <tr><td><strong>Skin Concerns:</strong></td><td>${formulation.skin_concerns || 'None specified'}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Formulation Details</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Format:</strong></td><td><span class="badge bg-primary">${formulation.base_format}</span></td></tr>
                                <tr><td><strong>Created:</strong></td><td>${new Date(formulation.created_at).toLocaleString()}</td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <h6>Skin Types</h6>
                            <div class="mb-3">
                                ${JSON.parse(formulation.skin_types).map(type => `<span class="badge bg-secondary me-1">${type}</span>`).join('')}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6>Key Actives</h6>
                            <div class="mb-3">
                                ${JSON.parse(formulation.key_actives).map(active => `<span class="badge bg-success me-1">${active}</span>`).join('')}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6>Extracts</h6>
                            <div class="mb-3">
                                ${JSON.parse(formulation.extracts).map(extract => `<span class="badge bg-warning me-1">${extract}</span>`).join('')}
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6>Boosters</h6>
                            <div class="mb-3">
                                ${JSON.parse(formulation.boosters).map(booster => `<span class="badge bg-info me-1">${booster}</span>`).join('')}
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="../api/preview_pdf.php?id=${formulation.id}" target="_blank" class="btn btn-primary">
                            <i class="bi bi-file-pdf"></i> View PDF Report
                        </a>
                    </div>
                `;
            } else {
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Failed to load formulation details: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Error loading formulation details. Please try again.
                </div>
            `;
        });
}
</script>

<?php include 'includes/footer.php'; ?>