<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

requireLogin();

$conn = getConnection();
$userId = getCurrentUserId();
$userRole = getCurrentUserRole();

// Get statistics
$stats = [
    'total_clients' => 0,
    'active_assessments' => 0,
    'pending_actions' => 0,
    'resources' => 0
];

if ($conn) {
    // Total clients
    if ($userRole === 'admin') {
        $result = $conn->query("SELECT COUNT(*) as count FROM clients");
    } else {
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT c.id) as count FROM clients c 
                                LEFT JOIN client_assignments ca ON c.id = ca.client_id 
                                WHERE ca.user_id = ? OR c.created_by = ?");
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    if ($result) {
        $stats['total_clients'] = $result->fetch_assoc()['count'];
    }
    
    // Active assessments (last 90 days)
    if ($userRole === 'admin') {
        $result = $conn->query("SELECT COUNT(*) as count FROM assessments WHERE assessment_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)");
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM assessments WHERE assessed_by = ? AND assessment_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    if ($result) {
        $stats['active_assessments'] = $result->fetch_assoc()['count'];
    }
    
    // Pending actions
    if ($userRole === 'admin') {
        $result = $conn->query("SELECT COUNT(*) as count FROM action_plans WHERE status IN ('recommended', 'in_progress')");
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM action_plans WHERE assigned_to = ? AND status IN ('recommended', 'in_progress')");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    if ($result) {
        $stats['pending_actions'] = $result->fetch_assoc()['count'];
    }
    
    // Total resources
    $result = $conn->query("SELECT COUNT(*) as count FROM resources WHERE status = 'active'");
    if ($result) {
        $stats['resources'] = $result->fetch_assoc()['count'];
    }
    
    // Get recent clients
    $recentClients = [];
    if ($userRole === 'admin') {
        $result = $conn->query("SELECT c.*, u.full_name as created_by_name 
                               FROM clients c 
                               LEFT JOIN users u ON c.created_by = u.id 
                               ORDER BY c.created_at DESC LIMIT 5");
    } else {
        $stmt = $conn->prepare("SELECT c.*, u.full_name as created_by_name 
                               FROM clients c 
                               LEFT JOIN users u ON c.created_by = u.id 
                               LEFT JOIN client_assignments ca ON c.id = ca.client_id 
                               WHERE ca.user_id = ? OR c.created_by = ? 
                               ORDER BY c.created_at DESC LIMIT 5");
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recentClients[] = $row;
        }
    }
    
    closeConnection($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars(getCurrentUserName()); ?>!</p>
            </div>
            
            <?php displayFlashMessage(); ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">Total Clients</div>
                    <div class="stat-card-value"><?php echo $stats['total_clients']; ?></div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-card-header">Active Assessments</div>
                    <div class="stat-card-value"><?php echo $stats['active_assessments']; ?></div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-card-header">Pending Actions</div>
                    <div class="stat-card-value"><?php echo $stats['pending_actions']; ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">Available Resources</div>
                    <div class="stat-card-value"><?php echo $stats['resources']; ?></div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Clients</h2>
                    <a href="/modules/clients/list.php" class="btn btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentClients)): ?>
                        <p class="text-center">No clients found. <a href="/modules/clients/create.php">Create your first client</a></p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentClients as $client): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($client['phone'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($client['created_by_name'] ?? 'Unknown'); ?></td>
                                        <td><?php echo formatDate($client['created_at']); ?></td>
                                        <td>
                                            <a href="/modules/clients/view.php?id=<?php echo $client['id']; ?>" class="btn btn-info" style="padding: 6px 12px; font-size: 14px;">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="/modules/clients/create.php" class="btn btn-primary">Add New Client</a>
                        <a href="/modules/resources/list.php" class="btn btn-info">Browse Resources</a>
                        <?php if ($userRole === 'admin'): ?>
                            <a href="/admin/index.php" class="btn btn-danger">Admin Panel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
