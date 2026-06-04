<?php
// IMPORTANTE: session_start() debe ir en la primera línea, antes de cualquier HTML
session_start();
// Iniciamos sesión para manejar el estado del usuario y su rol.
// Esto permite mostrar diferentes enlaces según el usuario esté logueado o no.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inmobiliaria AURA | Tu próximo hogar</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<!-- Navegación superior con marca y alternador de tema -->
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
    <header class="jumbotron bg-light p-5 rounded shadow-sm text-center">
        <h1 class="display-4">Bienvenido a Inmobiliaria AURA</h1>
        <hr class="my-4 w-50 mx-auto">
        <p class="lead text-muted">Con más de 10 años de experiencia en el mercado local, combinamos tradición y tecnología para ofrecer viviendas únicas.</p>
        
        <div class="mt-4 d-flex justify-content-center flex-wrap gap-2">
            <a href="tabla.php" class="btn btn-primary btn-lg m-1">Ver viviendas</a>
            <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'agente'], true)): ?>
                <!-- Administradores y agentes pueden acceder a la gestión/información de usuarios -->
                <a href="gestion_usuarios.php" class="btn btn-warning btn-lg m-1">
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        Gestión de usuarios
                    <?php else: ?>
                        Información de clientes
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php
            // Verificamos si existe la sesión del usuario
            // Cambiá 'usuario' por el nombre de la variable que uses en tu login.php
            if (isset($_SESSION['user'])): 
            ?>
                <a href="php/salir.php" class="btn btn-danger btn-lg m-1">Cerrar sesión</a>
            <?php else: ?>
                <a href="inicio.php" class="btn btn-outline-dark btn-lg m-1">Iniciar sesión</a>
                <a href="registro.php" class="btn btn-outline-dark btn-lg m-1">Registrarse</a>
            <?php endif; ?>
        </div>
    </header>

    <section class="row mb-5 mt-5">
        <div class="col-md-7">
            <h2>Nuestra historia</h2>
            <p>Fundada en 2014 por un equipo de agentes inmobiliarios y diseñadores, AURA nació con el objetivo de acercar viviendas de calidad a familias y profesionales en crecimiento.</p>
            <p>Desde nuestros primeros pasos, trabajamos ofreciendo atención personalizada, selección rigurosa de propiedades y una presencia online clara.</p>
        </div>
        <div class="col-md-5 mt-4 mt-md-0">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body p-4">
                    <h5 class="card-title text-primary font-weight-bold mb-4">Lo que dicen nuestros clientes</h5>
                    <blockquote class="blockquote font-italic mb-3">
                        <p class="mb-0 small">"Encontramos la casa perfecta gracias a AURA. El proceso fue simple y rápido."</p>
                    </blockquote>
                    <blockquote class="blockquote font-italic mb-0">
                        <p class="mb-0 small">"Excelente atención y plataforma fácil de usar. Muy recomendados."</p>
                    </blockquote>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-5 bg-light p-4 rounded shadow-sm">
        <h2>Nuestra experiencia</h2>
        <ul class="list-unstyled mt-3">
            <li class="mb-2">✔️ <strong>10+ años</strong> en el mercado inmobiliario.</li>
            <li class="mb-2">✔️ Más de <strong>300 viviendas</strong> gestionadas.</li>
            <li class="mb-2">✔️ Especialistas en ventas, alquileres y administración digital.</li>
        </ul>
    </section>

    <footer class="text-center border-top pt-4 pb-4 mt-5 text-muted">
        <p class="mb-0">&copy; 2026 Inmobiliaria AURA. Confianza, experiencia e innovación en cada propiedad.</p>
    </footer>
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