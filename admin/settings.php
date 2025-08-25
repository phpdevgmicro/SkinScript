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

$pageTitle = 'Settings Management';
$currentPage = 'settings';

// Include required files
require_once '../api/models/AdminUserModel.php';

$adminModel = new AdminUserModel();
$message = '';
$messageType = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_ai_prompt':
                $newPrompt = trim($_POST['ai_prompt'] ?? '');
                if (!empty($newPrompt)) {
                    if ($adminModel->updateSetting('ai_formulation_prompt', $newPrompt, $_SESSION['admin_user_id'])) {
                        $message = 'AI prompt updated successfully';
                        $messageType = 'success';
                    } else {
                        $message = 'Failed to update AI prompt';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'AI prompt cannot be empty';
                    $messageType = 'danger';
                }
                break;

            case 'update_ai_parameters':
                $aiModel = trim($_POST['ai_model']);
                $aiTemperature = floatval($_POST['ai_temperature']);
                $aiMaxTokens = intval($_POST['ai_max_tokens']);

                $success = true;
                $success &= $adminModel->updateSetting('ai_model', $aiModel, $_SESSION['admin_user_id']);
                $success &= $adminModel->updateSetting('ai_temperature', $aiTemperature, $_SESSION['admin_user_id']);
                $success &= $adminModel->updateSetting('ai_max_tokens', $aiMaxTokens, $_SESSION['admin_user_id']);

                if ($success) {
                    $message = 'AI parameters updated successfully';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to update AI parameters';
                    $messageType = 'danger';
                }
                break;

            case 'update_admin_email':
                $newEmail = trim($_POST['admin_email'] ?? '');
                if (!empty($newEmail) && filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                    if ($adminModel->updateSetting('admin_notification_email', $newEmail, $_SESSION['admin_user_id'])) {
                        $message = 'Admin notification email updated successfully';
                        $messageType = 'success';
                    } else {
                        $message = 'Failed to update admin email';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Please enter a valid email address';
                    $messageType = 'danger';
                }
                break;

            case 'change_password':
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                    $message = 'All password fields are required';
                    $messageType = 'danger';
                } elseif ($newPassword !== $confirmPassword) {
                    $message = 'New passwords do not match';
                    $messageType = 'danger';
                } elseif (strlen($newPassword) < 8) {
                    $message = 'New password must be at least 8 characters long';
                    $messageType = 'danger';
                } else {
                    // Verify current password
                    $user = $adminModel->authenticateUser($_SESSION['admin_username'], $currentPassword);
                    if ($user) {
                        if ($adminModel->updatePassword($_SESSION['admin_user_id'], $newPassword)) {
                            $message = 'Password changed successfully';
                            $messageType = 'success';
                        } else {
                            $message = 'Failed to change password';
                            $messageType = 'danger';
                        }
                    } else {
                        $message = 'Current password is incorrect';
                        $messageType = 'danger';
                    }
                }
                break;
        }
    }
}

// Get current settings
$settings = $adminModel->getAllSettings();
$settingsArray = [];
foreach ($settings as $setting) {
    $settingsArray[$setting['setting_key']] = $setting['setting_value'];
}

include 'includes/header.php';
?>

