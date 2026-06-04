<?php 

// Cierra la sesión del usuario y redirige al inicio.
	session_start();

	unset($_SESSION['user']);
	session_destroy();
	header("location:../index.php");

 ?>