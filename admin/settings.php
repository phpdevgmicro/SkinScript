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

$pageTitle = 'Settings';
$currentPage = 'settings';

// Handle form submissions
$message = '';
$messageType = '';

// Get current admin info from session or default values
$adminName = $_SESSION['admin_username'] ?? 'Admin';

if ($_POST) {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $newName = trim($_POST['admin_name'] ?? '');
        $newPassword = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        // Validate inputs
        if (empty($newName)) {
            $message = 'Admin name is required.';
            $messageType = 'danger';
        } elseif (!empty($newPassword) && strlen($newPassword) < 6) {
            $message = 'Password must be at least 6 characters long.';
            $messageType = 'danger';
        } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
            $message = 'New passwords do not match.';
            $messageType = 'danger';
        } else {
            // Update session username
            $_SESSION['admin_username'] = $newName;
            $adminName = $newName;

            // If password change is requested, update the login file
            if (!empty($newPassword)) {
                $loginFile = 'auth/login.php';
                $loginContent = file_get_contents($loginFile);
                
                // Update the password in the login file
                $loginContent = preg_replace(
                    '/\$admin_password = \'[^\']*\';/',
                    '$admin_password = \'' . addslashes($newPassword) . '\';',
                    $loginContent
                );

                file_put_contents($loginFile, $loginContent);
                $message = 'Profile and password updated successfully!';
            } else {
                $message = 'Profile updated successfully!';
            }
            $messageType = 'success';
        }
    }
}

include 'includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <!-- Admin Profile Management -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-gear text-primary me-2"></i>
                    Admin Profile Settings
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-4 text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-person-fill" style="font-size: 2.5rem;"></i>
                    </div>
                    <h6 class="mt-2 mb-0">Administrator Account</h6>
                    <small class="text-muted">Manage your admin profile</small>
                </div>

                <form id="profileForm" method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="mb-4">
                        <label for="admin_name" class="form-label fw-semibold">
                            <i class="bi bi-person text-primary"></i>
                            Admin Name
                        </label>
                        <input type="text" class="form-control" id="admin_name" name="admin_name" 
                               value="<?php echo htmlspecialchars($adminName); ?>" 
                               required placeholder="Enter admin display name">
                        <div class="form-text">This name will be displayed in the admin panel.</div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="text-primary mb-3">
                        <i class="bi bi-shield-lock"></i>
                        Change Password
                    </h6>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-semibold">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password"
                               placeholder="Enter new password">
                        <div class="form-text">Leave blank to keep current password. Minimum 6 characters required.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                               placeholder="Confirm new password">
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                            <i class="bi bi-arrow-clockwise"></i>
                            Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i>
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle text-info me-2"></i>
                    Security Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="text-success me-3">
                                <i class="bi bi-shield-check" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Session Active</div>
                                <small class="text-muted">Logged in as <?php echo htmlspecialchars($adminName); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="text-primary me-3">
                                <i class="bi bi-clock" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Session Timeout</div>
                                <small class="text-muted">24 hours from login</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr class="my-3">
                
                <div class="alert alert-info mb-0">
                    <i class="bi bi-lightbulb"></i>
                    <strong>Security Tips:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Use a strong password with at least 6 characters</li>
                        <li>Log out when accessing from shared computers</li>
                        <li>Keep your admin credentials secure</li>
                    </ul>
                </div>
            </div>
        </div>

        
    </div>
</div>

<script>
// Profile form validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const adminName = document.getElementById('admin_name').value.trim();
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (!adminName) {
        e.preventDefault();
        showAlert('Admin name is required!', 'danger');
        return false;
    }

    if (newPassword && newPassword !== confirmPassword) {
        e.preventDefault();
        showAlert('New passwords do not match!', 'danger');
        return false;
    }

    if (newPassword && newPassword.length < 6) {
        e.preventDefault();
        showAlert('New password must be at least 6 characters long!', 'danger');
        return false;
    }
});

// Clear confirm password when new password changes
document.getElementById('new_password').addEventListener('input', function() {
    document.getElementById('confirm_password').value = '';
});

// Reset form function
function resetForm() {
    document.getElementById('new_password').value = '';
    document.getElementById('confirm_password').value = '';
    document.getElementById('admin_name').value = '<?php echo htmlspecialchars($adminName); ?>';
}

// Alert function
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.col-lg-8');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>

<?php include 'includes/footer.php'; ?>
