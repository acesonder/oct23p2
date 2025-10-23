<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

requireLogin();

$clientId = $_GET['client_id'] ?? 0;
$client = null;

if ($clientId) {
    $conn = getConnection();
    if ($conn) {
        $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->bind_param("i", $clientId);
        $stmt->execute();
        $result = $stmt->get_result();
        $client = $result->fetch_assoc();
        $stmt->close();
        closeConnection($conn);
    }
}

if (!$client) {
    redirectWithMessage('/modules/clients/list.php', 'Client not found', 'danger');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the assessment form
    $conn = getConnection();
    if ($conn) {
        $userId = getCurrentUserId();
        
        // Housing data
        $housingStatus = $_POST['housing_status'] ?? '';
        $housingDuration = sanitize($_POST['housing_duration'] ?? '');
        $housingNotes = sanitize($_POST['housing_notes'] ?? '');
        
        // Addiction data
        $hasAddiction = isset($_POST['has_addiction']) ? 1 : 0;
        $addictionTypes = sanitize($_POST['addiction_types'] ?? '');
        $addictionDuration = sanitize($_POST['addiction_duration'] ?? '');
        $previousTreatment = isset($_POST['previous_treatment']) ? 1 : 0;
        $treatmentNotes = sanitize($_POST['treatment_notes'] ?? '');
        
        // Employment data
        $employmentStatus = $_POST['employment_status'] ?? '';
        $employmentHistory = sanitize($_POST['employment_history'] ?? '');
        $employmentBarriers = sanitize($_POST['employment_barriers'] ?? '');
        
        // Mental health data
        $mentalHealthConcerns = isset($_POST['mental_health_concerns']) ? 1 : 0;
        $mentalHealthDiagnosis = sanitize($_POST['mental_health_diagnosis'] ?? '');
        $currentlyInTreatment = isset($_POST['currently_in_treatment']) ? 1 : 0;
        $mentalHealthNotes = sanitize($_POST['mental_health_notes'] ?? '');
        
        // Support network
        $hasFamilySupport = isset($_POST['has_family_support']) ? 1 : 0;
        $supportNetworkDetails = sanitize($_POST['support_network_details'] ?? '');
        
        // Legal issues
        $hasLegalIssues = isset($_POST['has_legal_issues']) ? 1 : 0;
        $legalIssuesDetails = sanitize($_POST['legal_issues_details'] ?? '');
        $hasIdDocuments = isset($_POST['has_id_documents']) ? 1 : 0;
        $missingDocuments = sanitize($_POST['missing_documents'] ?? '');
        
        // Daily living
        $transportationAccess = isset($_POST['transportation_access']) ? 1 : 0;
        $foodSecurity = $_POST['food_security'] ?? '';
        $healthcareAccess = isset($_POST['healthcare_access']) ? 1 : 0;
        
        // Overall
        $primaryBarriers = sanitize($_POST['primary_barriers'] ?? '');
        $immediateNeeds = sanitize($_POST['immediate_needs'] ?? '');
        
        // Validation
        if (empty($housingStatus)) $errors[] = 'Housing status is required';
        if (empty($employmentStatus)) $errors[] = 'Employment status is required';
        if (empty($foodSecurity)) $errors[] = 'Food security is required';
        
        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO assessments (
                client_id, assessed_by, housing_status, housing_duration, housing_notes,
                has_addiction, addiction_types, addiction_duration, previous_treatment, treatment_notes,
                employment_status, employment_history, employment_barriers,
                mental_health_concerns, mental_health_diagnosis, currently_in_treatment, mental_health_notes,
                has_family_support, support_network_details,
                has_legal_issues, legal_issues_details, has_id_documents, missing_documents,
                transportation_access, food_security, healthcare_access,
                primary_barriers, immediate_needs
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("iisssississsisissississsss",
                $clientId, $userId, $housingStatus, $housingDuration, $housingNotes,
                $hasAddiction, $addictionTypes, $addictionDuration, $previousTreatment, $treatmentNotes,
                $employmentStatus, $employmentHistory, $employmentBarriers,
                $mentalHealthConcerns, $mentalHealthDiagnosis, $currentlyInTreatment, $mentalHealthNotes,
                $hasFamilySupport, $supportNetworkDetails,
                $hasLegalIssues, $legalIssuesDetails, $hasIdDocuments, $missingDocuments,
                $transportationAccess, $foodSecurity, $healthcareAccess,
                $primaryBarriers, $immediateNeeds
            );
            
            if ($stmt->execute()) {
                $assessmentId = $conn->insert_id;
                
                // Identify and record barriers
                identifyBarriers($conn, $assessmentId, $_POST);
                
                // Generate action plans
                generateActionPlans($conn, $assessmentId, $_POST);
                
                redirectWithMessage('view.php?id=' . $assessmentId, 'Assessment completed successfully!', 'success');
            } else {
                $errors[] = 'Failed to save assessment: ' . $conn->error;
            }
            
            $stmt->close();
        }
        
        closeConnection($conn);
    } else {
        $errors[] = 'Database connection error';
    }
}

