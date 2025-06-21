<?php
require 'config.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if locked
        if ($user['login_attempts'] >= 3 && strtotime($user['last_attempt']) > strtotime("-5 minutes")) {
            $message = "Account locked. Try again later.";
        } elseif (password_verify($password, $user['password'])) {
            // Reset attempts
            $conn->query("UPDATE admins SET login_attempts = 0 WHERE id = " . $user['id']);

            // Generate OTP
            require 'includes/functions.php';
            $otp = generateOTP();
            $_SESSION['otp'] = $otp;
            $_SESSION['temp_admin'] = $user['id'];

            // Simulate OTP display (in real use, send via email/SMS)
            file_put_contents("otp_display.txt", "OTP: " . $otp);

            header("Location: verify.php");
            exit;
        } else {
            // Increase attempt
            $conn->query("UPDATE admins SET login_attempts = login_attempts + 1, last_attempt = NOW() WHERE id = " . $user['id']);
            $message = "Incorrect password.";
        }
    } else {
        $message = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login - Eye Assist</title>
</head>

<body>
    <h2>Admin Login</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <p><?= $message ?></p>
</body>

</html>