<?php
// =========================================
// proteger_pagina.php
// - Incluído no topo das páginas que exigem login
// - Verifica se existe $_SESSION['usuario']
// - Se não existir, redireciona para login.php
// =========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario']['id'])) {
    header('Location: /backend/login.php');
    exit;
}
