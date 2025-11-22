<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario']['id'])) {
    header('Location: /backend/login.php');
    exit;
}
