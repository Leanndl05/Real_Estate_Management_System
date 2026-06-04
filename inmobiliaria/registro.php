<!DOCTYPE html>
<?php
// Guardamos la página de referencia para poder volver después de registrarse.
$original_referer = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php');
?>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | Inmobiliaria AURA</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    
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

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5"> 
            
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="text-center text-primary font-weight-bold mb-4">Registro de Usuario</h2>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php if ($_GET['error'] === '2'): ?>
                                Verifica que todos los datos estén completos y el email tenga formato correcto.
                            <?php else: ?>
                                El usuario o DNI ya existe, o falta información obligatoria.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <form id="frmRegistro" method="POST" action="php/registro.php">
                        <!-- Formulario de registro que envía los datos al script php/registro.php -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nombre" class="font-weight-bold">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="apellido" class="font-weight-bold">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="usuario" class="font-weight-bold">Usuario</label>
                            <input type="text" class="form-control" id="usuario" name="usuario" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="dni" class="font-weight-bold">DNI</label>
                            <input type="text" class="form-control" id="dni" name="dni" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="email" class="font-weight-bold">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="form-group mb-4">
                            <label for="telefono" class="font-weight-bold">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" required>
                        </div>

                        <div class="form-group mb-4">
                            <label for="password" class="font-weight-bold">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <input type="hidden" name="original_referer" value="<?php echo htmlspecialchars($original_referer); ?>">
                        <!-- Hidden que conserva la página de origen para redirigir después del registro -->
                        <button type="submit" class="btn btn-primary btn-block mb-3">Crear cuenta</button>
                        <div class="d-flex justify-content-between">
                            <a href="inicio.php" class="btn btn-outline-dark w-100 mr-2">Iniciar sesión</a>
                            <a href="index.php" class="btn btn-outline-dark w-100 ml-2">Volver</a>
                        </div>
                    </form>

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