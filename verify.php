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
<html>

<head>
    <title>OTP Verification</title>
</head>

<body>
    <h2>Enter OTP</h2>
    <form method="post">
        <input type="text" name="otp" placeholder="Enter OTP" required><br>
        <button type="submit">Verify</button>
    </form>
    <p><?= $message ?></p>
</body>

</html>