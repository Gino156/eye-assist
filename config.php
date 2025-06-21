<?php
$conn = new mysqli("localhost", "root", "", "eye_assist");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
