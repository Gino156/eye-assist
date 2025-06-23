<?php
require 'includes/session.php';
require 'config.php';
require 'includes/functions.php';

// Ensure role is set
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$isSuperAdmin = $role === 'superadmin';

// Handle create
if (isset($_POST['add_device'])) {
    $name = trim($_POST['device_name']);
    $token = bin2hex(random_bytes(32));
    $stmt = $conn->prepare("INSERT INTO devices (device_name, api_token) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $token);
    $stmt->execute();
    header("Location: devices.php");
    exit;
}

// Handle delete
if (isset($_GET['delete']) && $isSuperAdmin) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM devices WHERE id = $id");
    header("Location: devices.php");
    exit;
}

// Handle toggle
if (isset($_GET['toggle']) && $isSuperAdmin) {
    $id = (int) $_GET['toggle'];
    $conn->query("UPDATE devices SET is_active = NOT is_active WHERE id = $id");
    header("Location: devices.php");
    exit;
}

// Handle export
if (isset($_GET['export'])) {
    if ($_GET['export'] === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="devices.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Device Name', 'API Token', 'Status', 'Created At']);
        $res = $conn->query("SELECT * FROM devices");
        while ($row = $res->fetch_assoc()) {
            fputcsv($out, [$row['id'], $row['device_name'], $row['api_token'], $row['is_active'] ? 'Active' : 'Inactive', $row['created_at']]);
        }
        fclose($out);
        exit;
    } elseif ($_GET['export'] === 'json') {
        header('Content-Type: application/json');
        $res = $conn->query("SELECT * FROM devices");
        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        exit;
    }
}

// Filters and Search
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$where = [];

if ($statusFilter === 'active') $where[] = "is_active = 1";
if ($statusFilter === 'inactive') $where[] = "is_active = 0";
if (!empty($search)) {
    $safe = $conn->real_escape_string($search);
    $where[] = "device_name LIKE '%$safe%'";
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Pagination setup
$limit = 10;
$page = max((int)($_GET['page'] ?? 1), 1);
$offset = ($page - 1) * $limit;

$totalResult = $conn->query("SELECT COUNT(*) as total FROM devices $whereSql");
$total = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);

$result = $conn->query("SELECT * FROM devices $whereSql ORDER BY created_at DESC LIMIT $offset, $limit");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Device Management - Eye Assist</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 2rem;
            background: #f4f4f4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background: white;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        .danger,
        .toggle {
            padding: 4px 8px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .danger {
            background: #dc3545;
        }

        .toggle {
            background: #6c757d;
        }

        .pagination {
            margin-top: 1rem;
            text-align: center;
        }

        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            color: #007BFF;
        }

        .filters,
        form {
            margin-top: 1rem;
        }

        input[type="text"] {
            padding: 6px;
        }

        select {
            padding: 6px;
        }

        button {
            padding: 6px 12px;
        }
    </style>
</head>

<body>

    <h1>📱 Device Management</h1>

    <form method="post">
        <label>Device Name: <input type="text" name="device_name" required></label>
        <button type="submit" name="add_device">➕ Add Device</button>
        <a href="devices.php?export=csv">⬇ CSV</a>
        <a href="devices.php?export=json">⬇ JSON</a>
    </form>

    <div class="filters">
        <form method="get">
            <input type="text" name="search" placeholder="Search device name..." value="<?= htmlspecialchars($search) ?>">
            <select name="status">
                <option value="">All Status</option>
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>✅ Active</option>
                <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>❌ Inactive</option>
            </select>
            <button type="submit">🔍 Filter</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Device Name</th>
                <th>API Token</th>
                <th>Status</th>
                <th>Created</th>
                <?php if ($isSuperAdmin): ?><th>Actions</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['device_name']) ?></td>
                    <td><code><?= htmlspecialchars($row['api_token']) ?></code></td>
                    <td><?= $row['is_active'] ? '✅ Active' : '❌ Inactive' ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <?php if ($isSuperAdmin): ?>
                        <td>
                            <a class="toggle" href="?toggle=<?= $row['id'] ?>">🔄</a>
                            <a class="danger" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this device?')">🗑</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>">[<?= $i ?>]</a>
        <?php endfor; ?>
    </div>

</body>

</html>