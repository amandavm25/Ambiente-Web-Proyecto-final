<?php
// cliente/favorito_toggle.php
declare(strict_types=1);
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once("../include/auth.php");
require_role('cliente'); // asegura que sea cliente
require_once("../include/conexion.php");

// Evita que avisos/notice rompan el JSON
ini_set('display_errors', '0');

$uid = (int)($_SESSION['usuario']['Id_usuario'] ?? 0);
$neg = (int)($_POST['negocio'] ?? 0);

if ($uid <= 0 || $neg <= 0) {
  echo json_encode(['ok' => false, 'msg' => 'Parámetros inválidos']);
  exit;
}

// Verifica que el negocio exista 
$chk = $mysqli->prepare("SELECT 1 FROM negocios WHERE Id_negocio=? LIMIT 1");
$chk->bind_param("i", $neg);
$chk->execute();
$exists = (bool)$chk->get_result()->fetch_row();
$chk->close();

if (!$exists) {
  echo json_encode(['ok' => false, 'msg' => 'Negocio no existe']);
  exit;
}

try {
  // ¿Ya es favorito?
  $q = $mysqli->prepare("SELECT 1 FROM favoritos WHERE Usuario_id=? AND Negocio_id=?");
  $q->bind_param("ii", $uid, $neg);
  $q->execute();
  $isFav = (bool)$q->get_result()->fetch_row();
  $q->close();

  if ($isFav) {
    $d = $mysqli->prepare("DELETE FROM favoritos WHERE Usuario_id=? AND Negocio_id=? LIMIT 1");
    $d->bind_param("ii", $uid, $neg);
    $d->execute();
    $d->close();
    echo json_encode(['ok' => true, 'fav' => false]);
  } else {
    $ins = $mysqli->prepare("INSERT IGNORE INTO favoritos (Usuario_id, Negocio_id) VALUES (?,?)");
    $ins->bind_param("ii", $uid, $neg);
    $ins->execute();
    $ins->close();
    echo json_encode(['ok' => true, 'fav' => true]);
  }
} catch (Throwable $e) {
  echo json_encode(['ok' => false, 'msg' => 'Error ' . $e->getMessage()]);
}
