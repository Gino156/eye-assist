<?php require 'includes/session.php'; ?>
<?php
require 'config.php';
require 'includes/functions.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $log = $_POST['log_data'];
    $encrypted = encryptData($log);
    $adminId = $_SESSION['admin_id'];

    $stmt = $conn->prepare("INSERT INTO navigation_logs (admin_id, log_data) VALUES (?, ?)");
    $stmt->bind_param("is", $adminId, $encrypted);
    $stmt->execute();
    $message = "✅ Log saved successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Eye Assist</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #c9d6ff, #e2e2e2);
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        h1 {
            text-align: center;
            margin-bottom: 1rem;
            color: #333;
        }

        h3 {
            margin-top: 2rem;
            margin-bottom: 0.5rem;
            color: #444;
        }

        textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
            resize: vertical;
            margin-bottom: 1rem;
        }

        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            margin-top: 1rem;
            text-align: center;
            color: green;
        }

        .actions {
            margin-top: 1.5rem;
            display: flex;
            justify-content: space-between;
        }

        .actions a {
            text-decoration: none;
            color: #007BFF;
            font-weight: bold;
        }

        .actions a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
            }

            textarea,
            button {
                font-size: 0.95rem;
            }

            .actions {
                flex-direction: column;
                gap: 1rem;
                align-items: center;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Welcome Admin!</h1>

        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <h3>🧾 Simulate Navigation Log</h3>
        <form method="post">
            <textarea name="log_data" rows="4" placeholder="Enter user log or location..." required></textarea>
            <button type="submit">📝 Save Log</button>
        </form>

        <div class="actions">
            <a href="logs.php">🔍 View All Logs</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>
</body>

</html>