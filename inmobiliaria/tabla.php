<?php
session_start();
// Iniciamos sesión para poder verificar roles y mantener el estado del usuario.
require_once "php/conexion.php";
$conexion = conexion();

// Recogemos los filtros desde los parámetros GET del formulario.
$f_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$f_zona = isset($_GET['zona']) ? $_GET['zona'] : '';
$f_operacion = isset($_GET['operacion']) ? $_GET['operacion'] : '';
$f_solo_disponibles = isset($_GET['solo_disponibles']) && $_GET['solo_disponibles'] === '1';
$f_solo_vendidas = isset($_GET['solo_vendidas']) && $_GET['solo_vendidas'] === '1';
$f_min_precio = isset($_GET['min_precio']) ? $_GET['min_precio'] : '';
$f_max_precio = isset($_GET['max_precio']) ? $_GET['max_precio'] : '';
$f_dormitorios = isset($_GET['dormitorios']) ? $_GET['dormitorios'] : '';

$f_extras = isset($_GET['extras']) ? $_GET['extras'] : [];

$preservedFilters = [];
foreach (['tipo','zona','operacion','dormitorios','min_precio','max_precio','solo_disponibles','solo_vendidas','extras'] as $filterKey) {
    if (isset($_GET[$filterKey])) {
        $preservedFilters[$filterKey] = $_GET[$filterKey];
    }
}

$where = [];

if ($f_tipo !== '') {
    $where[] = "vt.nombre = '" . mysqli_real_escape_string($conexion, $f_tipo) . "'";
}
if ($f_zona !== '') {
    $where[] = "vz.nombre = '" . mysqli_real_escape_string($conexion, $f_zona) . "'";
}
if ($f_min_precio !== '') {
    $where[] = "precio >= " . intval($f_min_precio);
}
if ($f_max_precio !== '') {
    $where[] = "precio <= " . intval($f_max_precio);
}
if ($f_dormitorios !== '') {
    $where[] = "ndormitorios >= " . intval($f_dormitorios);
}

if ($f_operacion !== '') {
    $where[] = "operacion = '" . mysqli_real_escape_string($conexion, $f_operacion) . "'";
}

if ($f_solo_vendidas && isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'agente'], true)) {
    $where[] = "estado = 'vendida'";
} elseif ($f_solo_disponibles) {
    $where[] = "estado = 'disponible'";
} else {
    $where[] = "estado != 'vendida'";
}

if (!empty($f_extras) && is_array($f_extras)) {
    foreach ($f_extras as $extra) {
        $safe_extra = mysqli_real_escape_string($conexion, $extra);
        $where[] = "FIND_IN_SET('" . $safe_extra . "', extras)";
    }
}

// Construimos la consulta SQL según los filtros seleccionados.
$instruccion = "SELECT v.*, vt.nombre AS tipo_nombre, vz.nombre AS zona_nombre FROM viviendas v "
    . "LEFT JOIN viviendas_tipos vt ON v.tipo_id = vt.id "
    . "LEFT JOIN viviendas_zonas vz ON v.zona_id = vz.id";

if (count($where) > 0) {
    $instruccion .= " WHERE " . implode(" AND ", $where);
}

$instruccion .= " ORDER BY precio ASC";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de viviendas | Inmobiliaria AURA</title>
    
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

