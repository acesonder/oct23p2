<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

requireLogin();

$conn = getConnection();
$resources = [];
$searchTerm = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

if ($conn) {
    $query = "SELECT * FROM resources WHERE status = 'active'";
    $params = [];
    $types = '';
    
    if ($searchTerm) {
        $query .= " AND (name LIKE ? OR description LIKE ? OR services_offered LIKE ?)";
        $searchParam = "%$searchTerm%";
        $params[] = &$searchParam;
        $params[] = &$searchParam;
        $params[] = &$searchParam;
        $types .= 'sss';
    }
    
    if ($categoryFilter) {
        $query .= " AND category = ?";
        $params[] = &$categoryFilter;
        $types .= 's';
    }
    
    $query .= " ORDER BY name ASC";
    
    if ($params) {
        $stmt = $conn->prepare($query);
        array_unshift($params, $types);
        call_user_func_array([$stmt, 'bind_param'], $params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($query);
    }
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $resources[] = $row;
        }
    }
    
    closeConnection($conn);
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
    <title>Resource Directory - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Resource Directory</h1>
                <p>Searchable database of local services and resources</p>
            </div>
            
            <?php displayFlashMessage(); ?>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Search Resources</h2>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 15px; align-items: end;">
                            <div class="form-group" style="margin: 0;">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($searchTerm); ?>" 
                                       placeholder="Search by name, description, or services...">
                            </div>
                            
                            <div class="form-group" style="margin: 0;">
                                <label class="form-label">Category</label>
                                <select class="form-control" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo $categoryFilter === $value ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Resources (<?php echo count($resources); ?>)</h2>
                    <?php if (hasRole('admin')): ?>
                        <a href="/admin/resources.php" class="btn btn-success">Manage Resources</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($resources)): ?>
                        <p class="text-center">No resources found matching your criteria.</p>
                    <?php else: ?>
                        <div style="display: grid; gap: 20px;">
                            <?php foreach ($resources as $resource): ?>
                                <div style="padding: 20px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--light-color);">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                        <h3 style="margin: 0; font-size: 20px; color: var(--primary-color);">
                                            <?php echo htmlspecialchars($resource['name']); ?>
                                        </h3>
                                        <span class="badge badge-primary"><?php echo $categories[$resource['category']] ?? $resource['category']; ?></span>
                                    </div>
                                    
                                    <?php if ($resource['description']): ?>
                                        <p style="margin: 10px 0; color: var(--gray-color);">
                                            <?php echo htmlspecialchars($resource['description']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                                        <div>
                                            <?php if ($resource['address']): ?>
                                                <p style="margin: 5px 0;"><strong>📍 Address:</strong> <?php echo htmlspecialchars($resource['address']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($resource['phone']): ?>
                                                <p style="margin: 5px 0;"><strong>📞 Phone:</strong> <a href="tel:<?php echo htmlspecialchars($resource['phone']); ?>"><?php echo htmlspecialchars($resource['phone']); ?></a></p>
                                            <?php endif; ?>
                                            <?php if ($resource['email']): ?>
                                                <p style="margin: 5px 0;"><strong>✉️ Email:</strong> <a href="mailto:<?php echo htmlspecialchars($resource['email']); ?>"><?php echo htmlspecialchars($resource['email']); ?></a></p>
                                            <?php endif; ?>
                                            <?php if ($resource['website']): ?>
                                                <p style="margin: 5px 0;"><strong>🌐 Website:</strong> <a href="<?php echo htmlspecialchars($resource['website']); ?>" target="_blank"><?php echo htmlspecialchars($resource['website']); ?></a></p>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div>
                                            <?php if ($resource['hours_of_operation']): ?>
                                                <p style="margin: 5px 0;"><strong>🕐 Hours:</strong> <?php echo nl2br(htmlspecialchars($resource['hours_of_operation'])); ?></p>
                                            <?php endif; ?>
                                            <?php if ($resource['services_offered']): ?>
                                                <p style="margin: 5px 0;"><strong>🎯 Services:</strong> <?php echo htmlspecialchars($resource['services_offered']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($resource['eligibility_criteria']): ?>
                                                <p style="margin: 5px 0;"><strong>✅ Eligibility:</strong> <?php echo htmlspecialchars($resource['eligibility_criteria']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
