
<?php
session_start();
require_once __DIR__ . '/../api/models/AdminUserModel.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$adminModel = new AdminUserModel();
$currentModel = $adminModel->getSetting('ai_model') ?: 'gpt-4.1-mini';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SkinCraft</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .content {
            padding: 40px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
        }

        .dashboard-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.3rem;
        }

        .dashboard-card p {
            color: #666;
            margin-bottom: 20px;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .current-model {
            background: #e7f3ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .current-model strong {
            color: #667eea;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            float: right;
        }

        .logout-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <p>SkinCraft Management Panel</p>
            <a href="logout.php" class="logout-btn">Logout</a>
            <div style="clear: both;"></div>
        </div>
        
        <div class="content">
            <div class="current-model">
                <strong>Current AI Model:</strong> <?php echo htmlspecialchars($currentModel); ?>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>AI Model Settings</h3>
                    <p>Configure the AI model used for formulation processing and responses.</p>
                    <a href="settings.php" class="btn">Manage Models</a>
                </div>

                <div class="dashboard-card">
                    <h3>Formulation Requests</h3>
                    <p>View and manage customer formulation requests and submissions.</p>
                    <a href="formulations.php" class="btn">View Requests</a>
                </div>

                <div class="dashboard-card">
                    <h3>System Status</h3>
                    <p>Monitor system health, performance, and usage statistics.</p>
                    <a href="status.php" class="btn">View Status</a>
                </div>

                <div class="dashboard-card">
                    <h3>User Management</h3>
                    <p>Manage admin users, permissions, and access controls.</p>
                    <a href="users.php" class="btn">Manage Users</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
