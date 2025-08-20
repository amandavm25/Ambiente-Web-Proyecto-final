<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$host = 'localhost';
$usuario = 'root';
$contrasenia = '';
$base_datos = 'mamalilabase_db';

$mysqli = new mysqli($host, $usuario, $contrasenia, $base_datos);
if ($mysqli->connect_error) {
    die("<div class='alert alert-danger'>Error en la conexión a la base de datos</div>");
} else {
    $mysqli->set_charset('utf8mb4');
}

// Hora local CR
date_default_timezone_set('America/Costa_Rica');
$mysqli->query("SET time_zone = '-06:00'");
