<?php
// Inicia la sesión y protege páginas para usuarios autenticados.
session_start();

if (!isset($_SESSION['user'])) {
    // Si no hay sesión, redirige al login conservando la página solicitada.
    header("Location: inicio.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}