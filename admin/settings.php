<?php
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$conn = getConnection();
$settings = [];

if ($conn) {
    $result = $conn->query("SELECT * FROM system_config ORDER BY config_key ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['config_key']] = $row;
        }
    }
    closeConnection($conn);
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    if ($conn) {
        foreach ($_POST as $key => $value) {
            if ($key !== 'action' && strpos($key, 'setting_') === 0) {
                $configKey = str_replace('setting_', '', $key);
                $configValue = sanitize($value);
                
                $stmt = $conn->prepare("UPDATE system_config SET config_value = ? WHERE config_key = ?");
                $stmt->bind_param("ss", $configValue, $configKey);
                $stmt->execute();
                $stmt->close();
            }
        }
        closeConnection($conn);
        redirectWithMessage('settings.php', 'Settings updated successfully!', 'success');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>⚙️ System Settings</h1>
                <p>Configure system parameters and options</p>
            </div>
            
            <?php displayFlashMessage(); ?>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Application Settings</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php foreach ($settings as $key => $setting): ?>
                            <div class="form-group">
                                <label class="form-label">
                                    <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>
                                </label>
                                <input type="text" class="form-control" 
                                       name="setting_<?php echo $key; ?>" 
                                       value="<?php echo htmlspecialchars($setting['config_value']); ?>">
                                <?php if ($setting['description']): ?>
                                    <small class="form-text"><?php echo htmlspecialchars($setting['description']); ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" class="btn btn-primary mt-2">Save Settings</button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📊 Database Information</h2>
                </div>
                <div class="card-body">
                    <?php
                    $conn = getConnection();
                    if ($conn) {
                        echo "<table class='table'>";
                        echo "<tr><th>Table</th><th>Records</th></tr>";
                        
                        $tables = ['users', 'clients', 'assessments', 'barriers', 'action_plans', 'resources', 'team_notes', 'progress_milestones'];
                        foreach ($tables as $table) {
                            // Validate table name against whitelist
                            $safeTables = ['users', 'clients', 'assessments', 'barriers', 'action_plans', 'resources', 'team_notes', 'progress_milestones'];
                            if (in_array($table, $safeTables)) {
                                $result = $conn->query("SELECT COUNT(*) as count FROM " . $conn->real_escape_string($table));
                                if ($result) {
                                    $count = $result->fetch_assoc()['count'];
                                    echo "<tr><td>" . htmlspecialchars(ucfirst($table)) . "</td><td>" . htmlspecialchars($count) . "</td></tr>";
                                }
                            }
                        }
                        
                        echo "</table>";
                        closeConnection($conn);
                    }
                    ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">ℹ️ System Information</h2>
                </div>
                <div class="card-body">
                    <p><strong>Application:</strong> OUTSINC (Outreach Someone In Need Of Change)</p>
                    <p><strong>Version:</strong> 1.0.0</p>
                    <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                    <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
                    <p><strong>Database:</strong> MySQL</p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🔧 Database Setup Instructions</h2>
                </div>
                <div class="card-body">
                    <p>To set up the database for this application:</p>
                    <ol>
                        <li>Ensure MySQL is installed and running on your server</li>
                        <li>Import the database schema: <code>mysql -u root -p < database.sql</code></li>
                        <li>Update database credentials in <code>/config/database.php</code> if needed</li>
                        <li>Default admin credentials: <strong>admin / admin123</strong></li>
                    </ol>
                    
                    <h4 style="margin-top: 20px;">Quick Setup Commands:</h4>
                    <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;">
# Create database and import schema
mysql -u root -p < database.sql

# Or manually:
mysql -u root -p
CREATE DATABASE outsinc;
USE outsinc;
SOURCE database.sql;
</pre>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="index.php" class="btn btn-outline">Back to Admin Panel</a>
            </div>
        </div>
    </div>
</body>
</html>
