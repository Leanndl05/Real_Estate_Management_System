<?php
// Script que crea un nuevo usuario en la base de datos.
require_once "conexion.php";
$conexion = conexion();
ensureUserColumns($conexion);

$nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
$apellido = mysqli_real_escape_string($conexion, $_POST['apellido'] ?? '');
$usuario = mysqli_real_escape_string($conexion, $_POST['usuario'] ?? '');
$dni = mysqli_real_escape_string($conexion, $_POST['dni'] ?? '');
$email = mysqli_real_escape_string($conexion, $_POST['email'] ?? '');
$telefono = mysqli_real_escape_string($conexion, $_POST['telefono'] ?? '');
$password = sha1($_POST['password'] ?? '');

if (empty($nombre) || empty($apellido) || empty($usuario) || empty($dni) || empty($email) || empty($telefono) || empty($_POST['password'] ?? '')) {
    header("Location: ../registro.php?error=1");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[0-9+\s\-]{7,20}$/', $telefono)) {
    header("Location: ../registro.php?error=2");
    exit();
}

if (buscaRepetido($usuario, $conexion) || buscaDNIRepetido($dni, $conexion)) {
    header("Location: ../registro.php?error=1");
    exit();
}

$hasRoleColumn = mysqli_num_rows(mysqli_query($conexion, "SHOW COLUMNS FROM usuarios LIKE 'rol'")) > 0;
if ($hasRoleColumn) {
    $sql = "INSERT INTO usuarios (nombre, apellido, usuario, dni, email, telefono, password, rol) VALUES ('$nombre','$apellido','$usuario','$dni','$email','$telefono','$password','user')";
} else {
    $sql = "INSERT INTO usuarios (nombre, apellido, usuario, dni, email, telefono, password) VALUES ('$nombre','$apellido','$usuario','$dni','$email','$telefono','$password')";
}
mysqli_query($conexion, $sql);
$original_referer = filter_var($_POST['original_referer'] ?? '../index.php', FILTER_SANITIZE_URL);
header("Location: ../inicio.php?registered=1&original_referer=" . urlencode($original_referer));
exit();

function buscaRepetido($user, $conexion) {
    $sql = "SELECT * FROM usuarios WHERE usuario='$user'";
    $result = mysqli_query($conexion, $sql);
    return mysqli_num_rows($result) > 0 ? 1 : 0;
}

function buscaDNIRepetido($dni, $conexion) {
    $sql = "SELECT * FROM usuarios WHERE dni='$dni'";
    $result = mysqli_query($conexion, $sql);
    return mysqli_num_rows($result) > 0 ? 1 : 0;
}

function ensureUserColumns($conexion) {
    $result = mysqli_query($conexion, "SHOW COLUMNS FROM usuarios");
    if (!$result) {
        return;
    }

    $existing = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $existing[$row['Field']] = true;
    }

    if (empty($existing['dni'])) {
        mysqli_query($conexion, "ALTER TABLE usuarios ADD COLUMN dni VARCHAR(50) NULL AFTER usuario");
    }
    if (empty($existing['email'])) {
        mysqli_query($conexion, "ALTER TABLE usuarios ADD COLUMN email VARCHAR(255) NULL AFTER dni");
    }
    if (empty($existing['telefono'])) {
        mysqli_query($conexion, "ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(30) NULL AFTER email");
    }
    if (empty($existing['fecha_registro'])) {
        mysqli_query($conexion, "ALTER TABLE usuarios ADD COLUMN fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER telefono");
    }

    $indexCheck = mysqli_query($conexion, "SHOW INDEX FROM usuarios WHERE Column_name='dni' AND Non_unique=0");
    if ($indexCheck && mysqli_num_rows($indexCheck) === 0) {
        mysqli_query($conexion, "CREATE UNIQUE INDEX idx_usuarios_dni ON usuarios (dni)");
    }
}
?>