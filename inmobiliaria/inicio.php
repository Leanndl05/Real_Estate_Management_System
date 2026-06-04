<!DOCTYPE html>
<?php
session_start();
// Guardamos la URL de origen para redirigir de vuelta después de iniciar sesión.
// Si viene desde otra página, la usamos como destino seguro.
$referer = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : (isset($_GET['original_referer']) ? urldecode($_GET['original_referer']) : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php'));
if (!isset($_SESSION['login_referer'])) {
    $_SESSION['login_referer'] = $referer;
}
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Inmobiliaria AURA</title>
    
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
        <div class="col-md-6 col-lg-4">
            
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="text-center text-primary font-weight-bold mb-4">Iniciar Sesión</h2>
                    
                    <?php if (isset($_GET['error']) && $_GET['error'] === '1'): ?>
                        <div class="alert alert-danger" role="alert">
                            El usuario o la contraseña son incorrectos.
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="php/login.php">
                        <!-- Formulario de inicio de sesión que envía datos al script php/login.php -->
                        <div class="form-group mb-3">
                            <label for="usuario" class="font-weight-bold">Usuario</label>
                            <input type="text" id="usuario" name="usuario" class="form-control" required>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="password" class="font-weight-bold">Contraseña</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block mb-3">Entrar</button>
                        
                        <div class="d-flex justify-content-between">
                            <a href="registro.php?redirect=<?php echo urlencode($referer); ?>" class="btn btn-outline-dark w-100 mr-2">Registrarse</a>
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