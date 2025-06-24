<?php
require 'includes/session.php';
require 'config.php';
require 'includes/functions.php';
$config = require 'env.php'; // ✅ Load API_TOKEN

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$whereClause = "";
if (!empty($search)) {
    $escapedSearch = $conn->real_escape_string($search);
    $whereClause = "WHERE log_data LIKE '%$escapedSearch%'";
}

// ✅ Make sure to use `users` instead of `admins`
$totalResult = $conn->query("SELECT COUNT(*) as total FROM navigation_logs $whereClause");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

$result = $conn->query("SELECT navigation_logs.*, users.username 
    FROM navigation_logs 
    JOIN users ON navigation_logs.admin_id = users.id 
    $whereClause 
    ORDER BY navigation_logs.created_at DESC 
    LIMIT $start, $limit");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Navigation Logs - Eye Assist</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            max-width: 900px;
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
            position: relative;
        }

        .log-entry strong {
            display: block;
            margin-bottom: 0.3rem;
            color: #333;
        }

        .log-entry .actions {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .log-entry .actions button,
        .log-entry .actions a {
            background-color: #28a745;
            color: white;
            padding: 4px 8px;
            font-size: 0.8rem;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .log-entry .actions button.delete-btn {
            background-color: #dc3545;
        }

        .top-actions {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 1rem;
        }

        .top-actions form {
            display: flex;
            gap: 0.5rem;
        }

        .top-actions input[type="text"] {
            padding: 0.5rem;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .top-actions button,
        .export-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            color: white;
            background-color: #007BFF;
        }

        .export-btn.pdf {
            background-color: #6f42c1;
        }

        .export-btn.csv {
            background-color: #17a2b8;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            text-decoration: none;
            color: #007BFF;
            font-weight: bold;
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

        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 2rem;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 20px;
            cursor: pointer;
        }

        #copy-btn {
            background-color: #ffc107;
            color: black;
            margin-top: 1rem;
        }

        @media (max-width: 600px) {
            .top-actions {
                flex-direction: column;
                gap: 1rem;
            }

            .top-actions form {
                flex-direction: column;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>📋 Navigation Logs</h2>
        <a class="back-link" href="dashboard_user.php">⬅ Back to Dashboard</a>

        <div class="top-actions">
            <form method="get">
                <input type="text" name="search" placeholder="Search logs..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
            </form>
            <div>
                <a class="export-btn csv" href="export.php?format=csv&search=<?= urlencode($search) ?>">⬇ Export CSV</a>
                <a class="export-btn pdf" href="export.php?format=pdf&search=<?= urlencode($search) ?>">⬇ Export PDF</a>
            </div>
        </div>

        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="log-entry">
                <strong>🕒 <?= htmlspecialchars($row['created_at']) ?></strong>
                <strong>👤 User:</strong> <?= htmlspecialchars($row['username']) ?><br>
                <strong>🔐 Encrypted:</strong> <?= htmlspecialchars($row['log_data']) ?>
                <div class="actions">
                    <button onclick="showDecrypted('<?= $row['id'] ?>')">👁 View</button>
                    <button class="delete-btn" onclick="confirmDelete(<?= $row['id'] ?>)">🗑 Delete</button>
                </div>
            </div>
        <?php endwhile; ?>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">[<?= $i ?>]</a>
            <?php endfor; ?>
        </div>
    </div>

    <div class="modal" id="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal()">&times;</span>
            <p><strong>🔓 Decrypted:</strong></p>
            <pre id="decryptedText"></pre>
            <button id="copy-btn" onclick="copyText()">📋 Copy</button>
        </div>
    </div>

    <script>
        const apiToken = "<?= $config['API_TOKEN'] ?>";

        function showDecrypted(id) {
            fetch('view_api.php?id=' + id)
                .then(res => res.text())
                .then(data => {
                    document.getElementById('decryptedText').textContent = data;
                    document.getElementById('modal').style.display = 'block';
                    setTimeout(() => hideModal(), 10000);
                });
        }

        function hideModal() {
            document.getElementById('modal').style.display = 'none';
            document.getElementById('decryptedText').textContent = '';
        }

        function copyText() {
            const text = document.getElementById('decryptedText').textContent;
            navigator.clipboard.writeText(text).then(() => alert('Copied to clipboard!'));
        }

        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this log?")) {
                window.location.href = 'delete.php?id=' + id;
            }
        }
    </script>
</body>

</html>