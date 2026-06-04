<?php
// Requiere sesión de administrador para editar viviendas.
require_once "php/admin_validacion.php";
// Conexión a la base de datos para leer la propiedad a editar.
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

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: tabla.php");
    exit();
}

$sql = "SELECT v.*, vt.nombre AS tipo_nombre, vz.nombre AS zona_nombre FROM viviendas v "
    . "LEFT JOIN viviendas_tipos vt ON v.tipo_id = vt.id "
    . "LEFT JOIN viviendas_zonas vz ON v.zona_id = vz.id "
    . "WHERE v.id = $id";
$result = mysqli_query($conexion, $sql);
$vivienda = mysqli_fetch_assoc($result);

if (!$vivienda) {
    header("Location: tabla.php");
    exit();
}

if (empty($vivienda['ndormitorios']) && isset($vivienda['dormitorios'])) {
    $vivienda['ndormitorios'] = $vivienda['dormitorios'];
}



$galleryFile = 'fotos/galeria_' . $vivienda['id'] . '.json';
$galeria = [];
if (file_exists($galleryFile)) {
    $galeria = json_decode(file_get_contents($galleryFile), true);
    if (!is_array($galeria)) {
        $galeria = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Vivienda | Inmobiliaria AURA</title>
    
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
                    <h2 class="text-center text-primary font-weight-bold mb-4">Editar Vivienda #<?= $vivienda['id'] ?></h2>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">Error al actualizar la vivienda. <?php if (!empty($_GET['sqlerr'])) echo htmlspecialchars(urldecode($_GET['sqlerr'])); ?></div>
                    <?php endif; ?>
                    
                    <!-- Formulario para actualizar los datos de la vivienda seleccionada -->
                    <form method="POST" action="actualizar.php" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $vivienda['id']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="tipo_list" class="font-weight-bold">Tipo</label>
                                <select id="tipo_list" class="form-control mb-2" size="5">
                                    <option value="">-- Seleccionar tipo --</option>
                                    <?php foreach ($tipos as $t): ?>
                                        <option value="<?= htmlspecialchars($t) ?>" <?= htmlspecialchars($vivienda['tipo_nombre']) === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                                    <?php endforeach; ?>
                                    <option value="__nuevo">Ingresar otro tipo...</option>
                                </select>
                                <input id="tipo" name="tipo" class="form-control" placeholder="Escribe o selecciona un tipo..." type="text" value="<?= htmlspecialchars($vivienda['tipo_nombre'] ?? ''); ?>" required>
                                <small class="form-text text-muted">Selecciona uno de la lista o escribe uno nuevo.</small>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="zona_list" class="font-weight-bold">Zona</label>
                                <select id="zona_list" class="form-control mb-2" size="5">
                                    <option value="">-- Seleccionar zona --</option>
                                    <?php foreach ($zonas as $z): ?>
                                        <option value="<?= htmlspecialchars($z) ?>" <?= htmlspecialchars($vivienda['zona_nombre']) === $z ? 'selected' : '' ?>><?= htmlspecialchars($z) ?></option>
                                    <?php endforeach; ?>
                                    <option value="__nuevo">Ingresar otra zona...</option>
                                </select>
                                <input id="zona" name="zona" class="form-control" placeholder="Escribe o selecciona una zona..." type="text" value="<?= htmlspecialchars($vivienda['zona_nombre'] ?? ''); ?>" required>
                                <small class="form-text text-muted">Selecciona uno de la lista o escribe uno nuevo.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 form-group mb-3">
                                <label for="direccion" class="font-weight-bold">Dirección</label>
                                <input id="direccion" name="direccion" class="form-control" type="text" value="<?= htmlspecialchars($vivienda['direccion']); ?>" required>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="ndormitorios" class="font-weight-bold">Dormitorios</label>
                                <input id="ndormitorios" name="ndormitorios" class="form-control" type="number" min="0" value="<?= htmlspecialchars($vivienda['ndormitorios']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="precio" class="font-weight-bold">Precio ($)</label>
                                <input id="precio" name="precio" class="form-control" type="text" value="<?= htmlspecialchars($vivienda['precio']); ?>" required>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="tamano" class="font-weight-bold">Tamaño (m²)</label>
                                <input id="tamano" name="tamano" class="form-control" type="text" value="<?= htmlspecialchars($vivienda['tamano']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="operacion" class="font-weight-bold">Operación</label>
                                <select id="operacion" name="operacion" class="form-control" required>
                                    <option value="venta" <?= $vivienda['operacion'] === 'venta' ? 'selected' : '' ?>>Venta</option>
                                    <option value="alquiler" <?= $vivienda['operacion'] === 'alquiler' ? 'selected' : '' ?>>Alquiler</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="estado" class="font-weight-bold">Estado</label>
                                <select id="estado" name="estado" class="form-control" required>
                                    <option value="disponible" <?= $vivienda['estado'] === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                                    <option value="reservada" <?= $vivienda['estado'] === 'reservada' ? 'selected' : '' ?>>Reservada</option>
                                    <option value="vendida" <?= $vivienda['estado'] === 'vendida' ? 'selected' : '' ?>>Vendida</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-4">
                                <label for="fecha_reserva" class="font-weight-bold">Fecha de reserva</label>
                                <input id="fecha_reserva" name="fecha_reserva" class="form-control" type="date" value="<?= htmlspecialchars($vivienda['fecha_reserva']); ?>">
                                <small class="form-text text-muted">Solo se usa cuando el estado es "reservada".</small>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="extras" class="font-weight-bold">Extras</label>
                            <input id="extras" name="extras" class="form-control" type="text" value="<?= htmlspecialchars($vivienda['extras']); ?>">
                        </div>

                        <?php if (!empty($galeria) || !empty($vivienda['foto'])): ?>
                        <div class="card bg-light border-0 mb-4 p-3">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold d-block mb-3">Elegí la foto principal</label>
                                <?php if (!empty($vivienda['foto']) && file_exists('fotos/' . $vivienda['foto'])): ?>
                                <div class="custom-control custom-radio mb-3">
                                    <input type="radio" id="principal_current" name="principal" value="current" class="custom-control-input" checked>
                                    <label class="custom-control-label d-flex align-items-center" for="principal_current">
                                        <img src="fotos/<?= htmlspecialchars($vivienda['foto']); ?>" class="img-thumbnail mr-3" style="width: 100px; height: 72px; object-fit: cover;" alt="Foto actual">
                                        Foto actual
                                    </label>
                                </div>
                                <?php endif; ?>
                                <?php foreach ($galeria as $index => $archivo): ?>
                                <?php if (!empty($archivo) && file_exists('fotos/' . $archivo)): ?>
                                <div class="custom-control custom-radio mb-3">
                                    <input type="radio" id="principal_<?= $index ?>" name="principal" value="<?= htmlspecialchars($archivo); ?>" class="custom-control-input">
                                    <label class="custom-control-label d-flex align-items-center" for="principal_<?= $index ?>">
                                        <img src="fotos/<?= htmlspecialchars($archivo); ?>" class="img-thumbnail mr-3" style="width: 100px; height: 72px; object-fit: cover;" alt="Foto de galería">
                                        <?= htmlspecialchars($archivo); ?>
                                    </label>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="card bg-light border-0 mb-4 p-3">
                            <div class="form-group mb-0">
                                <label for="galeria" class="font-weight-bold">Agregar fotos a la galería</label>
                                <p class="text-muted small mb-2">Selecciona varias imágenes para mostrar en la galería de la vivienda.</p>
                                <input id="galeria" name="galeria[]" class="form-control-file" type="file" accept="image/*" multiple>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="observaciones" class="font-weight-bold">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" class="form-control" rows="3"><?= htmlspecialchars($vivienda['observaciones']); ?></textarea>
                        </div>
                        
                        <hr class="mb-4">

                        <div class="d-flex justify-content-between">
                            <a href="tabla.php" class="btn btn-outline-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-5 font-weight-bold">Actualizar vivienda</button>
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