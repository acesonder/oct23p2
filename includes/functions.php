<?php
// Common utility functions

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Format datetime
function formatDateTime($datetime) {
    return date('M d, Y g:i A', strtotime($datetime));
}

// Calculate age from date of birth
function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    return $birthDate->diff($today)->y;
}

// Get role badge HTML
function getRoleBadge($role) {
    $badges = [
        'admin' => '<span class="badge badge-danger">Admin</span>',
        'outreach' => '<span class="badge badge-primary">Outreach</span>',
        'provider' => '<span class="badge badge-success">Provider</span>'
    ];
    return $badges[$role] ?? '<span class="badge badge-secondary">Unknown</span>';
}

// Get status badge HTML
function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="badge badge-success">Active</span>',
        'inactive' => '<span class="badge badge-secondary">Inactive</span>',
        'completed' => '<span class="badge badge-success">Completed</span>',
        'in_progress' => '<span class="badge badge-warning">In Progress</span>',
        'identified' => '<span class="badge badge-info">Identified</span>',
        'resolved' => '<span class="badge badge-success">Resolved</span>',
        'cannot_resolve' => '<span class="badge badge-danger">Cannot Resolve</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
}

// Get priority badge HTML
function getPriorityBadge($priority) {
    $badges = [
        'low' => '<span class="badge badge-secondary">Low</span>',
        'medium' => '<span class="badge badge-info">Medium</span>',
        'high' => '<span class="badge badge-warning">High</span>',
        'critical' => '<span class="badge badge-danger">Critical</span>'
    ];
    return $badges[$priority] ?? '<span class="badge badge-secondary">Unknown</span>';
}

// Redirect with message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit;
}

// Display flash message
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        echo "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
              </div>";
    }
}

// JSON response
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Error response
function errorResponse($message, $statusCode = 400) {
    jsonResponse(['success' => false, 'error' => $message], $statusCode);
}

// Success response
function successResponse($data = [], $message = 'Success') {
    jsonResponse(['success' => true, 'message' => $message, 'data' => $data]);
}
?>
