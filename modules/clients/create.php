<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

requireLogin();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $dob = $_POST['date_of_birth'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $address = sanitize($_POST['address'] ?? '');
    $emergencyName = sanitize($_POST['emergency_contact_name'] ?? '');
    $emergencyPhone = sanitize($_POST['emergency_contact_phone'] ?? '');
    
    // Validation
    if (empty($firstName)) $errors[] = 'First name is required';
    if (empty($lastName)) $errors[] = 'Last name is required';
    if (!empty($email) && !isValidEmail($email)) $errors[] = 'Invalid email format';
    
    if (empty($errors)) {
        $conn = getConnection();
        if ($conn) {
            $stmt = $conn->prepare("INSERT INTO clients (first_name, last_name, date_of_birth, phone, email, gender, address, emergency_contact_name, emergency_contact_phone, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $userId = getCurrentUserId();
            $stmt->bind_param("sssssssssi", $firstName, $lastName, $dob, $phone, $email, $gender, $address, $emergencyName, $emergencyPhone, $userId);
            
            if ($stmt->execute()) {
                $clientId = $conn->insert_id;
                
                // Also assign the creator to the client
                $assignStmt = $conn->prepare("INSERT INTO client_assignments (client_id, user_id, role) VALUES (?, ?, 'primary')");
                $assignStmt->bind_param("ii", $clientId, $userId);
                $assignStmt->execute();
                
                redirectWithMessage('view.php?id=' . $clientId, 'Client created successfully!', 'success');
            } else {
                $errors[] = 'Failed to create client: ' . $conn->error;
            }
            
            $stmt->close();
            closeConnection($conn);
        } else {
            $errors[] = 'Database connection error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Client - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Create New Client</h1>
                <p>Add basic client information to begin the intake process</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Client Information</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label" for="first_name">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="last_name">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label" for="date_of_birth">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                       value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="gender">Gender</label>
                                <select class="form-control" id="gender" name="gender">
                                    <option value="">Select...</option>
                                    <option value="male" <?php echo ($_POST['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo ($_POST['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="other" <?php echo ($_POST['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                    <option value="prefer_not_to_say" <?php echo ($_POST['gender'] ?? '') === 'prefer_not_to_say' ? 'selected' : ''; ?>>Prefer not to say</option>
                                </select>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label" for="phone">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="address">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <h3 style="margin: 30px 0 20px; font-size: 20px;">Emergency Contact</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label class="form-label" for="emergency_contact_name">Emergency Contact Name</label>
                                <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" 
                                       value="<?php echo htmlspecialchars($_POST['emergency_contact_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="emergency_contact_phone">Emergency Contact Phone</label>
                                <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" 
                                       value="<?php echo htmlspecialchars($_POST['emergency_contact_phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div style="margin-top: 30px; display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-primary">Create Client</button>
                            <a href="list.php" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
