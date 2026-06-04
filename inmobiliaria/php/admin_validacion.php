<?php
// Inicia sesión y valida que el usuario sea administrador.
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Si no es admin, redirige al inicio.
    header("Location: ../inicio.php");
    exit();
}
?>
