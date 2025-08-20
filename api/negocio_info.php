<?php
session_start();
require_once("../include/conexion.php");
header('Content-Type: application/json; charset=utf-8');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  echo json_encode(['ok' => false]);
  exit;
}

$st = $mysqli->prepare("SELECT Id_negocio, Nombre, Horario FROM negocios WHERE Id_negocio=? LIMIT 1");
$st->bind_param("i", $id);
$st->execute();
$row = $st->get_result()->fetch_assoc();
$st->close();

if (!$row) {
  echo json_encode(['ok' => false]);
  exit;
}

echo json_encode([
  'ok'          => true,
  'Id_negocio'  => (int)$row['Id_negocio'],
  'Nombre'      => $row['Nombre'],
  'Horario'     => $row['Horario']
]);
