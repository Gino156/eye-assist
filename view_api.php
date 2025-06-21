<?php
require 'includes/session.php';
require 'config.php';
require 'includes/functions.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit("Missing ID.");
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT log_data FROM navigation_logs WHERE id = $id");

if ($row = $result->fetch_assoc()) {
    echo htmlspecialchars(decryptData($row['log_data']));
} else {
    echo "No data found.";
}
