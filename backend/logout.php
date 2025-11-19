<?php
// logout.php
// Encerra sessão e redireciona para a home

session_start();
$_SESSION = [];
session_unset();
session_destroy();
header('Location: /index.php');
exit;