// Function to identify barriers from assessment
function identifyBarriers($conn, $assessmentId, $data) {
    $barriers = [];
    
    // Housing barriers
    if (in_array($data['housing_status'] ?? '', ['homeless', 'shelter', 'unstable'])) {
        $barriers[] = ['housing', 'Unstable housing situation', 'high'];
    }
    
    // Addiction barriers
    if (!empty($data['has_addiction'])) {
        $barriers[] = ['addiction', 'Active addiction requiring treatment', 'high'];
    }
    
    // Employment barriers
    if ($data['employment_status'] === 'unemployed') {
        $barriers[] = ['employment', 'Currently unemployed', 'medium'];
    }
    
    // Mental health barriers
    if (!empty($data['mental_health_concerns']) && empty($data['currently_in_treatment'])) {
        $barriers[] = ['mental_health', 'Untreated mental health concerns', 'high'];
    }
    
    // Documentation barriers
    if (empty($data['has_id_documents'])) {
        $barriers[] = ['documentation', 'Missing identification documents', 'high'];
    }
    
    // Transportation barriers
    if (empty($data['transportation_access'])) {
        $barriers[] = ['transportation', 'Lack of transportation access', 'medium'];
    }
    
    // Healthcare barriers
    if (empty($data['healthcare_access'])) {
        $barriers[] = ['healthcare', 'Lack of healthcare access', 'medium'];
    }
    
    // Legal barriers
    if (!empty($data['has_legal_issues'])) {
        $barriers[] = ['legal', 'Active legal issues', 'medium'];
    }
    
    // Insert barriers
    foreach ($barriers as $barrier) {
        $stmt = $conn->prepare("INSERT INTO barriers (assessment_id, barrier_type, description, priority) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $assessmentId, $barrier[0], $barrier[1], $barrier[2]);
        $stmt->execute();
        $stmt->close();
    }
}

// Function to generate action plans
function generateActionPlans($conn, $assessmentId, $data) {
    $actions = [];
    
    // Housing recommendations
    if (in_array($data['housing_status'] ?? '', ['homeless', 'shelter'])) {
        $actions[] = ['Connect with emergency shelter services', 'critical'];
        $actions[] = ['Apply for transitional housing programs', 'high'];
    }
    
    // Addiction treatment
    if (!empty($data['has_addiction']) && empty($data['previous_treatment'])) {
        $actions[] = ['Refer to detox program for substance abuse treatment', 'critical'];
        $actions[] = ['Enroll in addiction support groups', 'high'];
    }
    
    // Employment support
    if ($data['employment_status'] === 'unemployed') {
        $actions[] = ['Enroll in job training program', 'medium'];
        $actions[] = ['Connect with employment counseling services', 'medium'];
    }
    
    // Mental health support
    if (!empty($data['mental_health_concerns']) && empty($data['currently_in_treatment'])) {
        $actions[] = ['Refer to mental health clinic for evaluation', 'high'];
        $actions[] = ['Connect with counseling services', 'high'];
    }
    
    // Documentation assistance
    if (empty($data['has_id_documents'])) {
        $actions[] = ['Connect with legal aid for ID document recovery', 'critical'];
    }
    
    // Transportation solutions
    if (empty($data['transportation_access'])) {
        $actions[] = ['Provide bus pass or transportation vouchers', 'medium'];
    }
    
    // Healthcare access
    if (empty($data['healthcare_access'])) {
        $actions[] = ['Enroll in local healthcare program', 'medium'];
        $actions[] = ['Schedule medical assessment', 'medium'];
    }
    
    // Insert action plans
    foreach ($actions as $action) {
        $stmt = $conn->prepare("INSERT INTO action_plans (assessment_id, action_type, description, priority, status) VALUES (?, 'recommendation', ?, ?, 'recommended')");
        $stmt->bind_param("iss", $assessmentId, $action[0], $action[1]);
        $stmt->execute();
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Assessment - OUTSINC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .assessment-section {
            margin-bottom: 40px;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        .section-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary-color);
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Comprehensive Intake Assessment</h1>
                <p>Client: <?php echo htmlspecialchars($client['first_name'] . ' ' . $client['last_name']); ?></p>
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
            
            <form method="POST" action="">
                <!-- Housing Status Section -->
                <div class="assessment-section">
                    <h2 class="section-title">🏠 Housing Status</h2>
                    
                    <div class="form-group">
                        <label class="form-label">Current Housing Status *</label>
                        <select class="form-control" name="housing_status" required>
                            <option value="">Select...</option>
                            <option value="homeless">Homeless (unsheltered)</option>
                            <option value="shelter">Emergency Shelter</option>
                            <option value="transitional">Transitional Housing</option>
                            <option value="unstable">Unstable Housing</option>
                            <option value="stable">Stable Housing</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">How long in current situation?</label>
                        <input type="text" class="form-control" name="housing_duration" 
                               placeholder="e.g., 3 months, 1 year">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Additional Housing Notes</label>
                        <textarea class="form-control" name="housing_notes" rows="3" 
                                  placeholder="Any additional information about housing situation..."></textarea>
                    </div>
                </div>
                
                <!-- Addiction History Section -->
                <div class="assessment-section">
                    <h2 class="section-title">💊 Addiction History</h2>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="has_addiction" name="has_addiction" value="1">
                        <label for="has_addiction">Client has substance use disorder or addiction concerns</label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Type(s) of Substance(s)</label>
                        <input type="text" class="form-control" name="addiction_types" 
                               placeholder="e.g., Alcohol, Opioids, Methamphetamine">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Duration of Use</label>
                        <input type="text" class="form-control" name="addiction_duration" 
                               placeholder="e.g., 5 years">
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="previous_treatment" name="previous_treatment" value="1">
                        <label for="previous_treatment">Has received previous treatment</label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Treatment History & Notes</label>
                        <textarea class="form-control" name="treatment_notes" rows="3" 
                                  placeholder="Details about previous treatment, relapses, current recovery status..."></textarea>
                    </div>
                </div>
                
                <!-- Employment Section -->
                <div class="assessment-section">
                    <h2 class="section-title">💼 Employment Situation</h2>
                    
                    <div class="form-group">
                        <label class="form-label">Current Employment Status *</label>
                        <select class="form-control" name="employment_status" required>
                            <option value="">Select...</option>
                            <option value="unemployed">Unemployed</option>
                            <option value="part_time">Part-time Employment</option>
                            <option value="full_time">Full-time Employment</option>
                            <option value="disabled">Disabled / Unable to Work</option>
                            <option value="student">Student</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Employment History</label>
                        <textarea class="form-control" name="employment_history" rows="3" 
                                  placeholder="Previous jobs, skills, work experience..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Barriers to Employment</label>
                        <textarea class="form-control" name="employment_barriers" rows="3" 
                                  placeholder="What prevents the client from obtaining/maintaining employment?"></textarea>
                    </div>
                </div>
                
                <!-- Mental Health Section -->
                <div class="assessment-section">
                    <h2 class="section-title">🧠 Mental Health</h2>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="mental_health_concerns" name="mental_health_concerns" value="1">
                        <label for="mental_health_concerns">Client has mental health concerns</label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Diagnosis (if known)</label>
                        <input type="text" class="form-control" name="mental_health_diagnosis" 
                               placeholder="e.g., Depression, PTSD, Bipolar Disorder">
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="currently_in_treatment" name="currently_in_treatment" value="1">
                        <label for="currently_in_treatment">Currently receiving mental health treatment</label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Mental Health Notes</label>
                        <textarea class="form-control" name="mental_health_notes" rows="3" 
                                  placeholder="Additional information about mental health status, medications, symptoms..."></textarea>
                    </div>
                </div>
                
                <!-- Support Networks Section -->
                <div class="assessment-section">
                    <h2 class="section-title">👨‍👩‍👧‍👦 Support Networks</h2>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="has_family_support" name="has_family_support" value="1">
                        <label for="has_family_support">Client has family or social support</label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Support Network Details</label>
                        <textarea class="form-control" name="support_network_details" rows="3" 
                                  placeholder="Who can the client rely on? Family, friends, community connections..."></textarea>
                    </div>
                </div>
                
                <!-- Legal Issues Section -->
                <div class="assessment-section">
                    <h2 class="section-title">⚖️ Legal Issues</h2>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="has_legal_issues" name="has_legal_issues" value="1">
                        <label for="has_legal_issues">Client has active legal issues</label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Legal Issues Details</label>
                        <textarea class="form-control" name="legal_issues_details" rows="3" 
                                  placeholder="Pending charges, warrants, probation, child custody issues..."></textarea>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="has_id_documents" name="has_id_documents" value="1">
                        <label for="has_id_documents">Client has valid ID documents</label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Missing Documents</label>
                        <input type="text" class="form-control" name="missing_documents" 
                               placeholder="e.g., Birth certificate, Social Security card, Driver's license">
                    </div>
                </div>
                
                <!-- Daily Living Section -->
                <div class="assessment-section">
                    <h2 class="section-title">🚗 Daily Living Challenges</h2>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="transportation_access" name="transportation_access" value="1">
                        <label for="transportation_access">Client has reliable transportation</label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Food Security *</label>
                        <select class="form-control" name="food_security" required>
                            <option value="">Select...</option>
                            <option value="secure">Food Secure</option>
                            <option value="insecure">Food Insecure</option>
                            <option value="very_insecure">Very Food Insecure</option>
                        </select>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="healthcare_access" name="healthcare_access" value="1">
                        <label for="healthcare_access">Client has access to healthcare</label>
                    </div>
                </div>
                
                <!-- Overall Assessment Section -->
                <div class="assessment-section">
                    <h2 class="section-title">📊 Overall Assessment</h2>
                    
                    <div class="form-group">
                        <label class="form-label">Primary Barriers (What's keeping the client "stuck"?)</label>
                        <textarea class="form-control" name="primary_barriers" rows="4" 
                                  placeholder="Identify the main obstacles preventing stability..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Immediate Needs (Top priorities)</label>
                        <textarea class="form-control" name="immediate_needs" rows="4" 
                                  placeholder="What does the client need most urgently?"></textarea>
                    </div>
                </div>
                
                <div style="margin-top: 30px; display: flex; gap: 10px; justify-content: center; padding: 30px; background: white; border-radius: 12px;">
                    <button type="submit" class="btn btn-primary btn-large">Complete Assessment & Generate Action Plan</button>
                    <a href="/modules/clients/view.php?id=<?php echo $client['id']; ?>" class="btn btn-outline btn-large">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
