

<?php 
	// Devuelve la conexión mysqli a la base de datos.
	function conexion()
	{
		$conexion = mysqli_connect("localhost","c2790799_base1","19ramaKAzu","c2790799_base1");

		// Nota: la creación y el llenado de las tablas auxiliares `viviendas_tipos` y
		// `viviendas_zonas` se realiza por scripts SQL externos. Aquí solo devolvemos
		// la conexión.

		return $conexion;
	}

	function logUserInterest($conexion, $usuario, $viviendaId, $filtros, $contactado = false)
	{
		$usuario = mysqli_real_escape_string($conexion, $usuario);
		$viviendaId = intval($viviendaId);
		$filtrosJson = mysqli_real_escape_string($conexion, json_encode($filtros, JSON_UNESCAPED_UNICODE));
		$contactadoValue = $contactado ? 1 : 0;

		$sql = "INSERT INTO usuarios_intereses (usuario, vivienda_id, filtros, contactado, fecha_vista, fecha_contacto) VALUES ('$usuario', $viviendaId, '$filtrosJson', $contactadoValue, NOW(), " . ($contactado ? "NOW()" : "NULL") . ")"
		    . " ON DUPLICATE KEY UPDATE"
		    . " filtros = VALUES(filtros),"
		    . " contactado = GREATEST(contactado, VALUES(contactado)),"
		    . " fecha_vista = VALUES(fecha_vista),"
		    . " fecha_contacto = CASE WHEN VALUES(contactado) = 1 THEN VALUES(fecha_contacto) ELSE fecha_contacto END";

		mysqli_query($conexion, $sql);
	}

	function getViviendasTipoId($conexion, $tipoNombre)
	{
		$tipoNombre = trim($tipoNombre);
		if ($tipoNombre === '') {
			return null;
		}
		$safeTipo = mysqli_real_escape_string($conexion, $tipoNombre);
		mysqli_query($conexion, "INSERT IGNORE INTO viviendas_tipos (nombre) VALUES ('$safeTipo')");
		$result = mysqli_query($conexion, "SELECT id FROM viviendas_tipos WHERE nombre = '$safeTipo' LIMIT 1");
		$row = $result ? mysqli_fetch_assoc($result) : null;
		return $row ? intval($row['id']) : null;
	}

	function getViviendasZonaId($conexion, $zonaNombre)
	{
		$zonaNombre = trim($zonaNombre);
		if ($zonaNombre === '') {
			return null;
		}
		$safeZona = mysqli_real_escape_string($conexion, $zonaNombre);
		mysqli_query($conexion, "INSERT IGNORE INTO viviendas_zonas (nombre) VALUES ('$safeZona')");
		$result = mysqli_query($conexion, "SELECT id FROM viviendas_zonas WHERE nombre = '$safeZona' LIMIT 1");
		$row = $result ? mysqli_fetch_assoc($result) : null;
		return $row ? intval($row['id']) : null;
	}

 ?>
