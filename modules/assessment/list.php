<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

requireLogin();

$conn = getConnection();
$assessments = [];

if ($conn) {
    $userId = getCurrentUserId();
    $userRole = getCurrentUserRole();
    
    if ($userRole === 'admin') {
        $result = $conn->query("SELECT a.*, c.first_name, c.last_name, u.full_name as assessed_by_name 
                               FROM assessments a 
                               LEFT JOIN clients c ON a.client_id = c.id 
                               LEFT JOIN users u ON a.assessed_by = u.id 
                               ORDER BY a.assessment_date DESC");
    } else {
        $stmt = $conn->prepare("SELECT a.*, c.first_name, c.last_name, u.full_name as assessed_by_name 
                               FROM assessments a 
                               LEFT JOIN clients c ON a.client_id = c.id 
                               LEFT JOIN users u ON a.assessed_by = u.id 
                               WHERE a.assessed_by = ? 
                               ORDER BY a.assessment_date DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $assessments[] = $row;
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
    <title>Assessments - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>All Assessments</h1>
                <p>View and manage client assessments</p>
            </div>
            
            <?php displayFlashMessage(); ?>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Assessments (<?php echo count($assessments); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($assessments)): ?>
                        <p class="text-center">No assessments found. <a href="/modules/clients/list.php">Select a client to begin assessment</a></p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Client Name</th>
                                    <th>Assessment Date</th>
                                    <th>Assessed By</th>
                                    <th>Housing Status</th>
                                    <th>Employment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assessments as $assessment): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($assessment['first_name'] . ' ' . $assessment['last_name']); ?></strong></td>
                                        <td><?php echo formatDate($assessment['assessment_date']); ?></td>
                                        <td><?php echo htmlspecialchars($assessment['assessed_by_name']); ?></td>
                                        <td><?php echo getStatusBadge($assessment['housing_status']); ?></td>
                                        <td><?php echo getStatusBadge($assessment['employment_status']); ?></td>
                                        <td>
                                            <a href="view.php?id=<?php echo $assessment['id']; ?>" class="btn btn-info" style="padding: 6px 12px; font-size: 14px;">View Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
