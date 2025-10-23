<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Invalid request method', 405);
}

$clientId = intval($_POST['client_id'] ?? 0);
$noteType = sanitize($_POST['note_type'] ?? '');
$note = sanitize($_POST['note'] ?? '');
$userId = getCurrentUserId();

if (!$clientId || !$noteType || !$note) {
    errorResponse('Missing required fields');
}

$conn = getConnection();
if (!$conn) {
    errorResponse('Database connection error', 500);
}

$stmt = $conn->prepare("INSERT INTO team_notes (client_id, user_id, note_type, note) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $clientId, $userId, $noteType, $note);

if ($stmt->execute()) {
    successResponse(['note_id' => $conn->insert_id], 'Note added successfully');
} else {
    errorResponse('Failed to add note');
}

$stmt->close();
closeConnection($conn);
?>
