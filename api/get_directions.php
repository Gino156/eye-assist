<?php
require '../config.php';
require '../includes/functions.php';
$config = require '../env.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// 1. Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

// 2. Token validation (device-bound tokens)
$token = $_GET['token'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$deviceStmt = $conn->prepare("SELECT id FROM devices WHERE api_token = ?");
$deviceStmt->bind_param("s", $token);
$deviceStmt->execute();
$deviceResult = $deviceStmt->get_result();

if ($deviceResult->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized. Invalid device token.']);
    exit;
}

$device = $deviceResult->fetch_assoc();
$deviceId = $device['id'];

// 3. Parse input
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['from'], $data['to'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters: from, to']);
    exit;
}

$from = trim($data['from']);
$to = trim($data['to']);

// 4. Validate route
$mapStmt = $conn->prepare("SELECT directions, estimated_time, distance, instructions FROM map_routes WHERE from_location = ? AND to_location = ?");
$mapStmt->bind_param("ss", $from, $to);
$mapStmt->execute();
$mapResult = $mapStmt->get_result();

if ($mapResult->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'No known route between these locations.']);
    exit;
}

$mapRow = $mapResult->fetch_assoc();
$directions = $mapRow['directions'];
$estimatedTime = $mapRow['estimated_time'] ?? null;
$distance = $mapRow['distance'] ?? null;
$instructions = $mapRow['instructions'] ?? null;

// 5. Store navigation history (logs)
$encryptedLog = encryptData("Device $deviceId requested direction from $from to $to");
$adminId = null;

$logStmt = $conn->prepare("INSERT INTO navigation_logs (admin_id, device_id, log_data) VALUES (?, ?, ?)");
$logStmt->bind_param("iis", $adminId, $deviceId, $encryptedLog);
$logStmt->execute();

// 6. Return response
echo json_encode([
    'success' => true,
    'from' => $from,
    'to' => $to,
    'directions' => $directions,
    'estimated_time' => $estimatedTime,
    'distance' => $distance,
    'instructions' => $instructions
]);
