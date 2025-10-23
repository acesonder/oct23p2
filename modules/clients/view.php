<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

requireLogin();

$clientId = $_GET['id'] ?? 0;
$client = null;
$assessments = [];
$notes = [];

if ($clientId) {
    $conn = getConnection();
    if ($conn) {
        // Get client details
        $stmt = $conn->prepare("SELECT c.*, u.full_name as created_by_name FROM clients c LEFT JOIN users u ON c.created_by = u.id WHERE c.id = ?");
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        $result = $stmt->get_result();
        $client = $result->fetch_assoc();
        $stmt->close();
        
        if ($client) {
            // Get assessments
            $stmt = $conn->prepare("SELECT a.*, u.full_name as assessed_by_name FROM assessments a LEFT JOIN users u ON a.assessed_by = u.id WHERE a.client_id = ? ORDER BY a.assessment_date DESC");
            $stmt->bind_param("i", $clientId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $assessments[] = $row;
            }
            $stmt->close();
            
            // Get notes
            $stmt = $conn->prepare("SELECT n.*, u.full_name as user_name FROM team_notes n LEFT JOIN users u ON n.user_id = u.id WHERE n.client_id = ? ORDER BY n.created_at DESC LIMIT 10");
            $stmt->bind_param("i", $clientId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $notes[] = $row;
            }
            $stmt->close();
        }
        
        closeConnection($conn);
    }
}

if (!$client) {
    redirectWithMessage('list.php', 'Client not found', 'danger');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?> - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1><?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></h1>
                <p>Client Profile and History</p>
            </div>
            
            <?php displayFlashMessage(); ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">Total Assessments</div>
                    <div class="stat-card-value"><?php echo count($assessments); ?></div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-card-header">Team Notes</div>
                    <div class="stat-card-value"><?php echo count($notes); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">Age</div>
                    <div class="stat-card-value"><?php echo $client['date_of_birth'] ? calculateAge($client['date_of_birth']) : 'N/A'; ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">Member Since</div>
                    <div class="stat-card-value" style="font-size: 18px;"><?php echo formatDate($client['created_at']); ?></div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Client Information</h2>
                    <a href="/modules/assessment/create.php?client_id=<?php echo $client['id']; ?>" class="btn btn-primary">New Assessment</a>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                        <div>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($client['phone'] ?? 'Not provided'); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($client['email'] ?? 'Not provided'); ?></p>
                            <p><strong>Gender:</strong> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $client['gender'] ?? 'Not specified'))); ?></p>
                            <p><strong>Date of Birth:</strong> <?php echo $client['date_of_birth'] ? formatDate($client['date_of_birth']) : 'Not provided'; ?></p>
                        </div>
                        <div>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($client['address'] ?? 'Not provided'); ?></p>
                            <p><strong>Emergency Contact:</strong> <?php echo htmlspecialchars($client['emergency_contact_name'] ?? 'Not provided'); ?></p>
                            <p><strong>Emergency Phone:</strong> <?php echo htmlspecialchars($client['emergency_contact_phone'] ?? 'Not provided'); ?></p>
                            <p><strong>Created By:</strong> <?php echo htmlspecialchars($client['created_by_name']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Assessment History</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($assessments)): ?>
                        <p>No assessments yet. <a href="/modules/assessment/create.php?client_id=<?php echo $client['id']; ?>">Create first assessment</a></p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Assessed By</th>
                                    <th>Housing Status</th>
                                    <th>Employment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assessments as $assessment): ?>
                                    <tr>
                                        <td><?php echo formatDate($assessment['assessment_date']); ?></td>
                                        <td><?php echo htmlspecialchars($assessment['assessed_by_name']); ?></td>
                                        <td><?php echo getStatusBadge($assessment['housing_status']); ?></td>
                                        <td><?php echo getStatusBadge($assessment['employment_status']); ?></td>
                                        <td>
                                            <a href="/modules/assessment/view.php?id=<?php echo $assessment['id']; ?>" class="btn btn-info" style="padding: 6px 12px; font-size: 14px;">View Details</a>
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
                    <h2 class="card-title">Recent Notes</h2>
                    <button class="btn btn-primary" onclick="document.getElementById('noteForm').style.display='block'">Add Note</button>
                </div>
                <div class="card-body">
                    <div id="noteForm" style="display: none; margin-bottom: 20px; padding: 20px; background: #f9fafb; border-radius: 8px;">
                        <form id="addNoteForm">
                            <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                            <div class="form-group">
                                <label class="form-label">Note Type</label>
                                <select class="form-control" name="note_type" required>
                                    <option value="contact">Contact</option>
                                    <option value="assessment">Assessment</option>
                                    <option value="intervention">Intervention</option>
                                    <option value="follow_up">Follow-up</option>
                                    <option value="general">General</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Note</label>
                                <textarea class="form-control" name="note" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Save Note</button>
                            <button type="button" class="btn btn-outline" onclick="document.getElementById('noteForm').style.display='none'">Cancel</button>
                        </form>
                    </div>
                    
                    <div id="notesList">
                        <?php if (empty($notes)): ?>
                            <p>No notes yet.</p>
                        <?php else: ?>
                            <?php foreach ($notes as $note): ?>
                                <div style="padding: 15px; border-left: 3px solid var(--primary-color); background: var(--light-color); margin-bottom: 15px; border-radius: 4px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                        <strong><?php echo htmlspecialchars($note['user_name']); ?></strong>
                                        <span style="color: var(--gray-color); font-size: 14px;">
                                            <?php echo formatDateTime($note['created_at']); ?> - 
                                            <span class="badge badge-info"><?php echo ucfirst($note['note_type']); ?></span>
                                        </span>
                                    </div>
                                    <p style="margin: 0;"><?php echo nl2br(htmlspecialchars($note['note'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/assets/js/client.js"></script>
</body>
</html>