<div class="container mt-5 mb-5">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
        <h1 class="text-primary font-weight-bold mb-3 mb-md-0">Propiedades</h1>
        
        <div class="d-flex flex-wrap justify-content-center justify-content-md-end">
            <a href="index.php" class="btn btn-outline-secondary mr-2 mb-2">Volver al inicio</a>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="alta.php" class="btn btn-success mr-2 mb-2">Agregar vivienda</a>
            <?php endif; ?>
            
            <?php if (!isset($_SESSION['user'])): ?>
                <a href="inicio.php" class="btn btn-outline-dark mb-2">Iniciar sesión</a>
            <?php else: ?>
                <a href="php/salir.php" class="btn btn-danger mb-2">Cerrar sesión</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-5 bg-white">
        <div class="card-body p-4">
            <!-- Formulario de filtros que actualiza los resultados al cambiar los campos -->
            <form method="GET" action="tabla.php">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold small text-muted">Tipo de propiedad</label>
                        <select name="tipo" class="form-control form-control-sm">
                            <option value="">Todos los tipos</option>
                            <?php
                            $tiposRes = mysqli_query($conexion, "SELECT nombre FROM viviendas_tipos ORDER BY nombre");
                            if ($tiposRes) {
                                while ($r = mysqli_fetch_assoc($tiposRes)) {
                                    $val = $r['nombre'];
                                    $sel = $f_tipo === $val ? 'selected' : '';
                                    echo "<option value=\"" . htmlspecialchars($val) . "\" $sel>" . htmlspecialchars($val) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold small text-muted">Zona</label>
                        <select name="zona" class="form-control form-control-sm">
                            <option value="">Todas las zonas</option>
                            <?php
                            $zonasRes = mysqli_query($conexion, "SELECT nombre FROM viviendas_zonas ORDER BY nombre");
                            if ($zonasRes) {
                                while ($rz = mysqli_fetch_assoc($zonasRes)) {
                                    $valz = $rz['nombre'];
                                    $selz = $f_zona === $valz ? 'selected' : '';
                                    echo "<option value=\"" . htmlspecialchars($valz) . "\" $selz>" . htmlspecialchars($valz) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2 mb-3">
                        <label class="font-weight-bold small text-muted">Dormitorios (min)</label>
                        <input type="number" name="dormitorios" class="form-control form-control-sm" placeholder="Ej: 2" min="1" max="255" value="<?= htmlspecialchars($f_dormitorios) ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small text-muted">Rango de precio ($)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="min_precio" class="form-control" placeholder="Mínimo" min="0" value="<?= htmlspecialchars($f_min_precio) ?>">
                            <div class="input-group-prepend input-group-append">
                                <span class="input-group-text border-left-0 border-right-0">-</span>
                            </div>
                            <input type="number" name="max_precio" class="form-control" placeholder="Máximo" min="0" value="<?= htmlspecialchars($f_max_precio) ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="font-weight-bold small text-muted">Operación</label>
                        <select name="operacion" class="form-control form-control-sm">
                            <option value="" <?= $f_operacion === '' ? 'selected' : '' ?>>Todas</option>
                            <option value="venta" <?= $f_operacion === 'venta' ? 'selected' : '' ?>>Venta</option>
                            <option value="alquiler" <?= $f_operacion === 'alquiler' ? 'selected' : '' ?>>Alquiler</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="solo_disponibles" name="solo_disponibles" value="1" <?= $f_solo_disponibles ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="solo_disponibles">Mostrar solo disponibles</label>
                        </div>
                    </div>
                    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'agente'], true)): ?>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="solo_vendidas" name="solo_vendidas" value="1" <?= $f_solo_vendidas ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="solo_vendidas">Vendidas</label>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row mt-2">
                    <div class="col-12 mb-3">
                        <label class="font-weight-bold small text-muted d-block">Extras:</label>
                        
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="extra_piscina" name="extras[]" value="Piscina" <?= in_array('Piscina', $f_extras) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="extra_piscina">Piscina</label>
                        </div>
                        
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="extra_jardin" name="extras[]" value="Jardin" <?= in_array('Jardin', $f_extras) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="extra_jardin">Jardín</label>
                        </div>
                        
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="extra_garage" name="extras[]" value="Garage" <?= in_array('Garage', $f_extras) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="extra_garage">Garage</label>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-2 border-top pt-3">
                    <?php if (count($where) > 0): ?>
                        <button type="button" id="btn-clear-filters" class="btn btn-light btn-sm mr-2 text-muted border">Limpiar filtros</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="row">

        <?php
        $consulta = mysqli_query($conexion, $instruccion) or die("Fallo en la consulta");
        $nfilas = mysqli_num_rows($consulta);

        if ($nfilas > 0) {
            while ($resultado = mysqli_fetch_assoc($consulta)) {
                $displayTipo = $resultado['tipo_nombre'] ?? '';
                $displayZona = $resultado['zona_nombre'] ?? '';
        ?>
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        
                        <img src="fotos/<?= htmlspecialchars($resultado['foto']) ?>" class="card-img-top" alt="Foto de la vivienda en <?= htmlspecialchars($displayZona) ?>">
                        
                        <div class="card-body">
                            <h5 class="card-title text-primary font-weight-bold">
                                <?= htmlspecialchars($displayTipo) ?> en <?= htmlspecialchars($displayZona) ?>
                            </h5>
                            <div class="mb-2">
                                <?php if ($resultado['estado'] === 'reservada'): ?>
                                    <span class="badge badge-warning text-uppercase mr-1">Reservada</span>
                                <?php elseif ($resultado['estado'] === 'vendida'): ?>
                                    <span class="badge badge-danger text-uppercase mr-1">Vendida</span>
                                <?php else: ?>
                                    <span class="badge badge-success text-uppercase mr-1">Disponible</span>
                                <?php endif; ?>
                                <span class="badge badge-info text-uppercase"><?= htmlspecialchars($resultado['operacion']) ?></span>
                            </div>
                            
                            <ul class="list-unstyled mt-3 mb-0 text-muted">
                                <li><strong>Precio:</strong> $<?= htmlspecialchars($resultado['precio']) ?></li>
                                <li><strong>Dormitorios:</strong> <?= htmlspecialchars($resultado['ndormitorios']) ?></li>
                                <li><strong>Tamaño:</strong> <?= htmlspecialchars($resultado['tamano']) ?> m²</li>
                                <li><strong>Extras:</strong> <?= htmlspecialchars($resultado['extras']) ?></li>
                            </ul>
                        </div>

                        <div class="card-footer bg-white border-0 text-center pb-4">
                            <?php $detalleUrl = 'detalle.php?' . http_build_query(array_merge($preservedFilters, ['id' => $resultado['id']])); ?>
                            <a href='<?= htmlspecialchars($detalleUrl); ?>' class='btn btn-outline-primary btn-block mb-2'>Ver Detalles</a>
                            
                            <?php 
                            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): 
                            ?>
                                <div class="d-flex justify-content-between mt-2">
                                    <a href='editar.php?id=<?= $resultado['id'] ?>' class='btn btn-sm btn-warning w-50 mr-1'>Editar</a>
                                    <a href='eliminar.php?id=<?= $resultado['id'] ?>' class='btn btn-sm btn-danger w-50 ml-1' onclick="return confirm('¿Desea eliminar esta vivienda?');">Baja</a>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
        <?php
            }
        } else {
            echo "
            <div class='col-12'>
                <div class='alert alert-warning text-center shadow-sm p-5'>
                    <h4 class='alert-heading font-weight-bold'>No encontramos propiedades</h4>
                    <p>No hay resultados que coincidan con tus filtros de búsqueda.</p>
                    <a href='tabla.php' class='btn btn-outline-dark mt-3'>Ver todas las propiedades</a>
                </div>
            </div>";
        }

        mysqli_close($conexion);
        ?>

    </div> 
