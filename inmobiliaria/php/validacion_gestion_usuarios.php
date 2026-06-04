<?php
// Inicia sesión y valida que el usuario sea administrador o agente.
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'agente'], true)) {
    // Si no es admin ni agente, redirige al inicio.
    header("Location: ../inicio.php");
    exit();
}
?>
