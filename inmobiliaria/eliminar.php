<?php
// Solo el administrador puede eliminar viviendas.
require_once "php/admin_validacion.php";
// Conexión a la base de datos para eliminar el registro y sus archivos.
require_once "php/conexion.php";
$conexion = conexion();
$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $result = mysqli_query($conexion, "SELECT foto FROM viviendas WHERE id=$id");
    if ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['foto']) && file_exists('fotos/' . $row['foto'])) {
            unlink('fotos/' . $row['foto']);
        }
        $galleryFile = 'fotos/galeria_' . $id . '.json';
        if (file_exists($galleryFile)) {
            $galleryItems = json_decode(file_get_contents($galleryFile), true);
            if (is_array($galleryItems)) {
                foreach ($galleryItems as $galleryPhoto) {
                    if (!empty($galleryPhoto) && file_exists('fotos/' . $galleryPhoto)) {
                        unlink('fotos/' . $galleryPhoto);
                    }
                }
            }
            unlink($galleryFile);
        }
    }
    mysqli_query($conexion, "DELETE FROM viviendas WHERE id=$id");
}
header("Location: tabla.php");
exit();