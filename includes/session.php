<?php
session_start();
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role'], ['superadmin', 'user'])) {
    header("Location: login.php");
    exit;
}
