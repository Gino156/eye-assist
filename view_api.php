<?php
require 'includes/session.php';
require 'config.php';
require 'includes/functions.php';
require 'env.php'; // Make sure this file defines $API_TOKEN

$validTokens = [$API_TOKEN];

if (!isset($_GET['token']) || !in_array($_GET['token'], $validTokens)) {
    http_response_code(403);
    exit("Unauthorized access.");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    exit("Missing or invalid log ID.");
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT log_data FROM navigation_logs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $decrypted = decryptData($row['log_data']);
    echo htmlspecialchars($decrypted);
} else {
    http_response_code(404);
    echo "No data found.";
}

$stmt->close();
$conn->close();
