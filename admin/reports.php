<?php
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$conn = getConnection();
$reports = [
    'user_activity' => [],
    'client_demographics' => [],
    'barrier_types' => [],
    'action_plan_status' => [],
    'resource_usage' => []
];

if ($conn) {
    // User activity
    $result = $conn->query("SELECT u.full_name, u.role, COUNT(DISTINCT c.id) as clients, COUNT(DISTINCT a.id) as assessments 
                           FROM users u 
                           LEFT JOIN clients c ON u.id = c.created_by 
                           LEFT JOIN assessments a ON u.id = a.assessed_by 
                           WHERE u.status = 'active' 
                           GROUP BY u.id 
                           ORDER BY assessments DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $reports['user_activity'][] = $row;
        }
    }
    
    // Client demographics by housing status
    $result = $conn->query("SELECT a.housing_status, COUNT(DISTINCT a.client_id) as count 
                           FROM assessments a 
                           WHERE a.id IN (SELECT MAX(id) FROM assessments GROUP BY client_id) 
                           GROUP BY a.housing_status");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $reports['client_demographics'][] = $row;
        }
    }
    
    // Barrier types
    $result = $conn->query("SELECT barrier_type, COUNT(*) as count, 
                           SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved 
                           FROM barriers 
                           GROUP BY barrier_type 
                           ORDER BY count DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $reports['barrier_types'][] = $row;
        }
    }
    
    // Action plan status
    $result = $conn->query("SELECT status, COUNT(*) as count FROM action_plans GROUP BY status");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $reports['action_plan_status'][] = $row;
        }
    }
    
    // Resource usage by category
    $result = $conn->query("SELECT category, COUNT(*) as count FROM resources WHERE status = 'active' GROUP BY category ORDER BY count DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $reports['resource_usage'][] = $row;
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
    <title>Reports & Analytics - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>📊 Reports & Analytics</h1>
                <p>System reports and statistics</p>
            </div>
            
            <?php displayFlashMessage(); ?>
            
            <!-- User Activity Report -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">👥 User Activity</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($reports['user_activity'])): ?>
                        <p>No user activity data available.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Clients Created</th>
                                    <th>Assessments Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports['user_activity'] as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><?php echo getRoleBadge($row['role']); ?></td>
                                        <td><?php echo $row['clients']; ?></td>
                                        <td><?php echo $row['assessments']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Client Demographics -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🏠 Client Housing Status Distribution</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($reports['client_demographics'])): ?>
                        <p>No client data available.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Housing Status</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = array_sum(array_column($reports['client_demographics'], 'count'));
                                foreach ($reports['client_demographics'] as $row): 
                                    $percentage = $total > 0 ? round(($row['count'] / $total) * 100, 1) : 0;
                                ?>
                                    <tr>
                                        <td><?php echo getStatusBadge($row['housing_status']); ?></td>
                                        <td><?php echo $row['count']; ?></td>
                                        <td>
                                            <div style="background: var(--light-color); border-radius: 4px; height: 20px; overflow: hidden;">
                                                <div style="background: var(--primary-color); height: 100%; width: <?php echo $percentage; ?>%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">
                                                    <?php echo $percentage; ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Barrier Types -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🚧 Barrier Types Analysis</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($reports['barrier_types'])): ?>
                        <p>No barrier data available.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Barrier Type</th>
                                    <th>Total Count</th>
                                    <th>Resolved</th>
                                    <th>Resolution Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports['barrier_types'] as $row): 
                                    $rate = $row['count'] > 0 ? round(($row['resolved'] / $row['count']) * 100, 1) : 0;
                                ?>
                                    <tr>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $row['barrier_type'])); ?></td>
                                        <td><?php echo $row['count']; ?></td>
                                        <td><?php echo $row['resolved']; ?></td>
                                        <td>
                                            <span class="badge <?php echo $rate >= 50 ? 'badge-success' : 'badge-warning'; ?>">
                                                <?php echo $rate; ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Action Plan Status -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🎯 Action Plan Status</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($reports['action_plan_status'])): ?>
                        <p>No action plan data available.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports['action_plan_status'] as $row): ?>
                                    <tr>
                                        <td><?php echo getStatusBadge($row['status']); ?></td>
                                        <td><?php echo $row['count']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Resource Usage -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🏢 Resources by Category</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($reports['resource_usage'])): ?>
                        <p>No resource data available.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports['resource_usage'] as $row): ?>
                                    <tr>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $row['category'])); ?></td>
                                        <td><?php echo $row['count']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="index.php" class="btn btn-outline">Back to Admin Panel</a>
            </div>
        </div>
    </div>
</body>
</html>
