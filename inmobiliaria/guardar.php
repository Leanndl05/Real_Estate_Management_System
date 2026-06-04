<?php
// Validamos que sea un administrador antes de guardar una nueva vivienda.
require_once "php/admin_validacion.php";
// Abrimos la conexión a la base de datos.
require 'php/conexion.php';
$conectar = conexion();

// Limpiamos los datos recibidos por POST para evitar inyección SQL.
$tipo = mysqli_real_escape_string($conectar, $_POST['tipo'] ?? '');
$zona = mysqli_real_escape_string($conectar, $_POST['zona'] ?? '');
$direccion = mysqli_real_escape_string($conectar, $_POST['direccion'] ?? '');
$ndormitorios = mysqli_real_escape_string($conectar, $_POST['ndormitorios'] ?? '');
$precio = mysqli_real_escape_string($conectar, $_POST['precio'] ?? '');
$tamano = mysqli_real_escape_string($conectar, $_POST['tamano'] ?? '');
$extras = mysqli_real_escape_string($conectar, $_POST['extras'] ?? '');
$estado = mysqli_real_escape_string($conectar, $_POST['estado'] ?? 'disponible');
$operacion = mysqli_real_escape_string($conectar, $_POST['operacion'] ?? 'venta');
$fecha_reserva = mysqli_real_escape_string($conectar, $_POST['fecha_reserva'] ?? '');
$observaciones = mysqli_real_escape_string($conectar, $_POST['observaciones'] ?? '');

$tipoId = null;
$zonaId = null;
if ($tipo !== '') {
    $tipoId = getViviendasTipoId($conectar, $tipo);
}
if ($zona !== '') {
    $zonaId = getViviendasZonaId($conectar, $zona);
}

$allowedEstados = ['disponible', 'reservada', 'vendida'];
$allowedOperaciones = ['venta', 'alquiler'];
if (!in_array($estado, $allowedEstados, true)) {
    $estado = 'disponible';
}
if (!in_array($operacion, $allowedOperaciones, true)) {
    $operacion = 'venta';
}
if ($fecha_reserva !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_reserva)) {
    $fecha_reserva = '';
}
$fechaReservaValue = $fecha_reserva !== '' ? "'$fecha_reserva'" : 'NULL';

$fotoNombre = '';
$galeriaArchivos = [];
$targetDir = 'fotos/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}
// Procesamos la imagen principal si se subió un archivo válido.
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['foto']['tmp_name'];
    $fileName = basename($_FILES['foto']['name']);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($extension, $allowed)) {
        $fotoNombre = time() . '_' . uniqid() . '.' . $extension;
        move_uploaded_file($fileTmp, $targetDir . $fotoNombre);
    }
}

// Procesamos las imágenes adicionales de la galería si se enviaron.
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
            $galeriaArchivos[] = $galleryName;
        }
    }
}

$insertar = "INSERT INTO viviendas (tipo_id, zona_id, direccion, ndormitorios, precio, tamano, extras, estado, operacion, fecha_reserva, foto, observaciones) VALUES (" . ($tipoId !== null ? intval($tipoId) : 'NULL') . ", " . ($zonaId !== null ? intval($zonaId) : 'NULL') . ", '$direccion','$ndormitorios','$precio','$tamano','$extras','$estado','$operacion',$fechaReservaValue,'$fotoNombre','$observaciones')";
$query = mysqli_query($conectar, $insertar);

if (empty($fotoNombre) && !empty($galeriaArchivos)) {
    $fotoNombre = $galeriaArchivos[0];
    mysqli_query($conectar, "UPDATE viviendas SET foto='$fotoNombre' WHERE id=" . mysqli_insert_id($conectar));
}
if ($query && !empty($galeriaArchivos)) {
    $id = mysqli_insert_id($conectar);
    file_put_contents($targetDir . 'galeria_' . $id . '.json', json_encode(array_values($galeriaArchivos)));
}

if ($query) {
    header("Location: index.php?ok=1");
} else {
    $err = urlencode(mysqli_error($conectar));
    header("Location: index.php?error=1&sqlerr={$err}");
}
exit();