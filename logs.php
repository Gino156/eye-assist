<?php
require 'includes/session.php';
require 'config.php';
require 'includes/functions.php';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$whereClause = "";
if (!empty($search)) {
    $escapedSearch = $conn->real_escape_string($search);
    $whereClause = "WHERE log_data LIKE '%$escapedSearch%'";
}

$totalResult = $conn->query("SELECT COUNT(*) as total FROM navigation_logs $whereClause");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

$result = $conn->query("SELECT * FROM navigation_logs $whereClause ORDER BY created_at DESC LIMIT $start, $limit");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Logs - Eye Assist</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #dbe6f6, #c5796d);
            min-height: 100vh;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
        }

        .log-entry {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 5px solid #007BFF;
        }

        .log-entry strong {
            display: block;
            margin-bottom: 0.3rem;
            color: #333;
        }

        .log-entry small {
            display: block;
            color: #777;
            margin-top: 0.5rem;
        }

        .back-link,
        .export-link {
            display: inline-block;
            margin-top: 1rem;
            text-decoration: none;
            color: #007BFF;
            font-weight: bold;
            margin-right: 1rem;
        }

        .search-form {
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        .search-form input[type="text"] {
            padding: 0.5rem;
            width: 70%;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .search-form button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            background-color: #007BFF;
            color: white;
            cursor: pointer;
        }

        .pagination {
            margin-top: 1.5rem;
            text-align: center;
        }

        .pagination a {
            margin: 0 5px;
            text-decoration: none;
            color: #007BFF;
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
            }

            .search-form {
                flex-direction: column;
            }

            .search-form input {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>📋 Navigation Logs (Decrypted)</h2>
        <div class="search-form">
            <form method="get">
                <input type="text" name="search" placeholder="Search logs..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
            </form>
            <a class="export-link" href="export.php?format=csv">⬇ Export CSV</a>
        </div>
        <a class="back-link" href="dashboard.php">⬅ Back to Dashboard</a>

        <?php while ($row = $result->fetch_assoc()):
            $decrypted = decryptData($row['log_data']);
        ?>
            <div class="log-entry">
                <strong>🕒 <?= htmlspecialchars($row['created_at']) ?></strong>
                <strong>🔐 Encrypted:</strong> <?= htmlspecialchars($row['log_data']) ?>
                <strong>🔓 Decrypted:</strong> <?= htmlspecialchars($decrypted) ?>
            </div>
        <?php endwhile; ?>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">[<?= $i ?>]</a>
            <?php endfor; ?>
        </div>
    </div>
</body>

</html>