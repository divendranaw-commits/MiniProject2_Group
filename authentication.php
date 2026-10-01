<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if (isset($required_role) && $_SESSION['role'] !== $required_role) {
    die("Access denied.");
}