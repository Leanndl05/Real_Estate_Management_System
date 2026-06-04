<?php
require_once "php/validacion_gestion_usuarios.php";
require_once "php/conexion.php";
$conexion = conexion();

$usuario = mysqli_real_escape_string($conexion, $_GET['usuario'] ?? '');
$usuarioInfo = null;
if (!empty($usuario)) {
    $sql = "SELECT nombre, apellido, usuario, dni, email, telefono, fecha_registro, rol FROM usuarios WHERE usuario = '$usuario' LIMIT 1";
    $result = mysqli_query($conexion, $sql);
    $usuarioInfo = $result ? mysqli_fetch_assoc($result) : null;
    
    // Si es agente, solo puede ver usuarios con rol 'user'
    if ($_SESSION['role'] === 'agente' && $usuarioInfo && $usuarioInfo['rol'] !== 'user') {
        $usuarioInfo = null; // Bloquear acceso
    }
}
$usuarioIntereses = [];
if (!empty($usuarioInfo)) {
    $sqlIntereses = "SELECT ui.*, vt.nombre AS tipo, vz.nombre AS zona, v.direccion, v.precio, v.operacion, v.estado "
        . "FROM usuarios_intereses ui "
        . "LEFT JOIN viviendas v ON ui.vivienda_id = v.id "
        . "LEFT JOIN viviendas_tipos vt ON v.tipo_id = vt.id "
        . "LEFT JOIN viviendas_zonas vz ON v.zona_id = vz.id "
        . "WHERE ui.usuario = '$usuario' ORDER BY ui.fecha_vista DESC";
    $resultIntereses = mysqli_query($conexion, $sqlIntereses);
    if ($resultIntereses) {
        $usuarioIntereses = mysqli_fetch_all($resultIntereses, MYSQLI_ASSOC);
    }
}

function formatFilterSummary($json)
{
    $filters = json_decode($json, true);
    if (!is_array($filters) || count($filters) === 0) {
        return 'Sin filtros guardados';
    }

    $parts = [];
    if (!empty($filters['tipo'])) {
        $parts[] = 'Tipo: ' . $filters['tipo'];
    }
    if (!empty($filters['zona'])) {
        $parts[] = 'Zona: ' . $filters['zona'];
    }
    if (!empty($filters['operacion'])) {
        $parts[] = 'Operación: ' . $filters['operacion'];
    }
    if (!empty($filters['dormitorios'])) {
        $parts[] = 'Dormitorios: ' . $filters['dormitorios'];
    }
    if (!empty($filters['min_precio'])) {
        $parts[] = 'Precio min: $' . $filters['min_precio'];
    }
    if (!empty($filters['max_precio'])) {
        $parts[] = 'Precio max: $' . $filters['max_precio'];
    }
    if (!empty($filters['solo_disponibles'])) {
        $parts[] = 'Solo disponibles';
    }
    if (!empty($filters['solo_vendidas'])) {
        $parts[] = 'Solo vendidas';
    }
    if (!empty($filters['extras']) && is_array($filters['extras'])) {
        $parts[] = 'Extras: ' . implode(', ', $filters['extras']);
    }

    return $parts ? implode(' · ', $parts) : 'Sin filtros guardados';
}

function buildDetalleLink($id)
{
    return 'detalle.php?id=' . intval($id);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información de Usuario | Inmobiliaria AURA</title>
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

<div class="container mt-5 pt-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Información adicional</h2>
                    <a href="gestion_usuarios.php" class="btn btn-outline-secondary btn-sm">Volver</a>
                </div>
                <div class="card-body">
                    <?php if (empty($usuario) || !$usuarioInfo): ?>
                        <div class="alert alert-warning" role="alert">
                            Usuario no encontrado o no se envió ningún parámetro.
                        </div>
                    <?php else: ?>
                        <h4 class="mb-3"><?= htmlspecialchars($usuarioInfo['apellido'] . ', ' . $usuarioInfo['nombre']); ?></h4>
                        <p class="text-muted mb-4">Usuario: <strong><?= htmlspecialchars($usuarioInfo['usuario']); ?></strong></p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 bg-white">
                                    <h6 class="text-uppercase text-secondary">Email</h6>
                                    <p class="mb-0"><?= htmlspecialchars($usuarioInfo['email'] ?? 'No disponible'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 bg-white">
                                    <h6 class="text-uppercase text-secondary">Teléfono</h6>
                                    <p class="mb-0"><?= htmlspecialchars($usuarioInfo['telefono'] ?? 'No disponible'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 bg-white">
                                    <h6 class="text-uppercase text-secondary">DNI</h6>
                                    <p class="mb-0"><?= htmlspecialchars($usuarioInfo['dni'] ?? 'No disponible'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 bg-white">
                                    <h6 class="text-uppercase text-secondary">Fecha de registro</h6>
                                    <p class="mb-0">
                                        <?= !empty($usuarioInfo['fecha_registro']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($usuarioInfo['fecha_registro']))) : 'No disponible'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h5 class="mb-3">Casas en las que estuvo interesado</h5>
                            <?php if (empty($usuarioIntereses)): ?>
                                <div class="alert alert-secondary" role="alert">
                                    No se han registrado visitas a propiedades para este usuario.
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($usuarioIntereses as $interes): ?>
                                        <div class="col-12 mb-3">
                                            <div class="card shadow-sm <?= $interes['contactado'] ? 'border-success' : ''; ?>">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="font-weight-bold mb-1"><?= htmlspecialchars(($interes['tipo'] ?? 'Vivienda') . ' en ' . ($interes['zona'] ?? '')); ?></h6>
                                                            <p class="mb-1 text-muted small"><?= htmlspecialchars($interes['direccion'] ?? 'Dirección no disponible'); ?></p>
                                                            <p class="mb-1 small text-muted">
                                                                <?= !empty($interes['precio']) ? 'Precio: $' . htmlspecialchars($interes['precio']) . ' · ' : ''; ?>
                                                                <?= !empty($interes['operacion']) ? 'Operación: ' . htmlspecialchars($interes['operacion']) : ''; ?>
                                                            </p>
                                                        </div>
                                                        <div class="text-right">
                                                            <?php if ($interes['contactado']): ?>
                                                                <span class="badge badge-success">Contactado</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-secondary">Visto</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <p class="mb-2 small"><strong>Fecha de vista:</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($interes['fecha_vista']))); ?></p>
                                                    <?php if (!empty($interes['fecha_contacto'])): ?>
                                                        <p class="mb-2 small"><strong>Fecha de contacto:</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($interes['fecha_contacto']))); ?></p>
                                                    <?php endif; ?>
                                                    <p class="mb-2 small"><strong>Filtros usados:</strong> <?= nl2br(htmlspecialchars(formatFilterSummary($interes['filtros']))); ?></p>
                                                    <?php if (!empty($interes['vivienda_id'])): ?>
                                                        <a href="<?= htmlspecialchars(buildDetalleLink($interes['vivienda_id'])); ?>" class="btn btn-sm btn-outline-primary">Ver detalle</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
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