</div> 
<!-- Scripts para tema oscuro y guardado/autoenvío de filtros -->
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

    var STORAGE_KEY = 'tabla_filtros';
    var filterForm = document.querySelector('form[action="tabla.php"]');
    if (!filterForm) return;

    var timeoutId;
    var debounceSubmit = function() {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(function() {
            saveFilterState();
            filterForm.submit();
        }, 350);
    };

    var immediateSubmit = function() {
        window.clearTimeout(timeoutId);
        saveFilterState();
        filterForm.submit();
    };

    var getFormState = function() {
        var extras = [];
        filterForm.querySelectorAll('input[name="extras[]"]').forEach(function(extra) {
            if (extra.checked) {
                extras.push(extra.value);
            }
        });

        return {
            tipo: filterForm.tipo ? filterForm.tipo.value : '',
            zona: filterForm.zona ? filterForm.zona.value : '',
            operacion: filterForm.operacion ? filterForm.operacion.value : '',
            dormitorios: filterForm.dormitorios ? filterForm.dormitorios.value : '',
            min_precio: filterForm.min_precio ? filterForm.min_precio.value : '',
            max_precio: filterForm.max_precio ? filterForm.max_precio.value : '',
            solo_disponibles: document.getElementById('solo_disponibles') ? document.getElementById('solo_disponibles').checked : false,
            solo_vendidas: document.getElementById('solo_vendidas') ? document.getElementById('solo_vendidas').checked : false,
            extras: extras
        };
    };

    var applyFormState = function(state) {
        if (!state) return;
        if (filterForm.tipo) filterForm.tipo.value = state.tipo || '';
        if (filterForm.zona) filterForm.zona.value = state.zona || '';
        if (filterForm.operacion) filterForm.operacion.value = state.operacion || '';
        if (filterForm.dormitorios) filterForm.dormitorios.value = state.dormitorios || '';
        if (filterForm.min_precio) filterForm.min_precio.value = state.min_precio || '';
        if (filterForm.max_precio) filterForm.max_precio.value = state.max_precio || '';

        var disponibles = document.getElementById('solo_disponibles');
        var vendidas = document.getElementById('solo_vendidas');
        if (disponibles) disponibles.checked = !!state.solo_disponibles;
        if (vendidas) vendidas.checked = !!state.solo_vendidas;

        filterForm.querySelectorAll('input[name="extras[]"]').forEach(function(extra) {
            extra.checked = Array.isArray(state.extras) && state.extras.indexOf(extra.value) !== -1;
        });
    };

    var saveFilterState = function() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(getFormState()));
        } catch (e) {
            // LocalStorage no disponible o lleno
        }
    };

    var loadFilterState = function() {
        try {
            var stored = localStorage.getItem(STORAGE_KEY);
            return stored ? JSON.parse(stored) : null;
        } catch (e) {
            return null;
        }
    };

    var statesEqual = function(a, b) {
        if (!a || !b) return false;
        var keys = ['tipo', 'zona', 'operacion', 'dormitorios', 'min_precio', 'max_precio', 'solo_disponibles', 'solo_vendidas'];
        for (var i = 0; i < keys.length; i++) {
            if (a[keys[i]] !== b[keys[i]]) return false;
        }
        if (!Array.isArray(a.extras) || !Array.isArray(b.extras)) {
            return false;
        }
        if (a.extras.length !== b.extras.length) return false;
        for (var j = 0; j < a.extras.length; j++) {
            if (b.extras.indexOf(a.extras[j]) === -1) return false;
        }
        return true;
    };

    var currentState = getFormState();
    var storedState = loadFilterState();
    if (storedState && !statesEqual(currentState, storedState) && window.location.search.length === 0) {
        applyFormState(storedState);
        saveFilterState();
        immediateSubmit();
        return;
    }

    saveFilterState();

    var controls = filterForm.querySelectorAll('select, input[type="checkbox"], input[type="number"]');
    controls.forEach(function(control) {
        if (control.type === 'checkbox' || control.tagName.toLowerCase() === 'select') {
            control.addEventListener('change', function() {
                if (this.id === 'solo_disponibles' && this.checked) {
                    var vendidas = document.getElementById('solo_vendidas');
                    if (vendidas) vendidas.checked = false;
                }
                if (this.id === 'solo_vendidas' && this.checked) {
                    var disponibles = document.getElementById('solo_disponibles');
                    if (disponibles) disponibles.checked = false;
                }
                immediateSubmit();
            });
        } else {
            control.addEventListener('input', debounceSubmit);
        }
    });

    var clearButton = document.getElementById('btn-clear-filters');
    if (clearButton) {
        clearButton.addEventListener('click', function() {
            localStorage.removeItem(STORAGE_KEY);
            filterForm.querySelectorAll('select').forEach(function(select) {
                select.selectedIndex = 0;
            });
            filterForm.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
                checkbox.checked = false;
            });
            filterForm.querySelectorAll('input[type="number"]').forEach(function(input) {
                input.value = '';
            });
            filterForm.submit();
        });
    }
});
</script>
</body>
</html>