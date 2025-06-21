<?php
require 'config.php';
session_start();

if (!isset($_SESSION['otp'])) {
    header("Location: index.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered = $_POST['otp'];

    if ($entered == $_SESSION['otp']) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $_SESSION['temp_admin'];
        unset($_SESSION['otp'], $_SESSION['temp_admin']);
        header("Location: dashboard.php");
        exit;
    } else {
        $message = "Invalid OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #dbe6f6, #c5796d);
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

        input[type="text"] {
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
            color: #e63946;
            margin-top: 1rem;
            font-weight: 500;
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>OTP Verification</h2>
        <form method="post">
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit">Verify</button>
        </form>
        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
    </div>
</body>

</html>