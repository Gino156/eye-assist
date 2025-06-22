<?php
// ✅ IP Whitelist setup
$whitelist = [
    '127.0.0.1', // Localhost
    '::1',       // IPv6 localhost
    '131.226.107.207 ',
];

// Load from .env.php (optional)
if (file_exists(__DIR__ . '/.env.php')) {
    require __DIR__ . '/.env.php';
    if (isset($WHITELIST_IPS) && is_array($WHITELIST_IPS)) {
        $whitelist = array_merge($whitelist, $WHITELIST_IPS);
    }
}

$client_ip = $_SERVER['REMOTE_ADDR'];
if (!in_array($client_ip, $whitelist)) {
    http_response_code(403);
    exit("❌ Access denied from IP: $client_ip");
}

require 'config.php';
require 'includes/functions.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['login_attempts'] >= 3 && strtotime($user['last_attempt']) > strtotime("-5 minutes")) {
            $message = "⛔ Account locked. Try again after 5 minutes.";
        } elseif (password_verify($password, $user['password'])) {
            $conn->query("UPDATE admins SET login_attempts = 0 WHERE id = " . $user['id']);

            $otp = generateOTP();
            $_SESSION['otp'] = $otp;
            $_SESSION['temp_admin'] = $user['id'];

            // Save OTP for demo purpose (remove this in production)
            file_put_contents("otp_display.txt", "OTP: " . $otp);

            header("Location: verify.php");
            exit;
        } else {
            $conn->query("UPDATE admins SET login_attempts = login_attempts + 1, last_attempt = NOW() WHERE id = " . $user['id']);
            $message = "❌ Incorrect password.";
        }
    } else {
        $message = "❌ User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Eye Assist - Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #eef2f3, #8e9eab);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: #ffffff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
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
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            margin-top: 1rem;
            color: #e63946;
            font-weight: 500;
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
            }

            input[type="text"],
            input[type="password"],
            button {
                font-size: 0.95rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Admin Login</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Enter Username" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit">Login</button>
        </form>
        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
    </div>
</body>

</html>