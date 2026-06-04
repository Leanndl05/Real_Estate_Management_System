<?php
// Esta página es para administradores y agentes (con permisos limitados).
require_once "php/validacion_gestion_usuarios.php";
// Conexión a la base de datos para gestionar usuarios.
require_once "php/conexion.php";
$conexion = conexion();

// Procesar cambios de rol o eliminación de usuario enviados por el formulario.
// IMPORTANTE: Solo los administradores pueden hacer estos cambios.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario'] ?? '');

    // Validar que solo admin puede cambiar roles o eliminar usuarios
    if ($_SESSION['role'] !== 'admin') {
        // Los agentes no pueden hacer cambios, ignorar el POST silenciosamente
        $_SERVER['REQUEST_METHOD'] = 'GET';
    } else {
        if (isset($_POST['cambiar_rol'])) {
            $nuevo_rol = mysqli_real_escape_string($conexion, $_POST['rol'] ?? '');
            $roles_validos = ['admin', 'agente', 'user'];
            if (!empty($usuario) && in_array($nuevo_rol, $roles_validos, true)) {
                $sql_update = "UPDATE usuarios SET rol = '$nuevo_rol' WHERE usuario = '$usuario'";
                mysqli_query($conexion, $sql_update);
            }
        }

        if (isset($_POST['eliminar_usuario']) && !empty($usuario) && $usuario !== $_SESSION['user']) {
            $sql_delete = "DELETE FROM usuarios WHERE usuario = '$usuario'";
            mysqli_query($conexion, $sql_delete);
        }
    }
}

// Obtener usuarios para mostrarlos en la tabla.
// Si es agente, mostrar solo usuarios con rol 'user' (clientes).
// Si es admin, mostrar todos los usuarios.
$sql = "SELECT nombre, apellido, usuario, dni, email, telefono, rol FROM usuarios";
if ($_SESSION['role'] === 'agente') {
    $sql .= " WHERE rol = 'user'";
}
$sql .= " ORDER BY apellido, nombre";
$result = mysqli_query($conexion, $sql);
$usuarios = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios | Inmobiliaria AURA</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" crossorigin="anonymous">
    
    <link rel="stylesheet" href="estilo.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-custom px-4 py-3">
    <a class="navbar-brand" href="index.php">AURA</a>
    <div class="ml-auto theme-switcher d-flex align-items-center">
        <span class="theme-icon" aria-hidden="true">☀️</span>
        <label class="switch mb-0 mx-2">
            <input type="checkbox" id="themeToggle" aria-label="Alternar modo claro y oscuro">
            <span class="slider"></span>
        </label>
        <span class="theme-icon" aria-hidden="true">🌙</span>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h2 class="mb-0">
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            Gestión de Usuarios
                        <?php else: ?>
                            Información de Clientes
                        <?php endif; ?>
                    </h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nombre completo</th>
                                    <th>Usuario</th>
                                    <th>Rol actual</th>
                                    <th>Información adicional</th>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                        <th>Eliminar</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?= htmlspecialchars($usuario['apellido'] . ', ' . $usuario['nombre']); ?></td>
                                    <td><?= htmlspecialchars($usuario['usuario']); ?></td>
                                    <td>
                                        <?php if ($_SESSION['role'] === 'admin'): ?>
                                            <form method="post" class="d-inline" onsubmit="return confirm('¿Confirma el cambio de rol para este usuario?');">
                                                <input type="hidden" name="usuario" value="<?= htmlspecialchars($usuario['usuario']); ?>">
                                                <div class="form-row align-items-center">
                                                    <div class="col-auto p-0">
                                                        <select name="rol" class="form-control form-control-sm" style="width: 120px;">
                                                            <option value="user" <?= ($usuario['rol'] ?? 'user') === 'user' ? 'selected' : '' ?>>User</option>
                                                            <option value="agente" <?= ($usuario['rol'] ?? 'user') === 'agente' ? 'selected' : '' ?>>Agente</option>
                                                            <option value="admin" <?= ($usuario['rol'] ?? 'user') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-auto pl-2">
                                                        <button type="submit" name="cambiar_rol" class="btn btn-primary btn-sm">Cambiar</button>
                                                    </div>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?= htmlspecialchars($usuario['rol'] ?? 'user'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="usuario_info.php?usuario=<?= urlencode($usuario['usuario']); ?>" class="btn btn-info btn-sm">Información adicional</a>
                                    </td>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                        <td>
                                            <?php if ($usuario['usuario'] !== $_SESSION['user']): ?>
                                                <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar este usuario?');">
                                                    <input type="hidden" name="usuario" value="<?= htmlspecialchars($usuario['usuario']); ?>">
                                                    <button type="submit" name="eliminar_usuario" class="btn btn-danger btn-sm">Eliminar</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted small">No se puede eliminar</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="index.php" class="btn btn-outline-secondary">Volver al inicio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var body = document.body;
    var toggle = document.getElementById('themeToggle');
    var savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
        if (toggle) toggle.checked = true;
    }
    if (toggle) {
        toggle.addEventListener('change', function() {
            body.classList.toggle('dark-mode', this.checked);
            localStorage.setItem('theme', this.checked ? 'dark' : 'light');
        });
    }
});
</script>
</body>
</html>