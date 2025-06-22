<?php
require '../config.php';
require '../includes/functions.php';

// Check for token (e.g., from header or GET param)
$token = $_GET['token'] ?? '';

$validTokens = ['70f7edc6e3b58cce8635fe1f67ae7235d2512bb57691d7fcaefe9d1299aaf9ec']; // replace with your token

if (!in_array($token, $validTokens)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['log']) || empty($data['log'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing log data']);
    exit;
}

$log = $data['log'];
$encrypted = encryptData($log);
$adminId = 1; // default or passed through the payload if needed

$stmt = $conn->prepare("INSERT INTO navigation_logs (admin_id, log_data) VALUES (?, ?)");
$stmt->bind_param("is", $adminId, $encrypted);
$stmt->execute();

echo json_encode(['success' => true, 'message' => 'Log saved securely']);
