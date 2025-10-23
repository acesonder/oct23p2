<?php
require_once '../config/database.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$conn = getConnection();
$resources = [];

if ($conn) {
    $result = $conn->query("SELECT * FROM resources ORDER BY name ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $resources[] = $row;
        }
    }
    closeConnection($conn);
}

// Handle resource creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = sanitize($_POST['name'] ?? '');
    $category = $_POST['category'] ?? '';
    $description = sanitize($_POST['description'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $website = sanitize($_POST['website'] ?? '');
    $hours = sanitize($_POST['hours_of_operation'] ?? '');
    $eligibility = sanitize($_POST['eligibility_criteria'] ?? '');
    $services = sanitize($_POST['services_offered'] ?? '');
    
    if (!empty($name) && !empty($category)) {
        $conn = getConnection();
        if ($conn) {
            $stmt = $conn->prepare("INSERT INTO resources (name, category, description, address, phone, email, website, hours_of_operation, eligibility_criteria, services_offered) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $name, $category, $description, $address, $phone, $email, $website, $hours, $eligibility, $services);
            
            if ($stmt->execute()) {
                redirectWithMessage('resources.php', 'Resource added successfully!', 'success');
            } else {
                redirectWithMessage('resources.php', 'Failed to add resource: ' . $conn->error, 'danger');
            }
            
            $stmt->close();
            closeConnection($conn);
        }
    }
}

// Handle status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $resourceId = intval($_POST['resource_id'] ?? 0);
    $newStatus = $_POST['new_status'] ?? 'active';
    
    $conn = getConnection();
    if ($conn) {
        $stmt = $conn->prepare("UPDATE resources SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $resourceId);
        $stmt->execute();
        $stmt->close();
        closeConnection($conn);
        
        redirectWithMessage('resources.php', 'Resource status updated!', 'success');
    }
}

$categories = [
    'shelter' => 'Shelter',
    'housing' => 'Housing',
    'detox' => 'Detox',
    'rehab' => 'Rehabilitation',
    'mental_health' => 'Mental Health',
    'employment' => 'Employment',
    'legal' => 'Legal Aid',
    'healthcare' => 'Healthcare',
    'food' => 'Food',
    'transportation' => 'Transportation',
    'education' => 'Education',
    'other' => 'Other'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resource Management - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>🏢 Resource Management</h1>
                <p>Manage community resources and services</p>
            </div>
            
            <?php displayFlashMessage(); ?>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Add New Resource</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="create">
                        
                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label">Resource Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Category *</label>
                                <select class="form-control" name="category" required>
                                    <option value="">Select...</option>
                                    <?php foreach ($categories as $value => $label): ?>
                                        <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Services Offered</label>
                            <textarea class="form-control" name="services_offered" rows="2"></textarea>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Website</label>
                                <input type="url" class="form-control" name="website">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label">Hours of Operation</label>
                                <textarea class="form-control" name="hours_of_operation" rows="2"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Eligibility Criteria</label>
                                <textarea class="form-control" name="eligibility_criteria" rows="2"></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Add Resource</button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">All Resources (<?php echo count($resources); ?>)</h2>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resources as $resource): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($resource['name']); ?></strong></td>
                                    <td><?php echo $categories[$resource['category']] ?? $resource['category']; ?></td>
                                    <td>
                                        <?php if ($resource['phone']): ?>
                                            <?php echo htmlspecialchars($resource['phone']); ?><br>
                                        <?php endif; ?>
                                        <?php if ($resource['email']): ?>
                                            <?php echo htmlspecialchars($resource['email']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo getStatusBadge($resource['status']); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                                            <input type="hidden" name="new_status" value="<?php echo $resource['status'] === 'active' ? 'inactive' : 'active'; ?>">
                                            <button type="submit" class="btn <?php echo $resource['status'] === 'active' ? 'btn-danger' : 'btn-success'; ?>" style="padding: 6px 12px; font-size: 14px;">
                                                <?php echo $resource['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="index.php" class="btn btn-outline">Back to Admin Panel</a>
            </div>
        </div>
    </div>
</body>
</html>
