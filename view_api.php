<?php
require 'includes/session.php'; // 🔒 Validates active admin session
require 'config.php';
require 'includes/functions.php';

// ✅ Only allow logged-in admins
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit("❌ Unauthorized access.");
}

// ✅ Validate input
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    exit("❌ Missing or invalid log ID.");
}

$id = (int) $_GET['id'];

// 🔍 Fetch log entry by ID
$stmt = $conn->prepare("SELECT log_data FROM navigation_logs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $decrypted = decryptData($row['log_data']); // 🔓 Decrypt log content
    echo htmlspecialchars($decrypted); // 🚫 Prevent XSS
} else {
    http_response_code(404);
    echo "❌ No data found.";
}

$stmt->close();
$conn->close();