<!-- Settings Management -->
<div class="container-fluid">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- AI Prompt Settings -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-robot"></i>
                        AI Formulation Prompt Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_ai_prompt">
                        <div class="mb-3">
                            <label for="ai_prompt" class="form-label">AI Prompt Template</label>
                            <textarea class="form-control" id="ai_prompt" name="ai_prompt" rows="8" required
                                placeholder="Enter the prompt that will be used to guide AI formulation generation..."><?php echo htmlspecialchars($settingsArray['ai_formulation_prompt'] ?? ''); ?></textarea>
                            <div class="form-text">
                                This prompt will be used to guide the AI when generating formulation suggestions. 
                                Be specific about what you want the AI to include (percentages, INCI names, explanations, etc.).
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i>
                            Update AI Prompt
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- AI Parameters and Settings -->
        <div class="col-lg-4 mb-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-gear"></i>
                        AI Parameters
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_ai_parameters">
                        <div class="mb-3">
                            <label for="ai_model" class="form-label">AI Model</label>
                            <select class="form-select" id="ai_model" name="ai_model">
                                <optgroup label="GPT-5 Series (Latest - August 2025)">
                                    <option value="gpt-5" <?php echo ($settingsArray['ai_model'] ?? '') === 'gpt-5' ? 'selected' : ''; ?>>gpt-5 // Most advanced - highest cost, best performance</option>
                                    <option value="gpt-5-mini" <?php echo ($settingsArray['ai_model'] ?? '') === 'gpt-5-mini' ? 'selected' : ''; ?>>gpt-5-mini // Balanced - good performance, moderate cost</option>
                                    <option value="gpt-5-nano" <?php echo ($settingsArray['ai_model'] ?? '') === 'gpt-5-nano' ? 'selected' : ''; ?>>gpt-5-nano // Fastest - lowest cost, basic tasks</option>
                                </optgroup>
                                <optgroup label="GPT-4.1 Series (Coding-focused, June 2024 knowledge)">
                                    <option value="gpt-4.1" <?php echo ($settingsArray['ai_model'] ?? '') === 'gpt-4.1' ? 'selected' : ''; ?>>gpt-4.1 // Premium - highest accuracy for formulations</option>
                                    <option value="gpt-4.1-mini" <?php echo ($settingsArray['ai_model'] ?? 'gpt-4.1-mini') === 'gpt-4.1-mini' ? 'selected' : ''; ?>>gpt-4.1-mini // Best balance - newer, accurate, cost-effective ⭐</option>
                                    <option value="gpt-4.1-nano" <?php echo ($settingsArray['ai_model'] ?? '') === 'gpt-4.1-nano' ? 'selected' : ''; ?>>gpt-4.1-nano // Budget-friendly - fast responses, basic chemistry</option>
                                </optgroup>
                                <optgroup label="GPT-4o Series (Optimized for chat/multimodal)">
                                    <option value="gpt-4o" <?php echo ($settingsArray['ai_model'] ?? '') === 'gpt-4o' ? 'selected' : ''; ?>>gpt-4o // Full model - multimodal capabilities</option>
                                    <option value="gpt-4o-mini" <?php echo ($settingsArray['ai_model'] ?? '') === 'gpt-4o-mini' ? 'selected' : ''; ?>>gpt-4o-mini // Still good - proven, very cheap, reliable ⭐</option>
                                </optgroup>
                                <optgroup label="GPT-4 Series (Original)">
                                    <option value="gpt-4-turbo" <?php echo ($settingsArray['ai_model'] ?? '') === 'gpt-4-turbo' ? 'selected' : ''; ?>>gpt-4-turbo // Enhanced GPT-4 - good for complex chemistry</option>
                                    <option value="gpt-4" <?php echo ($settingsArray['ai_model'] ?? '') === 'gpt-4' ? 'selected' : ''; ?>>gpt-4 // Original - reliable but older</option>
                                </optgroup>
                            </select>
                            <div class="form-text">
                                <strong>⭐ Recommended:</strong> gpt-4.1-mini for best balance of accuracy and cost, or gpt-4o-mini for proven reliability.<br>
                                <strong>GPT-5 Series:</strong> Latest models with cutting-edge capabilities (August 2025).<br>
                                <strong>GPT-4.1 Series:</strong> Coding-focused with enhanced chemistry knowledge (June 2024).<br>
                                <strong>GPT-4o Series:</strong> Optimized for chat and multimodal tasks with excellent cost efficiency.<br>
                                <strong>GPT-4 Series:</strong> Original reliable models for complex formulations.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="ai_temperature" class="form-label">Temperature (<?php echo $settingsArray['ai_temperature'] ?? '0.7'; ?>)</label>
                            <input type="range" class="form-range" id="ai_temperature" name="ai_temperature" 
                                   min="0" max="2" step="0.1" value="<?php echo $settingsArray['ai_temperature'] ?? '0.7'; ?>"
                                   oninput="this.previousElementSibling.textContent = 'Temperature (' + this.value + ')'">
                            <div class="form-text">Higher values make output more creative</div>
                        </div>
                        <div class="mb-3">
                            <label for="ai_max_tokens" class="form-label">Max Tokens</label>
                            <input type="number" class="form-control" id="ai_max_tokens" name="ai_max_tokens" 
                                   value="<?php echo $settingsArray['ai_max_tokens'] ?? '1500'; ?>" min="100" max="4000">
                            <div class="form-text">Maximum response length</div>
                        </div>
                        <button type="submit" class="btn btn-success mb-3">
                            <i class="bi bi-save"></i>
                            Update AI Settings
                        </button>
                    </form>
                </div>
            </div>

            </div>
    </div>

    <!-- AI Prompt Examples -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb"></i>
                        AI Prompt Examples & Tips
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Example Prompts:</h6>
                            <div class="bg-light p-3 rounded mb-3">
                                <strong>Detailed Formulation:</strong><br>
                                <small class="text-muted">
                                    "You are a professional cosmetic chemist with 15+ years of experience. Create a detailed skincare formulation based on the customer's profile. Include exact percentages for each ingredient, INCI names, and brief explanations for ingredient choices. Ensure the formulation is stable, safe, and effective for the specified skin type."
                                </small>
                            </div>
                            <div class="bg-light p-3 rounded">
                                <strong>Simple Suggestions:</strong><br>
                                <small class="text-muted">
                                    "Based on the customer's skin type and concerns, suggest key ingredients and basic formulation structure. Focus on ingredient benefits and compatibility."
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Tips for Better Results:</h6>
                            <ul class="small">
                                <li>Be specific about what information to include</li>
                                <li>Mention safety and stability requirements</li>
                                <li>Request INCI names for professional accuracy</li>
                                <li>Ask for ingredient percentages if needed</li>
                                <li>Include pH and compatibility considerations</li>
                                <li>Specify the level of detail required</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Settings -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope"></i>
                        Notification Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_admin_email">
                        <div class="mb-3">
                            <label for="admin_email" class="form-label">Admin Email</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                   value="<?php echo htmlspecialchars($settingsArray['admin_notification_email'] ?? ''); ?>" 
                                   placeholder="admin@example.com" required>
                            <div class="form-text">Email address to receive new formulation notifications</div>
                        </div>
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-save"></i>
                            Update Email
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-key"></i>
                        Change Password
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   minlength="8" required>
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   minlength="8" required>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key"></i>
                            Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'includes/footer.php'; ?>