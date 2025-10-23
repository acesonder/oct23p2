<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

requireLogin();

$conn = getConnection();
$clients = [];

if ($conn) {
    $userId = getCurrentUserId();
    $userRole = getCurrentUserRole();
    
    if ($userRole === 'admin') {
        $result = $conn->query("SELECT c.*, u.full_name as created_by_name 
                               FROM clients c 
                               LEFT JOIN users u ON c.created_by = u.id 
                               ORDER BY c.created_at DESC");
    } else {
        $stmt = $conn->prepare("SELECT DISTINCT c.*, u.full_name as created_by_name 
                               FROM clients c 
                               LEFT JOIN users u ON c.created_by = u.id 
                               LEFT JOIN client_assignments ca ON c.id = ca.client_id 
                               WHERE ca.user_id = ? OR c.created_by = ? 
                               ORDER BY c.created_at DESC");
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $clients[] = $row;
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
    <title>Clients - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Clients</h1>
                <p>Manage client profiles and information</p>
            </div>
            
            <?php displayFlashMessage(); ?>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">All Clients (<?php echo count($clients); ?>)</h2>
                    <a href="create.php" class="btn btn-primary">Add New Client</a>
                </div>
                <div class="card-body">
                    <?php if (empty($clients)): ?>
                        <p class="text-center">No clients found. <a href="create.php">Create your first client</a></p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients as $client): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($client['phone'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($client['email'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($client['created_by_name'] ?? 'Unknown'); ?></td>
                                        <td><?php echo formatDate($client['created_at']); ?></td>
                                        <td>
                                            <a href="view.php?id=<?php echo $client['id']; ?>" class="btn btn-info" style="padding: 6px 12px; font-size: 14px;">View</a>
                                            <a href="/modules/assessment/create.php?client_id=<?php echo $client['id']; ?>" class="btn btn-success" style="padding: 6px 12px; font-size: 14px;">Assess</a>
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
