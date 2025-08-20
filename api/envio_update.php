<?php
session_start();
require_once("../include/conexion.php");
header('Content-Type: application/json; charset=utf-8');

$pid = (int)($_POST['pedido'] ?? 0);
$lat = (float)($_POST['lat'] ?? 0);
$lng = (float)($_POST['lng'] ?? 0);
$nom = trim($_POST['driver'] ?? 'Repartidor');

if ($pid<=0) { echo json_encode(['ok'=>false]); exit; }

$ins = $mysqli->prepare("INSERT INTO envios (Pedido_id,Driver_nombre,Lat,Lng) VALUES (?,?,?,?)
                         ON DUPLICATE KEY UPDATE Driver_nombre=VALUES(Driver_nombre), Lat=VALUES(Lat), Lng=VALUES(Lng)");
$ins->bind_param("isdd",$pid,$nom,$lat,$lng);
$ins->execute(); $ins->close();
echo json_encode(['ok'=>true]);
