<?php
// Validación de administrador: solo un admin puede acceder a esta página.
require_once "php/admin_validacion.php";
require_once "php/conexion.php";
$conexion = conexion();

// Obtener tipos y zonas existentes para el datalist
$tipos = [];
$zonas = [];
$r = mysqli_query($conexion, "SELECT nombre FROM viviendas_tipos ORDER BY nombre");
if ($r) {
    $rows = mysqli_fetch_all($r, MYSQLI_ASSOC);
    foreach ($rows as $rr) $tipos[] = $rr['nombre'];
}
$r2 = mysqli_query($conexion, "SELECT nombre FROM viviendas_zonas ORDER BY nombre");
if ($r2) {
    $rows2 = mysqli_fetch_all($r2, MYSQLI_ASSOC);
    foreach ($rows2 as $rr2) $zonas[] = $rr2['nombre'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Vivienda | Inmobiliaria AURA</title>
    
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

<div class="container mt-5 pt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <h2 class="text-center text-primary font-weight-bold mb-4">Agregar nueva vivienda</h2>
                    
                    <!-- Formulario para cargar una nueva vivienda en la base de datos -->
                    <form method="POST" action="guardar.php" enctype="multipart/form-data">
                        
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="tipo_list" class="font-weight-bold">Tipo</label>
                                <select id="tipo_list" class="form-control mb-2" size="5">
                                    <option value="">-- Seleccionar tipo --</option>
                                    <?php foreach ($tipos as $t): ?>
                                        <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                                    <?php endforeach; ?>
                                    <option value="__nuevo">Ingresar otro tipo...</option>
                                </select>
                                <input id="tipo" name="tipo" class="form-control" placeholder="Escribe o selecciona un tipo..." type="text" required>
                                <small class="form-text text-muted">Selecciona uno de la lista o escribe uno nuevo.</small>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="zona_list" class="font-weight-bold">Zona</label>
                                <select id="zona_list" class="form-control mb-2" size="5">
                                    <option value="">-- Seleccionar zona --</option>
                                    <?php foreach ($zonas as $z): ?>
                                        <option value="<?= htmlspecialchars($z) ?>"><?= htmlspecialchars($z) ?></option>
                                    <?php endforeach; ?>
                                    <option value="__nuevo">Ingresar otra zona...</option>
                                </select>
                                <input id="zona" name="zona" class="form-control" placeholder="Escribe o selecciona una zona..." type="text" required>
                                <small class="form-text text-muted">Selecciona uno de la lista o escribe uno nuevo.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 form-group mb-3">
                                <label for="direccion" class="font-weight-bold">Dirección</label>
                                <input id="direccion" name="direccion" class="form-control" placeholder="Dirección completa" type="text" required>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="ndormitorios" class="font-weight-bold">Dormitorios</label>
                                <input id="ndormitorios" name="ndormitorios" class="form-control" type="number" min="0" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="precio" class="font-weight-bold">Precio ($)</label>
                                <input id="precio" name="precio" class="form-control" placeholder="Ej: 150000" type="text" required>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="tamano" class="font-weight-bold">Tamaño (m²)</label>
                                <input id="tamano" name="tamano" class="form-control" placeholder="Ej: 120" type="text" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="operacion" class="font-weight-bold">Operación</label>
                                <select id="operacion" name="operacion" class="form-control" required>
                                    <option value="venta">Venta</option>
                                    <option value="alquiler">Alquiler</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="estado" class="font-weight-bold">Estado</label>
                                <select id="estado" name="estado" class="form-control" required>
                                    <option value="disponible">Disponible</option>
                                    <option value="reservada">Reservada</option>
                                    <option value="vendida">Vendida</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-4">
                                <label for="fecha_reserva" class="font-weight-bold">Fecha de reserva</label>
                                <input id="fecha_reserva" name="fecha_reserva" class="form-control" type="date">
                                <small class="form-text text-muted">Solo se usa cuando el estado es "reservada".</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 form-group mb-4">
                                <label for="galeria" class="font-weight-bold">Fotos de la galería</label>
                                <input id="galeria" name="galeria[]" class="form-control-file mt-1" type="file" accept="image/*" multiple>
                                <small class="form-text text-muted">Subí varias fotos y la primera imagen servirá como foto principal.</small>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="observaciones" class="font-weight-bold">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" class="form-control" placeholder="Detalles adicionales de la propiedad..." rows="3"></textarea>
                        </div>
                        
                        <hr class="mb-4">

                        <div class="d-flex justify-content-between">
                            <a href="tabla.php" class="btn btn-outline-secondary px-4">Volver</a>
                            <button type="submit" class="btn btn-primary px-5 font-weight-bold">Guardar vivienda</button>
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

    var tipoList = document.getElementById('tipo_list');
    var tipoInput = document.getElementById('tipo');
    if (tipoList && tipoInput) {
        tipoList.addEventListener('change', function() {
            if (this.value === '__nuevo') {
                tipoInput.value = '';
                tipoInput.focus();
            } else {
                tipoInput.value = this.value;
            }
        });
    }
    var zonaList = document.getElementById('zona_list');
    var zonaInput = document.getElementById('zona');
    if (zonaList && zonaInput) {
        zonaList.addEventListener('change', function() {
            if (this.value === '__nuevo') {
                zonaInput.value = '';
                zonaInput.focus();
            } else {
                zonaInput.value = this.value;
            }
        });
    }
});
</script>
</body>
</html>