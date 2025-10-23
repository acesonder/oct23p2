<?php
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$conn = getConnection();
$stats = [
    'total_users' => 0,
    'total_clients' => 0,
    'total_assessments' => 0,
    'total_resources' => 0,
    'active_barriers' => 0,
    'pending_actions' => 0
];

if ($conn) {
    // Get statistics
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
    if ($result) $stats['total_users'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM clients");
    if ($result) $stats['total_clients'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM assessments");
    if ($result) $stats['total_assessments'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM resources WHERE status = 'active'");
    if ($result) $stats['total_resources'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM barriers WHERE status IN ('identified', 'in_progress')");
    if ($result) $stats['active_barriers'] = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM action_plans WHERE status IN ('recommended', 'in_progress')");
    if ($result) $stats['pending_actions'] = $result->fetch_assoc()['count'];
    
    closeConnection($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .admin-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-color);
            cursor: pointer;
            transition: transform 0.3s;
        }
        .admin-card:hover {
            transform: translateY(-3px);
        }
        .admin-card h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--dark-color);
        }
        .admin-card p {
            color: var(--gray-color);
            margin-bottom: 10px;
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            padding: 10px 15px;
            margin-bottom: 8px;
            background: var(--light-color);
            border-radius: 6px;
            border-left: 3px solid var(--secondary-color);
        }
        .feature-list li.pending {
            border-left-color: var(--warning-color);
            opacity: 0.7;
        }
        .feature-list li.future {
            border-left-color: var(--info-color);
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>⚙️ Admin Panel</h1>
                <p>System Administration and Configuration</p>
            </div>
            
            <?php displayFlashMessage(); ?>
            
            <!-- System Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">Active Users</div>
                    <div class="stat-card-value"><?php echo $stats['total_users']; ?></div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-card-header">Total Clients</div>
                    <div class="stat-card-value"><?php echo $stats['total_clients']; ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">Assessments</div>
                    <div class="stat-card-value"><?php echo $stats['total_assessments']; ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">Resources</div>
                    <div class="stat-card-value"><?php echo $stats['total_resources']; ?></div>
                </div>
                
                <div class="stat-card danger">
                    <div class="stat-card-header">Active Barriers</div>
                    <div class="stat-card-value"><?php echo $stats['active_barriers']; ?></div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-card-header">Pending Actions</div>
                    <div class="stat-card-value"><?php echo $stats['pending_actions']; ?></div>
                </div>
            </div>
            
            <!-- Admin Features -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🎛️ System Management</h2>
                </div>
                <div class="card-body">
                    <div class="admin-grid">
                        <div class="admin-card" onclick="location.href='users.php'">
                            <h3>👥 User Management</h3>
                            <p>Manage user accounts, roles, and permissions</p>
                            <span class="badge badge-success">Active</span>
                        </div>
                        
                        <div class="admin-card" onclick="location.href='resources.php'">
                            <h3>🏢 Resource Management</h3>
                            <p>Add, edit, and manage community resources</p>
                            <span class="badge badge-success">Active</span>
                        </div>
                        
                        <div class="admin-card" onclick="location.href='reports.php'">
                            <h3>📊 Reports & Analytics</h3>
                            <p>View system reports and statistics</p>
                            <span class="badge badge-success">Active</span>
                        </div>
                        
                        <div class="admin-card" onclick="location.href='settings.php'">
                            <h3>⚙️ System Settings</h3>
                            <p>Configure system parameters and options</p>
                            <span class="badge badge-success">Active</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Setup Status -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">✅ System Setup Status</h2>
                </div>
                <div class="card-body">
                    <ul class="feature-list">
                        <li>✅ Database schema created and initialized</li>
                        <li>✅ User authentication system implemented</li>
                        <li>✅ Role-based access control (Admin, Outreach, Provider)</li>
                        <li>✅ Client profile management</li>
                        <li>✅ Comprehensive intake assessment forms</li>
                        <li>✅ Barrier identification system</li>
                        <li>✅ Action plan recommendation engine</li>
                        <li>✅ Resource directory with search</li>
                        <li>✅ Team communication dashboard</li>
                        <li>✅ Progress tracking system</li>
                    </ul>
                </div>
            </div>
            
            <!-- Configuration Needed -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">⚠️ Configuration Required</h2>
                </div>
                <div class="card-body">
                    <ul class="feature-list">
                        <li class="pending">⚠️ Add local resources to the directory</li>
                        <li class="pending">⚠️ Create additional user accounts for team members</li>
                        <li class="pending">⚠️ Configure email notifications (optional)</li>
                        <li class="pending">⚠️ Set up automated backup system</li>
                    </ul>
                </div>
            </div>
            
            <!-- Future Enhancements -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🚀 Future Enhancement Ideas</h2>
                </div>
                <div class="card-body">
                    <ul class="feature-list">
                        <li class="future">📱 Mobile app for field workers</li>
                        <li class="future">📧 Automated email/SMS reminders for appointments</li>
                        <li class="future">📈 Advanced analytics and predictive modeling</li>
                        <li class="future">🔗 Integration with external case management systems</li>
                        <li class="future">📄 Document management and upload system</li>
                        <li class="future">🗓️ Calendar and appointment scheduling</li>
                        <li class="future">💬 Real-time messaging between team members</li>
                        <li class="future">🎯 Goal setting and milestone tracking</li>
                        <li class="future">📊 Custom report builder</li>
                        <li class="future">🔐 Two-factor authentication</li>
                        <li class="future">🌍 Multi-language support</li>
                        <li class="future">♿ Enhanced accessibility features</li>
                        <li class="future">🤖 AI-powered barrier prediction</li>
                        <li class="future">📱 Client self-service portal</li>
                        <li class="future">💳 Payment tracking and financial assistance management</li>
                    </ul>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">⚡ Quick Actions</h2>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="users.php" class="btn btn-primary">Manage Users</a>
                        <a href="resources.php" class="btn btn-success">Add Resources</a>
                        <a href="reports.php" class="btn btn-info">View Reports</a>
                        <a href="settings.php" class="btn btn-outline">System Settings</a>
                        <a href="/modules/dashboard/index.php" class="btn btn-outline">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
