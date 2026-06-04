<?php 

// Inicia sesión y valida credenciales de usuario.
	session_start();
	require_once "conexion.php";

	$conexion=conexion();

	$usuario=mysqli_real_escape_string($conexion, $_POST['usuario'] ?? '');
	$pass=sha1($_POST['password'] ?? '');

	// Buscamos al usuario con el nombre y contraseña proporcionados.
	$sql="SELECT * from usuarios where usuario='$usuario' and password='$pass'";
	$result=mysqli_query($conexion,$sql);

	if(mysqli_num_rows($result) > 0){
		$row = mysqli_fetch_assoc($result);
		$_SESSION['user']=$usuario;
		// Guardamos el rol del usuario en sesión.
		if (isset($row['rol']) && trim($row['rol']) !== '') {
			$_SESSION['role'] = $row['rol'];
		} else if ($row['usuario'] === 'admin') {
			$_SESSION['role'] = 'admin';
		} else {
			$_SESSION['role'] = 'user';
		}
		// Redirigimos a la página de origen después de iniciar sesión.
		$referer = $_SESSION['login_referer'] ?? '../index.php';
		unset($_SESSION['login_referer']);
		header("Location: $referer");
		exit();
	}else{
		// Credenciales incorrectas, volvemos al login con error.
		header("Location: ../inicio.php?error=1");
		exit();
	}



 ?>