<?php
// Se requiere que el usuario esté logueado antes de ver el detalle.
require_once "php/validacion.php";
// Conexión a la base de datos para cargar la vivienda seleccionada.
require_once "php/conexion.php";
$conexion = conexion();

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

$interestFilters = [];
foreach (['tipo','zona','operacion','dormitorios','min_precio','max_precio','solo_disponibles','solo_vendidas','extras'] as $filterKey) {
    if (isset($_GET[$filterKey])) {
        $interestFilters[$filterKey] = $_GET[$filterKey];
    }
}

$contacted = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact'])) {
    $contacted = true;
}

logUserInterest($conexion, $_SESSION['user'], $id, $interestFilters, $contacted);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'agente'])) {
        $nuevo_estado = mysqli_real_escape_string($conexion, $_POST['estado'] ?? 'disponible');
        $estadoValores = ['disponible', 'reservada', 'vendida'];
        if (in_array($nuevo_estado, $estadoValores, true)) {
            $fecha_reserva = '';
            if ($nuevo_estado === 'reservada') {
                $fecha_reserva = mysqli_real_escape_string($conexion, $_POST['fecha_reserva'] ?? '');
                if ($fecha_reserva !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_reserva)) {
                    $fecha_reserva = '';
                }
            }
            $fechaReservaValue = $fecha_reserva !== '' ? "'$fecha_reserva'" : 'NULL';
            $sql_update = "UPDATE viviendas SET estado = '$nuevo_estado', fecha_reserva = $fechaReservaValue WHERE id = $id";
            mysqli_query($conexion, $sql_update);
            // Recargar la vivienda
            $sql = "SELECT v.*, vt.nombre AS tipo_nombre, vz.nombre AS zona_nombre FROM viviendas v "
                . "LEFT JOIN viviendas_tipos vt ON v.tipo_id = vt.id "
                . "LEFT JOIN viviendas_zonas vz ON v.zona_id = vz.id "
                . "WHERE v.id = $id";
            $result = mysqli_query($conexion, $sql);
            $vivienda = mysqli_fetch_assoc($result);
        }
    }
}

$contactMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact'])) {
    $contactMessage = 'Se avisó al propietario que estás interesado, pronto se comunicará contigo.';
}
$galleryFile = 'fotos/galeria_' . $vivienda['id'] . '.json';
$galeria = [];
if (file_exists($galleryFile)) {
    $galeria = json_decode(file_get_contents($galleryFile), true);
    if (!is_array($galeria)) {
        $galeria = [];
    }
}
$imagenes = [];
if (!empty($vivienda['foto']) && file_exists('fotos/' . $vivienda['foto'])) {
    $imagenes[] = $vivienda['foto'];
}
foreach ($galeria as $imagen) {
    if (!empty($imagen) && $imagen !== $vivienda['foto'] && file_exists('fotos/' . $imagen)) {
        $imagenes[] = $imagen;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Vivienda | Inmobiliaria AURA</title>
    
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

<div class="container mt-5 mb-5">
    
    <div class="mb-4">
        <!-- Botón para volver a la lista de propiedades -->
        <a href="tabla.php" class="btn btn-outline-secondary btn-sm text-uppercase font-weight-bold" style="letter-spacing: 1px;">
            &larr; Volver al catálogo
        </a>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm overflow-hidden h-100 bg-white d-flex align-items-center justify-content-center">
                <?php if (!empty($vivienda['foto']) && file_exists('fotos/' . $vivienda['foto'])): ?>
                    <button type="button" class="gallery-open btn p-0 border-0 bg-transparent w-100" data-index="0" style="cursor: pointer;">
                        <img src="fotos/<?= htmlspecialchars($vivienda['foto']); ?>" class="img-fluid w-100 gallery-main-image" style="object-fit: cover; max-height: 600px;" alt="Foto de la vivienda en <?= htmlspecialchars($vivienda['zona_nombre'] ?? ''); ?>">
                    </button>
                <?php else: ?>
                    <div class="text-center p-5 text-muted">
                        <div class="display-1 mb-3">🏠</div>
                        <h5>Sin imagen disponible</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4 p-md-5 d-flex flex-column">
                    
                    <div class="mb-4">
                        <span class="badge badge-primary mb-2 px-3 py-2 text-uppercase" style="letter-spacing: 1px;"><?= htmlspecialchars($vivienda['tipo_nombre'] ?? ''); ?></span>
                        <h2 class="font-weight-bold mb-1"><?= htmlspecialchars($vivienda['direccion']); ?></h2>
                        <h5 class="text-muted font-weight-normal">📍 Zona <?= htmlspecialchars($vivienda['zona_nombre'] ?? ''); ?></h5>
                    </div>

                    <div class="mb-4 pb-3 border-bottom">
                        <h1 class="text-success font-weight-bold mb-0">$<?= htmlspecialchars($vivienda['precio']); ?></h1>
                    </div>

                    <ul class="list-group list-group-flush mb-4 flex-grow-1">
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Dormitorios</span>
                            <span class="font-weight-bold"><?= htmlspecialchars($vivienda['ndormitorios']); ?></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Tamaño</span>
                            <span class="font-weight-bold"><?= htmlspecialchars($vivienda['tamano']); ?> m²</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Operación</span>
                            <span class="font-weight-bold text-capitalize"><?= htmlspecialchars($vivienda['operacion']); ?></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Estado</span>
                            <span class="font-weight-bold text-capitalize"><?= htmlspecialchars($vivienda['estado']); ?></span>
                        </li>            
                        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'agente'])): ?>
                        <li class="list-group-item px-0">
                            <form method="post" class="mb-0">
                                <div class="d-flex align-items-center mb-2">
                                    <label for="estado_select" class="mr-2 mb-0 text-muted">Cambiar Estado:</label>
                                    <select name="estado" id="estado_select" class="form-control form-control-sm mr-2" style="width: auto;">
                                        <option value="disponible" <?= $vivienda['estado'] === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                                        <option value="reservada" <?= $vivienda['estado'] === 'reservada' ? 'selected' : '' ?>>Reservada</option>
                                        <option value="vendida" <?= $vivienda['estado'] === 'vendida' ? 'selected' : '' ?>>Vendida</option>
                                    </select>
                                    <button type="submit" name="cambiar_estado" class="btn btn-primary btn-sm">Cambiar</button>
                                </div>
                                <div id="fecha_reserva_container" class="d-flex align-items-center" style="display: <?= $vivienda['estado'] === 'reservada' ? 'flex' : 'none'; ?>;">
                                    <label for="fecha_reserva" class="mr-2 mb-0 text-muted">Fecha de Reserva:</label>
                                    <input type="date" name="fecha_reserva" id="fecha_reserva" class="form-control form-control-sm mr-2" style="width: auto;" value="<?= htmlspecialchars($vivienda['fecha_reserva'] ?? ''); ?>">
                                </div>
                            </form>
                        </li>
                        <?php endif; ?>
                        <?php if ($vivienda['estado'] === 'reservada' && !empty($vivienda['fecha_reserva'])): ?>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Reservada hasta</span>
                            <span class="font-weight-bold"><?= htmlspecialchars($vivienda['fecha_reserva']); ?></span>
                        </li>
                        <?php endif; ?>
                        <li class="list-group-item px-0 d-flex flex-column">
                            <span class="text-muted mb-1">Extras</span>
                            <span class="font-weight-bold"><?= htmlspecialchars($vivienda['extras']) ?: 'Ninguno especificado'; ?></span>
                        </li>
                        <li class="list-group-item px-0 border-bottom-0 mt-3 bg-light p-3 rounded">
                            <span class="text-muted d-block mb-1 font-weight-bold">Observaciones</span>
                            <span class="small"><?= nl2br(htmlspecialchars($vivienda['observaciones'])) ?: 'No hay observaciones adicionales.'; ?></span>
                        </li>
                    </ul>

                    <?php if (!empty($contactMessage)): ?>
                        <div class="alert alert-success text-center font-weight-bold shadow-sm" role="alert">
                            ✅ <?= htmlspecialchars($contactMessage); ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-auto pt-3">
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
                            <form method="POST" class="d-block w-100">
                                <button type="submit" name="contact" class="btn btn-primary btn-lg btn-block font-weight-bold shadow-sm">
                                    Contactar al propietario
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <div class="alert alert-info py-2 text-center small font-weight-bold mb-3">Modo Administrador Activo</div>
                            <div class="row">
                                <div class="col-6 pr-1">
                                    <a href="editar.php?id=<?= $vivienda['id']; ?>" class="btn btn-warning btn-block font-weight-bold">Modificar</a>
                                </div>
                                <div class="col-6 pl-1">
                                    <a href="eliminar.php?id=<?= $vivienda['id']; ?>" class="btn btn-danger btn-block font-weight-bold" onclick="return confirm('¿Desea eliminar esta vivienda de forma permanente?');">Dar de baja</a>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php if (count($imagenes) > 0): ?>
    <div class="gallery-section mt-4">
        <h3 class="mb-3">Galería</h3>
        <div class="gallery-thumbs d-flex flex-wrap">
            <?php foreach ($imagenes as $index => $imagen): ?>
                <button type="button" class="gallery-thumb btn p-0 border-0 bg-transparent mr-2 mb-2" data-index="<?= $index; ?>">
                    <img src="fotos/<?= htmlspecialchars($imagen); ?>" alt="Imagen <?= $index + 1; ?>" class="img-thumbnail" style="width: 132px; height: 88px; object-fit: cover;">
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<div id="galleryModal" class="gallery-modal d-none">
    <div class="gallery-modal-backdrop"></div>
    <div class="gallery-modal-content">
        <button type="button" class="gallery-modal-close" aria-label="Cerrar galería">×</button>
        <button type="button" class="gallery-modal-nav gallery-modal-prev" aria-label="Anterior">‹</button>
        <img src="" alt="Vista ampliada" class="gallery-modal-img">
        <button type="button" class="gallery-modal-nav gallery-modal-next" aria-label="Siguiente">›</button>
        <div class="gallery-modal-counter"></div>
    </div>
