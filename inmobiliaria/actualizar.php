<?php
// Validación de administrador para actualizar propiedades.
require_once "php/admin_validacion.php";
// Conexión a la base de datos para modificar el registro.
require_once "php/conexion.php";
$conexion = conexion();

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    header("Location: tabla.php");
    exit();
}
$tipo = mysqli_real_escape_string($conexion, $_POST['tipo'] ?? '');
$resultV = mysqli_query($conexion, "SELECT foto FROM viviendas WHERE id=$id");
$vivienda = mysqli_fetch_assoc($resultV);
$currentFoto = $vivienda['foto'] ?? '';
$zona = mysqli_real_escape_string($conexion, $_POST['zona'] ?? '');
$direccion = mysqli_real_escape_string($conexion, $_POST['direccion'] ?? '');
$ndormitorios = mysqli_real_escape_string($conexion, $_POST['ndormitorios'] ?? '');
$precio = mysqli_real_escape_string($conexion, $_POST['precio'] ?? '');
$tamano = mysqli_real_escape_string($conexion, $_POST['tamano'] ?? '');
$extras = mysqli_real_escape_string($conexion, $_POST['extras'] ?? '');
$estado = mysqli_real_escape_string($conexion, $_POST['estado'] ?? 'disponible');
$operacion = mysqli_real_escape_string($conexion, $_POST['operacion'] ?? 'venta');
$observaciones = mysqli_real_escape_string($conexion, $_POST['observaciones'] ?? '');
$fecha_reserva = mysqli_real_escape_string($conexion, $_POST['fecha_reserva'] ?? '');

$tipoId = null;
$zonaId = null;
if ($tipo !== '') {
    $tipoId = getViviendasTipoId($conexion, $tipo);
}
if ($zona !== '') {
    $zonaId = getViviendasZonaId($conexion, $zona);
}

$estadoValores = ['disponible', 'reservada', 'vendida'];
$operacionValores = ['venta', 'alquiler'];
if (!in_array($estado, $estadoValores, true)) {
    $estado = 'disponible';
}
if (!in_array($operacion, $operacionValores, true)) {
    $operacion = 'venta';
}
if ($fecha_reserva !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_reserva)) {
    $fecha_reserva = '';
}
$fechaReservaValue = $fecha_reserva !== '' ? "'$fecha_reserva'" : 'NULL';

$targetDir = 'fotos/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$foto = '';
$updateFoto = false;
$uploadedMain = '';
$galleryFile = $targetDir . 'galeria_' . $id . '.json';
$listaGaleria = [];
if (file_exists($galleryFile)) {
    $listaGaleria = json_decode(file_get_contents($galleryFile), true);
    if (!is_array($listaGaleria)) {
        $listaGaleria = [];
    }
}

// Procesamos el posible reemplazo de la foto principal.
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['foto']['tmp_name'];
    $fileName = basename($_FILES['foto']['name']);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($extension, $allowed)) {
        $newFileName = time() . '_' . uniqid() . '.' . $extension;
        if (move_uploaded_file($fileTmp, $targetDir . $newFileName)) {
            $foto = $newFileName;
            $uploadedMain = $newFileName;
            $updateFoto = true;
        }
    }
}

if (isset($_FILES['galeria']) && !empty($_FILES['galeria']['name'][0])) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    foreach ($_FILES['galeria']['name'] as $index => $name) {
        if ($_FILES['galeria']['error'][$index] !== UPLOAD_ERR_OK) {
            continue;
        }
        $fileTmp = $_FILES['galeria']['tmp_name'][$index];
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed)) {
            continue;
        }
        $galleryName = time() . '_' . uniqid() . '.' . $extension;
        if (move_uploaded_file($fileTmp, $targetDir . $galleryName)) {
            $listaGaleria[] = $galleryName;
        }
    }
}

$principalSeleccionado = $_POST['principal'] ?? 'current';
if ($principalSeleccionado !== 'current' && in_array($principalSeleccionado, $listaGaleria, true)) {
    $pos = array_search($principalSeleccionado, $listaGaleria, true);
    if ($pos !== false) {
        if (!empty($currentFoto)) {
            $listaGaleria[$pos] = $currentFoto;
        } else {
            unset($listaGaleria[$pos]);
        }
        $foto = $principalSeleccionado;
        $updateFoto = true;
        if (!empty($uploadedMain)) {
            $listaGaleria[] = $uploadedMain;
        }
        $listaGaleria = array_values($listaGaleria);
    }
}

if ($updateFoto) {
    $sqlFoto = "foto = '$foto', ";
} else {
    $sqlFoto = "";
}

$sql = "UPDATE viviendas SET tipo_id=" . ($tipoId !== null ? intval($tipoId) : 'NULL') . ", zona_id=" . ($zonaId !== null ? intval($zonaId) : 'NULL') . ", direccion='$direccion', ndormitorios='$ndormitorios', precio='$precio', tamano='$tamano', extras='$extras', estado='$estado', operacion='$operacion', fecha_reserva=$fechaReservaValue, $sqlFoto observaciones='$observaciones' WHERE id=$id";
$result = mysqli_query($conexion, $sql);
// Si la actualización fue exitosa, guardamos la lista de galería o la borramos si ya no existe.
if ($result) {
    if (!empty($listaGaleria)) {
        file_put_contents($galleryFile, json_encode(array_values($listaGaleria)));
    } elseif (file_exists($galleryFile)) {
        unlink($galleryFile);
    }
    header("Location: detalle.php?id=$id");
} else {
    $err = urlencode(mysqli_error($conexion));
    header("Location: editar.php?id=$id&error=1&sqlerr={$err}");
}
exit();