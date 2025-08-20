<?php
session_start();
require_once("../include/auth.php");
require_role('cliente');
require_once("../include/conexion.php");
header('Content-Type: application/json; charset=utf-8');

$u = (int)$_SESSION['usuario']['Id_usuario'];
$n = (int)($_POST['negocio'] ?? 0);
if ($n <= 0) {
  echo json_encode(['ok' => false]);
  exit;
}

$mysqli->query("INSERT IGNORE INTO favoritos (Usuario_id,Negocio_id) VALUES ($u,$n)");
if ($mysqli->affected_rows === 0) {
  $mysqli->query("DELETE FROM favoritos WHERE Usuario_id=$u AND Negocio_id=$n");
  echo json_encode(['ok' => true, 'fav' => false]);
} else {
  echo json_encode(['ok' => true, 'fav' => true]);
}
