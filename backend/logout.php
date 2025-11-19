<?php
session_start();

// Destrói todos os dados da sessão
session_unset();
session_destroy();

// Redireciona para a página inicial
header('Location: ../index.php');
exit;