</div>

<!-- Scripts que manejan el tema oscuro y la galería de imágenes -->
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

    // Mostrar/ocultar fecha de reserva
    var estadoSelect = document.getElementById('estado_select');
    var fechaContainer = document.getElementById('fecha_reserva_container');
    if (estadoSelect && fechaContainer) {
        estadoSelect.addEventListener('change', function() {
            if (this.value === 'reservada') {
                fechaContainer.style.display = 'flex';
            } else {
                fechaContainer.style.display = 'none';
            }
        });
    }

    var galleryImages = <?= json_encode($imagenes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var galleryModal = document.getElementById('galleryModal');
    var galleryImg = galleryModal ? galleryModal.querySelector('.gallery-modal-img') : null;
    var galleryCounter = galleryModal ? galleryModal.querySelector('.gallery-modal-counter') : null;
    var currentIndex = 0;

    function openGallery(index) {
        currentIndex = index;
        if (!galleryImg || !galleryImages.length) return;
        galleryImg.src = 'fotos/' + galleryImages[index];
        galleryCounter.textContent = (index + 1) + ' / ' + galleryImages.length;
        galleryModal.classList.remove('d-none');
    }

    function closeGallery() {
        if (!galleryModal) return;
        galleryModal.classList.add('d-none');
    }

    function showNext() {
        if (!galleryImages.length) return;
        currentIndex = (currentIndex + 1) % galleryImages.length;
        openGallery(currentIndex);
    }

    function showPrev() {
        if (!galleryImages.length) return;
        currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
        openGallery(currentIndex);
    }

    document.querySelectorAll('.gallery-open').forEach(function(button) {
        button.addEventListener('click', function() {
            openGallery(parseInt(this.getAttribute('data-index'), 10) || 0);
        });
    });

    document.querySelectorAll('.gallery-thumb').forEach(function(button) {
        button.addEventListener('click', function() {
            openGallery(parseInt(this.getAttribute('data-index'), 10) || 0);
        });
    });

    if (galleryModal) {
        galleryModal.querySelector('.gallery-modal-close').addEventListener('click', closeGallery);
        galleryModal.querySelector('.gallery-modal-prev').addEventListener('click', function(e) {
            e.stopPropagation();
            showPrev();
        });
        galleryModal.querySelector('.gallery-modal-next').addEventListener('click', function(e) {
            e.stopPropagation();
            showNext();
        });
        galleryModal.addEventListener('click', function(event) {
            if (event.target === galleryModal || event.target.classList.contains('gallery-modal-backdrop')) {
                closeGallery();
            }
        });
        document.addEventListener('keydown', function(event) {
            if (galleryModal.classList.contains('d-none')) return;
            if (event.key === 'Escape') {
                closeGallery();
            } else if (event.key === 'ArrowRight') {
                showNext();
            } else if (event.key === 'ArrowLeft') {
                showPrev();
            }
        });
    }
});
</script>
</body>
</html>