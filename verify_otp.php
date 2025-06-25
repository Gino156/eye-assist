<?php
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    $userOtp = trim($_POST['otp']);
    if (isset($_SESSION['otp']) && $userOtp == $_SESSION['otp']) {
        $_SESSION['otp_verified'] = true; // Mark as verified
        $message = "✅ OTP Verified Successfully!";
        // You can redirect or show a button to go to the dashboard
    } else {
        $message = "❌ Invalid OTP!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Verify OTP - Eye Assist</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e3ffe7, #d9e7ff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
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
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            font-weight: bold;
            margin-top: 1rem;
            color: #dc3545;
        }

        .success {
            color: #28a745;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Step 2: Enter OTP</h2>
        <form method="POST">
            <input type="text" name="otp" placeholder="Enter OTP" required />
            <button type="submit">Verify</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, '✅') === 0 ? 'success' : '' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>