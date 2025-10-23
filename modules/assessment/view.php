<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

requireLogin();

$assessmentId = $_GET['id'] ?? 0;
$assessment = null;
$client = null;
$barriers = [];
$actionPlans = [];

if ($assessmentId) {
    $conn = getConnection();
    if ($conn) {
        // Get assessment with client info
        $stmt = $conn->prepare("SELECT a.*, c.first_name, c.last_name, u.full_name as assessed_by_name 
                               FROM assessments a 
                               LEFT JOIN clients c ON a.client_id = c.id 
                               LEFT JOIN users u ON a.assessed_by = u.id 
                               WHERE a.id = ?");
        $stmt->bind_param("i", $assessmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $assessment = $result->fetch_assoc();
        $stmt->close();
        
        if ($assessment) {
            // Get barriers
            $stmt = $conn->prepare("SELECT * FROM barriers WHERE assessment_id = ? ORDER BY priority DESC");
            $stmt->bind_param("i", $assessmentId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $barriers[] = $row;
            }
            $stmt->close();
            
            // Get action plans
            $stmt = $conn->prepare("SELECT ap.*, u.full_name as assigned_to_name 
                                   FROM action_plans ap 
                                   LEFT JOIN users u ON ap.assigned_to = u.id 
                                   WHERE ap.assessment_id = ? 
                                   ORDER BY ap.priority DESC");
            $stmt->bind_param("i", $assessmentId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $actionPlans[] = $row;
            }
            $stmt->close();
        }
        
        closeConnection($conn);
    }
}

if (!$assessment) {
    redirectWithMessage('list.php', 'Assessment not found', 'danger');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Details - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Assessment Details</h1>
                <p>Client: <?php echo htmlspecialchars($assessment['first_name'] . ' ' . $assessment['last_name']); ?> | 
                   Date: <?php echo formatDate($assessment['assessment_date']); ?></p>
            </div>
            
            <?php displayFlashMessage(); ?>
            
            <div class="stats-grid">
                <div class="stat-card danger">
                    <div class="stat-card-header">Identified Barriers</div>
                    <div class="stat-card-value"><?php echo count($barriers); ?></div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-card-header">Action Plans</div>
                    <div class="stat-card-value"><?php echo count($actionPlans); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">Assessed By</div>
                    <div class="stat-card-value" style="font-size: 18px;"><?php echo htmlspecialchars($assessment['assessed_by_name']); ?></div>
                </div>
            </div>
            
            <!-- Housing Status -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🏠 Housing Status</h2>
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong> <?php echo getStatusBadge($assessment['housing_status']); ?></p>
                    <p><strong>Duration:</strong> <?php echo htmlspecialchars($assessment['housing_duration'] ?? 'Not specified'); ?></p>
                    <?php if ($assessment['housing_notes']): ?>
                        <p><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($assessment['housing_notes'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Addiction History -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">💊 Addiction History</h2>
                </div>
                <div class="card-body">
                    <p><strong>Has Addiction:</strong> <?php echo $assessment['has_addiction'] ? '<span class="badge badge-warning">Yes</span>' : '<span class="badge badge-success">No</span>'; ?></p>
                    <?php if ($assessment['has_addiction']): ?>
                        <p><strong>Types:</strong> <?php echo htmlspecialchars($assessment['addiction_types'] ?? 'Not specified'); ?></p>
                        <p><strong>Duration:</strong> <?php echo htmlspecialchars($assessment['addiction_duration'] ?? 'Not specified'); ?></p>
                        <p><strong>Previous Treatment:</strong> <?php echo $assessment['previous_treatment'] ? 'Yes' : 'No'; ?></p>
                        <?php if ($assessment['treatment_notes']): ?>
                            <p><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($assessment['treatment_notes'])); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Employment -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">💼 Employment Situation</h2>
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong> <?php echo getStatusBadge($assessment['employment_status']); ?></p>
                    <?php if ($assessment['employment_history']): ?>
                        <p><strong>History:</strong> <?php echo nl2br(htmlspecialchars($assessment['employment_history'])); ?></p>
                    <?php endif; ?>
                    <?php if ($assessment['employment_barriers']): ?>
                        <p><strong>Barriers:</strong> <?php echo nl2br(htmlspecialchars($assessment['employment_barriers'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Mental Health -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🧠 Mental Health</h2>
                </div>
                <div class="card-body">
                    <p><strong>Has Concerns:</strong> <?php echo $assessment['mental_health_concerns'] ? '<span class="badge badge-warning">Yes</span>' : '<span class="badge badge-success">No</span>'; ?></p>
                    <?php if ($assessment['mental_health_concerns']): ?>
                        <p><strong>Diagnosis:</strong> <?php echo htmlspecialchars($assessment['mental_health_diagnosis'] ?? 'Not specified'); ?></p>
                        <p><strong>Currently in Treatment:</strong> <?php echo $assessment['currently_in_treatment'] ? 'Yes' : 'No'; ?></p>
                        <?php if ($assessment['mental_health_notes']): ?>
                            <p><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($assessment['mental_health_notes'])); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Daily Living & Legal -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">🚗 Daily Living</h2>
                    </div>
                    <div class="card-body">
                        <p><strong>Transportation:</strong> <?php echo $assessment['transportation_access'] ? '✅ Has access' : '❌ No access'; ?></p>
                        <p><strong>Food Security:</strong> <?php echo getStatusBadge($assessment['food_security']); ?></p>
                        <p><strong>Healthcare:</strong> <?php echo $assessment['healthcare_access'] ? '✅ Has access' : '❌ No access'; ?></p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">⚖️ Legal & Documentation</h2>
                    </div>
                    <div class="card-body">
                        <p><strong>Legal Issues:</strong> <?php echo $assessment['has_legal_issues'] ? '<span class="badge badge-warning">Yes</span>' : '<span class="badge badge-success">No</span>'; ?></p>
                        <p><strong>Has ID Documents:</strong> <?php echo $assessment['has_id_documents'] ? '✅ Yes' : '❌ No'; ?></p>
                        <?php if (!$assessment['has_id_documents'] && $assessment['missing_documents']): ?>
                            <p><strong>Missing:</strong> <?php echo htmlspecialchars($assessment['missing_documents']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Barriers -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🚧 Identified Barriers</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($barriers)): ?>
                        <p>No barriers identified.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($barriers as $barrier): ?>
                                    <tr>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $barrier['barrier_type'])); ?></td>
                                        <td><?php echo htmlspecialchars($barrier['description']); ?></td>
                                        <td><?php echo getPriorityBadge($barrier['priority']); ?></td>
                                        <td><?php echo getStatusBadge($barrier['status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Action Plans -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🎯 Recommended Action Plans</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($actionPlans)): ?>
                        <p>No action plans generated.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($actionPlans as $plan): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($plan['description']); ?></td>
                                        <td><?php echo getPriorityBadge($plan['priority']); ?></td>
                                        <td><?php echo getStatusBadge($plan['status']); ?></td>
                                        <td><?php echo htmlspecialchars($plan['assigned_to_name'] ?? 'Unassigned'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Overall Assessment -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📊 Overall Assessment Summary</h2>
                </div>
                <div class="card-body">
                    <?php if ($assessment['primary_barriers']): ?>
                        <p><strong>Primary Barriers:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($assessment['primary_barriers'])); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($assessment['immediate_needs']): ?>
                        <p style="margin-top: 20px;"><strong>Immediate Needs:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($assessment['immediate_needs'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="/modules/clients/view.php?id=<?php echo $assessment['client_id']; ?>" class="btn btn-primary">Back to Client Profile</a>
                <a href="list.php" class="btn btn-outline">All Assessments</a>
            </div>
        </div>
    </div>
</body>
</html>
