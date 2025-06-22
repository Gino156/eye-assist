<?php
require '../config.php';
require '../includes/functions.php';
$config = require '../env.php';

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

$token = $_GET['token'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if ($token !== $config['API_TOKEN']) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized. Invalid API token.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['log_data']) || !isset($data['device_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing log_data or device_id.']);
    exit;
}

$log = $data['log_data'];
$deviceId = intval($data['device_id']);
$encrypted = encryptData($log);

$adminId = null;

$stmt = $conn->prepare("INSERT INTO navigation_logs (admin_id, log_data) VALUES (?, ?)");
$stmt->bind_param("is", $adminId, $encrypted);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Log stored successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to store log.']);
}
